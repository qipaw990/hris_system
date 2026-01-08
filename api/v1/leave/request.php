<?php
/**
 * Submit Leave Request
 * POST /api/v1/leave/request.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['leave_type_id', 'start_date', 'end_date', 'reason']);

$leaveTypeId = intval($input['leave_type_id']);
$startDate = $input['start_date'];
$endDate = $input['end_date'];
$reason = $input['reason'];

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Check for pending requests
    $pendingStmt = $pdo->prepare("SELECT COUNT(*) as pending_count 
                                  FROM leave_requests 
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
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    if ($start > $end) {
        sendError('End date must be after start date', 'VALIDATION_ERROR', 400);
    }
    
    // Calculate days
    $interval = $start->diff($end);
    $daysRequested = $interval->days + 1;
    
    // Check leave balance
    $balanceStmt = $pdo->prepare("SELECT lt.max_days,
                                  COALESCE(SUM(lr.total_days), 0) as used_days
                                  FROM leave_types lt
                                  LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id 
                                      AND lr.employee_id = ? 
                                      AND YEAR(lr.start_date) = YEAR(CURDATE())
                                      AND lr.status IN ('Approved', 'Pending')
                                  WHERE lt.id = ?
                                  GROUP BY lt.id");
    $balanceStmt->execute([$employeeId, $leaveTypeId]);
    $balance = $balanceStmt->fetch();
    
    if (!$balance) {
        sendError('Invalid leave type', 'VALIDATION_ERROR', 400);
    }
    
    $remaining = $balance['max_days'] - $balance['used_days'];
    
    if ($daysRequested > $remaining) {
        sendError("Insufficient leave balance. Remaining: $remaining days", 'INSUFFICIENT_BALANCE', 400);
    }
    
    // Insert leave request
    $sql = "INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId, $leaveTypeId, $startDate, $endDate, $daysRequested, $reason]);
    
    $requestId = $pdo->lastInsertId();
    
    sendResponse(true, 'Leave request submitted successfully', [
        'request_id' => (int)$requestId,
        'days_requested' => (int)$daysRequested,
        'remaining_balance' => (int)($remaining - $daysRequested),
        'status' => 'Pending',
        'message' => 'Your leave request is pending approval'
    ]);
    
} catch (PDOException $e) {
    error_log("Submit leave request error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Submit leave request error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
