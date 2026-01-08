<?php
/**
 * Today's Attendance
 * GET /api/v1/attendance/today.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get today's attendance
    $stmt = $pdo->prepare("SELECT a.*, ol.location_name, ol.address
                          FROM attendance a
                          LEFT JOIN office_locations ol ON a.office_location_id = ol.id
                          WHERE a.employee_id = ? AND a.attendance_date = CURDATE()");
    $stmt->execute([$employeeId]);
    $attendance = $stmt->fetch();
    
    if (!$attendance) {
        sendResponse(true, 'No attendance record for today', [
            'has_checked_in' => false,
            'attendance' => null
        ]);
    }
    
    // Calculate work hours if checked out
    $workHours = null;
    if ($attendance['check_out']) {
        $checkIn = new DateTime($attendance['check_in']);
        $checkOut = new DateTime($attendance['check_out']);
        $interval = $checkIn->diff($checkOut);
        $workHours = $interval->h + ($interval->i / 60);
    }
    
    sendResponse(true, 'Today\'s attendance retrieved successfully', [
        'has_checked_in' => true,
        'has_checked_out' => !empty($attendance['check_out']),
        'attendance' => [
            'id' => (int)$attendance['id'],
            'date' => $attendance['attendance_date'],
            'check_in_time' => $attendance['check_in'],
            'check_out_time' => $attendance['check_out'],
            'status' => $attendance['status'],
            'work_hours' => $workHours ? round($workHours, 2) : null,
            'office' => [
                'name' => $attendance['location_name'] ?? '',
                'address' => $attendance['address'] ?? ''
            ],
            'distance' => $attendance['distance_meters'] ? (int)$attendance['distance_meters'] : 0
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Today's attendance error: " . $e->getMessage());
    // Send more detailed error
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Today's attendance error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
