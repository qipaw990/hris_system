<?php
/**
 * Check-In Endpoint
 * POST /api/v1/attendance/check-in.php
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
    
    // Check if already checked in today
    $checkStmt = $pdo->prepare("SELECT id FROM attendance 
                                WHERE employee_id = ? AND attendance_date = CURDATE() AND check_out IS NULL");
    $checkStmt->execute([$employeeId]);
    if ($checkStmt->fetch()) {
        sendError('Already checked in today', 'ALREADY_CHECKED_IN', 400);
    }
    
    // Get employee's current shift
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
    
    // Determine status based on shift
    $currentTime = date('H:i:s');
    $currentDateTime = new DateTime();
    $status = 'Hadir';
    $lateMinutes = 0;
    $shiftId = null;
    
    if ($shift) {
        $shiftId = $shift['id'];
        $shiftStart = new DateTime(date('Y-m-d') . ' ' . $shift['start_time']);
        $shiftEnd = new DateTime(date('Y-m-d') . ' ' . $shift['end_time']);
        $gracePeriod = $shift['grace_period_minutes'] ?? 0;
        
        // Validate check-in window: Only during shift hours (start to end)
        $allowedEarliestCheckIn = clone $shiftStart;
        $allowedLatestCheckIn = clone $shiftEnd; // Can check-in until shift ends
        
        // Check if current time is within allowed window
        if ($currentDateTime < $allowedEarliestCheckIn) {
            $earliestTime = $allowedEarliestCheckIn->format('H:i');
            sendError(
                "Belum waktunya check-in. Shift Anda dimulai pukul {$earliestTime}",
                'TOO_EARLY',
                400
            );
        }
        
        if ($currentDateTime > $allowedLatestCheckIn) {
            $latestTime = $allowedLatestCheckIn->format('H:i');
            sendError(
                "Waktu check-in sudah lewat. Shift berakhir pukul {$latestTime}. Silakan hubungi HRD untuk koreksi kehadiran.",
                'TOO_LATE',
                400
            );
        }
        
        // Calculate late status
        $shiftStartWithGrace = clone $shiftStart;
        $shiftStartWithGrace->modify("+{$gracePeriod} minutes");
        
        // Check if late
        if ($currentDateTime > $shiftStartWithGrace) {
            $status = 'Terlambat';
            $lateDiff = $shiftStartWithGrace->diff($currentDateTime);
            $lateMinutes = ($lateDiff->h * 60) + $lateDiff->i;
        }
    } else {
        // No shift assigned, use default 08:00 - 17:00
        $defaultStart = new DateTime(date('Y-m-d') . ' 08:00:00');
        $defaultEnd = new DateTime(date('Y-m-d') . ' 17:00:00');
        $defaultGrace = new DateTime(date('Y-m-d') . ' 08:15:00');
        
        if ($currentDateTime < $defaultStart) {
            sendError(
                "Belum waktunya check-in. Jam kerja dimulai pukul 08:00",
                'TOO_EARLY',
                400
            );
        }
        
        if ($currentDateTime > $defaultEnd) {
            sendError(
                "Waktu check-in sudah lewat. Jam kerja berakhir pukul 17:00. Silakan hubungi HRD untuk koreksi kehadiran.",
                'TOO_LATE',
                400
            );
        }
        
        if ($currentDateTime > $defaultGrace) {
            $status = 'Terlambat';
            $lateDiff = $defaultGrace->diff($currentDateTime);
            $lateMinutes = ($lateDiff->h * 60) + $lateDiff->i;
        }
    }
    
    // Insert attendance record with shift_id
    $sql = "INSERT INTO attendance (employee_id, attendance_date, check_in, status, shift_id,
            check_in_latitude, check_in_longitude, office_location_id, distance_meters) 
            VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $employeeId,
        $currentTime,
        $status,
        $shiftId,
        $latitude,
        $longitude,
        $office['id'],
        round($distance)
    ]);
    
    $attendanceId = $pdo->lastInsertId();
    
    // Prepare response
    $responseData = [
        'attendance_id' => $attendanceId,
        'check_in_time' => $currentTime,
        'status' => $status,
        'office' => [
            'id' => $office['id'],
            'name' => $office['location_name'],
            'address' => $office['address']
        ],
        'distance' => round($distance, 2)
    ];
    
    // Add shift info if available
    if ($shift) {
        $responseData['shift'] = [
            'id' => $shift['id'],
            'name' => $shift['shift_name'],
            'start_time' => $shift['start_time'],
            'end_time' => $shift['end_time'],
            'grace_period' => $shift['grace_period_minutes']
        ];
    }
    
    // Add late info if applicable
    if ($status === 'Terlambat') {
        $responseData['late_minutes'] = $lateMinutes;
        $responseData['message'] = "Anda terlambat {$lateMinutes} menit";
    } else {
        $responseData['message'] = 'Selamat bekerja!';
    }
    
    sendResponse(true, 'Check-in successful', $responseData);
    
} catch (PDOException $e) {
    error_log("Check-in error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Check-in error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
