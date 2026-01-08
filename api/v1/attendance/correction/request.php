<?php
/**
 * Submit Attendance Correction Request
 * POST /api/v1/attendance/correction/request.php
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['request_date', 'reason']);

$requestDate = $input['request_date'];
$checkInTime = $input['check_in_time'] ?? null;
$checkOutTime = $input['check_out_time'] ?? null;
$reason = trim($input['reason']);

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Validate reason length
    if (strlen($reason) < 20) {
        sendError('Reason must be at least 20 characters', 'VALIDATION_ERROR', 400);
    }
    
    // Validate date format
    $requestDateTime = DateTime::createFromFormat('Y-m-d', $requestDate);
    if (!$requestDateTime) {
        sendError('Invalid date format. Use YYYY-MM-DD', 'VALIDATION_ERROR', 400);
    }
    
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $requestDateTime->setTime(0, 0, 0);
    
    // Check if date is in the past
    if ($requestDateTime >= $today) {
        sendError('Can only request correction for past dates', 'VALIDATION_ERROR', 400);
    }
    
    // Check max 7 days back
    $daysDiff = $today->diff($requestDateTime)->days;
    if ($daysDiff > 7) {
        sendError('Can only request correction for last 7 days', 'VALIDATION_ERROR', 400);
    }
    
    // Check if it's a weekend
    $dayOfWeek = $requestDateTime->format('N'); // 1 (Monday) to 7 (Sunday)
    if ($dayOfWeek >= 6) {
        sendError('Cannot request correction for weekends', 'VALIDATION_ERROR', 400);
    }
    
    // Check if already has attendance record
    $attendanceCheck = $pdo->prepare("SELECT id FROM attendance 
                                      WHERE employee_id = ? AND attendance_date = ?");
    $attendanceCheck->execute([$employeeId, $requestDate]);
    if ($attendanceCheck->fetch()) {
        sendError('Attendance record already exists for this date', 'DUPLICATE_RECORD', 400);
    }
    
    // Check for existing pending request
    $requestCheck = $pdo->prepare("SELECT id FROM attendance_correction_requests 
                                   WHERE employee_id = ? AND request_date = ? AND status = 'Pending'");
    $requestCheck->execute([$employeeId, $requestDate]);
    if ($requestCheck->fetch()) {
        sendError('You already have a pending request for this date', 'DUPLICATE_REQUEST', 400);
    }
    
    // Validate times if provided
    if ($checkInTime && $checkOutTime) {
        $checkIn = DateTime::createFromFormat('H:i:s', $checkInTime);
        $checkOut = DateTime::createFromFormat('H:i:s', $checkOutTime);
        
        if (!$checkIn || !$checkOut) {
            sendError('Invalid time format. Use HH:MM:SS', 'VALIDATION_ERROR', 400);
        }
        
        if ($checkIn >= $checkOut) {
            sendError('Check-out time must be after check-in time', 'VALIDATION_ERROR', 400);
        }
    }
    
    // Insert correction request
    $sql = "INSERT INTO attendance_correction_requests 
            (employee_id, request_date, check_in_time, check_out_time, reason, status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId, $requestDate, $checkInTime, $checkOutTime, $reason]);
    
    $requestId = $pdo->lastInsertId();
    
    sendResponse(true, 'Correction request submitted successfully', [
        'request_id' => (int)$requestId,
        'request_date' => $requestDate,
        'status' => 'Pending',
        'message' => 'Your request is pending admin approval'
    ]);
    
} catch (PDOException $e) {
    error_log("Correction request error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Correction request error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
