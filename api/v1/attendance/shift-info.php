<?php
/**
 * Get Employee Shift Info
 * GET /api/v1/attendance/shift-info.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

$userId = $tokenData['user_id'];

try {
    // Get employee data
    $user = getCurrentUser($userId);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get employee's current shift
    $shiftStmt = $pdo->prepare("SELECT ws.*, 
                                es.effective_date,
                                es.end_date,
                                es.is_permanent
                                FROM employee_shifts es
                                JOIN work_shifts ws ON es.shift_id = ws.id
                                WHERE es.employee_id = ?
                                AND (es.end_date IS NULL OR es.end_date >= CURDATE())
                                AND es.effective_date <= CURDATE()
                                ORDER BY es.effective_date DESC
                                LIMIT 1");
    $shiftStmt->execute([$employeeId]);
    $shift = $shiftStmt->fetch();
    
    if (!$shift) {
        // No shift assigned
        sendResponse(true, 'No shift assigned', [
            'has_shift' => false,
            'message' => 'Anda belum di-assign shift. Menggunakan jam kerja default (08:00 - 17:00)',
            'default_shift' => [
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'grace_period' => 15
            ],
            'check_in_window' => [
                'earliest' => '06:00:00',
                'latest' => '11:00:00'
            ]
        ]);
    }
    
    // Calculate check-in window
    $shiftStart = new DateTime(date('Y-m-d') . ' ' . $shift['start_time']);
    $shiftEnd = new DateTime(date('Y-m-d') . ' ' . $shift['end_time']);
    
    // Check-in allowed: From shift start to shift end
    $earliestCheckIn = clone $shiftStart;
    $latestCheckIn = clone $shiftEnd;
    
    // Check-out allowed: After shift end time
    $earliestCheckOut = clone $shiftEnd;
    
    // Current time info
    $currentTime = new DateTime();
    $canCheckInNow = ($currentTime >= $earliestCheckIn && $currentTime <= $latestCheckIn);
    $canCheckOutNow = ($currentTime >= $earliestCheckOut);
    
    // Calculate late threshold
    $lateThreshold = clone $shiftStart;
    $lateThreshold->modify('+' . $shift['grace_period_minutes'] . ' minutes');
    
    // Determine current status
    $status = '';
    $statusMessage = '';
    
    if ($currentTime < $earliestCheckIn) {
        $status = 'TOO_EARLY';
        $statusMessage = 'Belum waktunya check-in. Shift dimulai pukul ' . $earliestCheckIn->format('H:i');
    } elseif ($currentTime >= $earliestCheckIn && $currentTime <= $shiftStart) {
        $status = 'CAN_CHECK_IN_EARLY';
        $statusMessage = 'Anda bisa check-in sekarang (sebelum shift dimulai)';
    } elseif ($currentTime > $shiftStart && $currentTime <= $lateThreshold) {
        $status = 'CAN_CHECK_IN_ON_TIME';
        $statusMessage = 'Check-in sekarang masih tepat waktu';
    } elseif ($currentTime > $lateThreshold && $currentTime <= $latestCheckIn) {
        $minutesLate = round(($currentTime->getTimestamp() - $lateThreshold->getTimestamp()) / 60);
        $status = 'CAN_CHECK_IN_LATE';
        $statusMessage = 'Anda terlambat ' . $minutesLate . ' menit. Masih bisa check-in.';
    } elseif ($currentTime > $latestCheckIn) {
        $status = 'TOO_LATE';
        $statusMessage = 'Shift sudah berakhir. Hubungi HRD untuk koreksi.';
    }
    
    sendResponse(true, 'Shift info retrieved', [
        'has_shift' => true,
        'employee' => [
            'id' => $employeeId,
            'name' => $user['full_name'] ?? $user['username']
        ],
        'shift' => [
            'id' => $shift['id'],
            'name' => $shift['shift_name'],
            'code' => $shift['shift_code'],
            'start_time' => $shift['start_time'],
            'end_time' => $shift['end_time'],
            'start_time_formatted' => date('H:i', strtotime($shift['start_time'])),
            'end_time_formatted' => date('H:i', strtotime($shift['end_time'])),
            'grace_period_minutes' => $shift['grace_period_minutes'],
            'shift_allowance' => floatval($shift['shift_allowance']),
            'shift_allowance_formatted' => 'Rp ' . number_format($shift['shift_allowance'], 0, ',', '.'),
            'is_night_shift' => (bool)$shift['is_night_shift'],
            'description' => $shift['description']
        ],
        'assignment' => [
            'effective_date' => $shift['effective_date'],
            'end_date' => $shift['end_date'],
            'is_permanent' => (bool)$shift['is_permanent'],
            'days_assigned' => (new DateTime())->diff(new DateTime($shift['effective_date']))->days
        ],
        'check_in_window' => [
            'earliest' => $earliestCheckIn->format('H:i:s'),
            'latest' => $latestCheckIn->format('H:i:s'),
            'earliest_formatted' => $earliestCheckIn->format('H:i'),
            'latest_formatted' => $latestCheckIn->format('H:i'),
            'can_check_in_now' => $canCheckInNow,
            'description' => 'Selama jam kerja shift (' . $earliestCheckIn->format('H:i') . ' - ' . $latestCheckIn->format('H:i') . ')'
        ],
        'check_out_window' => [
            'earliest' => $earliestCheckOut->format('H:i:s'),
            'recommended' => $shift['end_time'],
            'earliest_formatted' => $earliestCheckOut->format('H:i'),
            'recommended_formatted' => date('H:i', strtotime($shift['end_time'])),
            'can_check_out_now' => $canCheckOutNow,
            'description' => 'Setelah shift berakhir (' . $earliestCheckOut->format('H:i') . ')'
        ],
        'current_status' => [
            'status' => $status,
            'message' => $statusMessage,
            'current_time' => $currentTime->format('H:i:s'),
            'current_time_formatted' => $currentTime->format('H:i'),
            'can_check_in' => $canCheckInNow,
            'can_check_out' => $canCheckOutNow,
            'is_late' => $currentTime > $lateThreshold && $currentTime <= $latestCheckIn
        ],
        'rules' => [
            'check_in_allowed' => 'Selama jam kerja shift',
            'check_out_allowed' => 'Setelah shift berakhir',
            'minimum_work_hours' => 0,
            'late_if_after' => $lateThreshold->format('H:i'),
            'grace_period_text' => $shift['grace_period_minutes'] . ' menit setelah shift dimulai'
        ],
        'display' => [
            'shift_badge' => $shift['shift_code'],
            'shift_badge_color' => $shift['is_night_shift'] ? '#1a1a1a' : '#0d6efd',
            'work_hours' => $shiftStart->format('H:i') . ' - ' . $shiftEnd->format('H:i'),
            'summary' => 'Shift ' . $shift['shift_name'] . ' (' . $shiftStart->format('H:i') . ' - ' . $shiftEnd->format('H:i') . ')'
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Shift info error: " . $e->getMessage());
    sendError('Database error', 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Shift info error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
