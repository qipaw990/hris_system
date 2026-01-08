<?php
/**
 * Submit Sick Leave or Permission Request
 * POST /api/v1/leave/sick-permission/request.php
 * Supports file upload for medical certificate
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get form data
    $leaveType = $_POST['leave_type'] ?? ''; // 'Sakit' or 'Izin'
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    
    // Validate required fields
    if (empty($leaveType) || empty($startDate) || empty($endDate) || empty($reason)) {
        sendError('All fields are required', 'VALIDATION_ERROR', 400);
    }
    
    // Validate leave type
    if (!in_array($leaveType, ['Sakit', 'Izin'])) {
        sendError('Invalid leave type. Must be Sakit or Izin', 'VALIDATION_ERROR', 400);
    }
    
    // Check for pending requests
    $pendingStmt = $pdo->prepare("SELECT COUNT(*) as pending_count 
                                  FROM sick_permission_requests 
                                  WHERE employee_id = ? 
                                  AND status = 'Pending'");
    $pendingStmt->execute([$employeeId]);
    $pendingResult = $pendingStmt->fetch();
    
    if ($pendingResult['pending_count'] > 0) {
        sendError(
            'Anda masih memiliki request yang pending. Tunggu verifikasi admin sebelum mengirim request baru.',
            'PENDING_REQUEST_EXISTS',
            400
        );
    }
    
    // Validate dates
    $start = DateTime::createFromFormat('Y-m-d', $startDate);
    $end = DateTime::createFromFormat('Y-m-d', $endDate);
    
    if (!$start || !$end) {
        sendError('Invalid date format. Use YYYY-MM-DD', 'VALIDATION_ERROR', 400);
    }
    
    if ($start > $end) {
        sendError('End date must be after start date', 'VALIDATION_ERROR', 400);
    }
    
    // Calculate total days
    $interval = $start->diff($end);
    $totalDays = $interval->days + 1;
    
    // Validate duration based on type
    if ($leaveType === 'Izin' && $totalDays > 1) {
        sendError('Permission leave (Izin) max 1 day per request', 'VALIDATION_ERROR', 400);
    }
    
    // Check if start date is not too far in the past (max 2 days back)
    $today = new DateTime();
    $daysDiff = $today->diff($start)->days;
    $isPast = $start < $today;
    
    if ($isPast && $daysDiff > 2) {
        sendError('Can only submit for today, yesterday, or day before yesterday', 'VALIDATION_ERROR', 400);
    }
    
    // Check for overlapping requests
    $overlapCheck = $pdo->prepare("SELECT id FROM sick_permission_requests 
                                   WHERE employee_id = ? 
                                   AND status != 'Rejected'
                                   AND (
                                       (start_date <= ? AND end_date >= ?) OR
                                       (start_date <= ? AND end_date >= ?) OR
                                       (start_date >= ? AND end_date <= ?)
                                   )");
    $overlapCheck->execute([
        $employeeId,
        $startDate, $startDate,
        $endDate, $endDate,
        $startDate, $endDate
    ]);
    
    if ($overlapCheck->fetch()) {
        sendError('You already have a request for this date range', 'DUPLICATE_REQUEST', 400);
    }
    
    // Handle file upload for Sakit >3 days
    $attachmentPath = null;
    
    if ($leaveType === 'Sakit' && $totalDays > 3) {
        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE) {
            sendError('Medical certificate required for sick leave more than 3 days', 'ATTACHMENT_REQUIRED', 400);
        }
    }
    
    // Process file upload if provided
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            sendError('File size must be less than 5MB', 'FILE_TOO_LARGE', 400);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            sendError('Only JPG, PNG, and PDF files are allowed', 'INVALID_FILE_TYPE', 400);
        }
        
        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../../../assets/uploads/leave_attachments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('leave_') . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            sendError('Failed to upload file', 'UPLOAD_FAILED', 500);
        }
        
        $attachmentPath = 'leave_attachments/' . $filename;
    }
    
    // Insert sick/permission request
    $insertStmt = $pdo->prepare("INSERT INTO sick_permission_requests 
                                 (employee_id, request_type, start_date, end_date, total_days, reason, attachment, status, created_at)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
    
    $insertStmt->execute([
        $employeeId,
        $leaveType,
        $startDate,
        $endDate,
        $totalDays,
        $reason,
        $attachmentPath
    ]);
    
    $requestId = $pdo->lastInsertId();
    
    sendResponse(true, ucfirst($leaveType) . ' request submitted successfully', [
        'request_id' => (int)$requestId,
        'leave_type' => $leaveType,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'total_days' => (int)$totalDays,
        'status' => 'Pending',
        'has_attachment' => $attachmentPath !== null,
        'message' => 'Your request is pending approval'
    ]);
    
} catch (PDOException $e) {
    error_log("Sick/Permission request error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Sick/Permission request error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
