<?php
/**
 * Check-Out Endpoint
 * POST /api/v1/attendance/check-out.php
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
validateRequired($input, ['latitude', 'longitude']);

$latitude = floatval($input['latitude']);
$longitude = floatval($input['longitude']);
$userId = $tokenData['user_id'];

try {
    // Get employee data
    $user = getCurrentUser($userId);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get today's attendance record
    $stmt = $pdo->prepare("SELECT * FROM attendance 
                          WHERE employee_id = ? AND attendance_date = CURDATE() AND check_out IS NULL");
    $stmt->execute([$employeeId]);
    $attendance = $stmt->fetch();
    
    if (!$attendance) {
        sendError('No check-in record found for today', 'NOT_CHECKED_IN', 400);
    }
    
    // Get employee's shift for validation
    $shiftStmt = $pdo->prepare("SELECT ws.* 
                                FROM employee_shifts es
                                JOIN work_shifts ws ON es.shift_id = ws.id
                                WHERE es.employee_id = ?
                                AND (es.end_date IS NULL OR es.end_date >= CURDATE())
                                AND es.effective_date <= CURDATE()
                                ORDER BY es.effective_date DESC
                                LIMIT 1");
    $shiftStmt->execute([$employeeId]);
    $shift = $shiftStmt->fetch();
    
    // Validate location
    $locationValidation = validateAttendanceLocation($latitude, $longitude);
    
    if (!$locationValidation['success']) {
        sendError($locationValidation['message'], 'LOCATION_ERROR', 400);
    }
    
    $office = $locationValidation['office'];
    $distance = $locationValidation['distance'];
    
    // Validate check-out time
    $currentTime = date('H:i:s');
    $currentDateTime = new DateTime();
    $checkInDateTime = new DateTime($attendance['check_in']);
    
    if ($shift) {
        // Validate against shift end time only
        $shiftEnd = new DateTime(date('Y-m-d') . ' ' . $shift['end_time']);
        
        // Check-out only allowed after shift ends
        if ($currentDateTime < $shiftEnd) {
            $endTime = $shiftEnd->format('H:i');
            $workSoFar = $checkInDateTime->diff($currentDateTime);
            $hoursWorked = $workSoFar->h + ($workSoFar->i / 60);
            
            sendError(
                "Belum waktunya check-out. Shift berakhir pukul {$endTime}. Anda baru bekerja " . round($hoursWorked, 1) . " jam.",
                'TOO_EARLY_CHECKOUT',
                400
            );
        }
    } else {
        // No shift, use default end time 17:00
        $defaultEnd = new DateTime(date('Y-m-d') . ' 17:00:00');
        
        if ($currentDateTime < $defaultEnd) {
            $workSoFar = $checkInDateTime->diff($currentDateTime);
            $hoursWorked = $workSoFar->h + ($workSoFar->i / 60);
            
            sendError(
                "Belum waktunya check-out. Jam kerja berakhir pukul 17:00. Anda baru bekerja " . round($hoursWorked, 1) . " jam.",
                'TOO_EARLY_CHECKOUT',
                400
            );
        }
    }
    
    // Update attendance record
    
    $sql = "UPDATE attendance 
            SET check_out = ?, 
                check_out_latitude = ?, 
                check_out_longitude = ? 
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentTime, $latitude, $longitude, $attendance['id']]);
    
    // Calculate work hours
    $checkIn = new DateTime($attendance['check_in']);
    $checkOut = new DateTime($currentTime);
    $interval = $checkIn->diff($checkOut);
    $workHours = $interval->h + ($interval->i / 60);
    
    sendResponse(true, 'Check-out successful', [
        'attendance_id' => $attendance['id'],
        'check_in_time' => $attendance['check_in'],
        'check_out_time' => $currentTime,
        'work_hours' => round($workHours, 2),
        'office' => [
            'id' => $office['id'],
            'name' => $office['location_name']
        ],
        'distance' => round($distance, 2),
        'message' => 'Terima kasih, sampai jumpa besok!'
    ]);
    
} catch (PDOException $e) {
    error_log("Check-out error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Check-out error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
