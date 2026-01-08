<?php
/**
 * Dashboard API - Mobile Home Screen
 * GET /api/v1/dashboard.php
 * Returns: Employee info, contract details, quick stats
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    $today = date('Y-m-d');
    
    // Get employee basic info
    $empStmt = $pdo->prepare("SELECT e.*, 
                              d.department_name,
                              p.position_name
                              FROM employees e
                              LEFT JOIN departments d ON e.department_id = d.id
                              LEFT JOIN positions p ON e.position_id = p.id
                              WHERE e.id = ?");
    $empStmt->execute([$employeeId]);
    $employee = $empStmt->fetch();
    
    if (!$employee) {
        sendError('Employee not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    // Get active contract info
    $contractStmt = $pdo->prepare("SELECT 
                                   contract_type,
                                   start_date,
                                   end_date,
                                   contract_status,
                                   DATEDIFF(end_date, ?) as days_remaining
                                   FROM contracts
                                   WHERE employee_id = ?
                                   AND contract_status = 'Active'
                                   ORDER BY start_date DESC
                                   LIMIT 1");
    $contractStmt->execute([$today, $employeeId]);
    $contract = $contractStmt->fetch();
    
    // Get today's attendance
    $attendanceStmt = $pdo->prepare("SELECT * FROM attendance 
                                     WHERE employee_id = ? AND attendance_date = ?");
    $attendanceStmt->execute([$employeeId, $today]);
    $todayAttendance = $attendanceStmt->fetch();
    
    // Get pending leave requests count
    $leaveStmt = $pdo->prepare("SELECT COUNT(*) as count FROM leave_requests 
                               WHERE employee_id = ? AND status = 'Pending'");
    $leaveStmt->execute([$employeeId]);
    $pendingLeave = $leaveStmt->fetch()['count'];
    
    // Get this month attendance summary
    $thisMonth = date('Y-m');
    $attendanceSummaryStmt = $pdo->prepare("SELECT 
                                           COUNT(*) as total_days,
                                           SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as present_days,
                                           SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as late_days,
                                           SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as absent_days
                                           FROM attendance
                                           WHERE employee_id = ?
                                           AND DATE_FORMAT(attendance_date, '%Y-%m') = ?");
    $attendanceSummaryStmt->execute([$employeeId, $thisMonth]);
    $attendanceSummary = $attendanceSummaryStmt->fetch();
    
    // Prepare contract info
    $contractInfo = null;
    if ($contract) {
        $daysRemaining = (int)$contract['days_remaining'];
        $isExpiringSoon = $daysRemaining <= 30 && $daysRemaining > 0;
        $isExpired = $daysRemaining < 0;
        
        $contractInfo = [
            'contract_type' => $contract['contract_type'],
            'start_date' => $contract['start_date'],
            'end_date' => $contract['end_date'],
            'days_remaining' => $daysRemaining,
            'status' => $contract['contract_status'],
            'is_expiring_soon' => $isExpiringSoon,
            'is_expired' => $isExpired,
            'warning_message' => $isExpired 
                ? 'Kontrak Anda sudah berakhir' 
                : ($isExpiringSoon ? "Kontrak akan berakhir dalam $daysRemaining hari" : null)
        ];
    }
    
    // Prepare response
    $response = [
        'employee' => [
            'id' => (int)$employee['id'],
            'employee_code' => $employee['employee_code'],
            'name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'email' => $employee['email'],
            'phone' => $employee['phone_number'],
            'department' => $employee['department_name'],
            'position' => $employee['position_name'],
            'photo_url' => $employee['photo'] ? '/hrm/assets/uploads/' . $employee['photo'] : null
        ],
        'contract' => $contractInfo,
        'today_attendance' => $todayAttendance ? [
            'status' => $todayAttendance['status'],
            'check_in' => $todayAttendance['check_in'],
            'check_out' => $todayAttendance['check_out'],
            'notes' => $todayAttendance['notes']
        ] : null,
        'quick_stats' => [
            'pending_leave_requests' => (int)$pendingLeave,
            'this_month_present' => (int)$attendanceSummary['present_days'],
            'this_month_late' => (int)$attendanceSummary['late_days'],
            'this_month_absent' => (int)$attendanceSummary['absent_days']
        ]
    ];
    
    sendResponse(true, 'Dashboard data retrieved successfully', $response);
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
