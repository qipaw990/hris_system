<?php
/**
 * Get Complete Profile with All Data
 * GET /api/v1/profile/complete.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }
    
    // Base user data
    $profileData = [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ]
    ];
    
    // If user has employee data, get comprehensive info
    if ($user['employee_id']) {
        $employeeId = $user['employee_id'];
        
        // Get complete employee data
        $empStmt = $pdo->prepare("SELECT e.*, 
                                  d.department_name, 
                                  p.position_name,
                                  p.description as position_description
                                  FROM employees e
                                  LEFT JOIN departments d ON e.department_id = d.id
                                  LEFT JOIN positions p ON e.position_id = p.id
                                  WHERE e.id = ?");
        $empStmt->execute([$employeeId]);
        $employee = $empStmt->fetch();
        
        if ($employee) {
            $profileData['employee'] = [
                'id' => $employee['id'],
                'employee_code' => $employee['employee_code'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'date_of_birth' => $employee['date_of_birth'],
                'gender' => $employee['gender'],
                'phone' => $employee['phone'],
                'address' => $employee['address'],
                'hire_date' => $employee['hire_date'],
                'photo' => $employee['photo'] ? '/hrm/uploads/employees/' . $employee['photo'] : null,
                'department' => [
                    'id' => $employee['department_id'],
                    'name' => $employee['department_name']
                ],
                'position' => [
                    'id' => $employee['position_id'],
                    'name' => $employee['position_name'],
                    'description' => $employee['position_description']
                ]
            ];
            
            // Get active contract
            $contractStmt = $pdo->prepare("SELECT c.*
                                          FROM contracts c
                                          WHERE c.employee_id = ? 
                                          AND c.contract_status = 'Active'
                                          AND c.start_date <= CURDATE()
                                          AND (c.end_date IS NULL OR c.end_date >= CURDATE())
                                          ORDER BY c.start_date DESC
                                          LIMIT 1");
            $contractStmt->execute([$employeeId]);
            $contract = $contractStmt->fetch();
            
            if ($contract) {
                $remainingDays = null;
                if ($contract['end_date']) {
                    $end = new DateTime($contract['end_date']);
                    $today = new DateTime();
                    if ($today < $end) {
                        $interval = $today->diff($end);
                        $remainingDays = $interval->days;
                    }
                }
                
                $profileData['contract'] = [
                    'id' => (int)$contract['id'],
                    'contract_number' => $contract['contract_number'],
                    'type' => $contract['contract_type'],
                    'start_date' => $contract['start_date'],
                    'end_date' => $contract['end_date'],
                    'remaining_days' => $remainingDays,
                    'salary' => floatval($contract['salary']),
                    'status' => $contract['contract_status']
                ];
            } else {
                $profileData['contract'] = null;
            }
            
            // Get attendance summary for current month
            $attStmt = $pdo->prepare("SELECT 
                                     COUNT(*) as total_days,
                                     SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as on_time,
                                     SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as late,
                                     SUM(CASE WHEN check_out IS NULL THEN 1 ELSE 0 END) as incomplete
                                     FROM attendance
                                     WHERE employee_id = ? 
                                     AND MONTH(attendance_date) = MONTH(CURDATE())
                                     AND YEAR(attendance_date) = YEAR(CURDATE())");
            $attStmt->execute([$employeeId]);
            $attendance = $attStmt->fetch();
            
            $profileData['attendance_summary'] = [
                'current_month' => date('F Y'),
                'total_days' => intval($attendance['total_days']),
                'on_time' => intval($attendance['on_time']),
                'late' => intval($attendance['late']),
                'incomplete' => intval($attendance['incomplete'])
            ];
            
            // Get leave balance summary
            $leaveStmt = $pdo->query("SELECT * FROM leave_types ORDER BY leave_name");
            $leaveTypes = $leaveStmt->fetchAll();
            
            $leaveBalances = [];
            foreach ($leaveTypes as $type) {
                $usedStmt = $pdo->prepare("SELECT COALESCE(SUM(total_days), 0) as used
                                          FROM leave_requests
                                          WHERE employee_id = ? 
                                          AND leave_type_id = ?
                                          AND YEAR(start_date) = YEAR(CURDATE())
                                          AND status IN ('Approved', 'Pending')");
                $usedStmt->execute([$employeeId, $type['id']]);
                $used = $usedStmt->fetch()['used'];
                
                $leaveBalances[] = [
                    'type' => $type['leave_name'],
                    'total' => (int)$type['max_days'],
                    'used' => (int)$used,
                    'remaining' => (int)($type['max_days'] - $used)
                ];
            }
            
            $profileData['leave_balance'] = $leaveBalances;
            
            // Get latest payroll
            $payrollStmt = $pdo->prepare("SELECT ps.*, pp.period_name
                                         FROM payroll_slips ps
                                         JOIN payroll_periods pp ON ps.period_id = pp.id
                                         WHERE ps.employee_id = ?
                                         ORDER BY pp.period_year DESC, pp.period_month DESC
                                         LIMIT 1");
            $payrollStmt->execute([$employeeId]);
            $payroll = $payrollStmt->fetch();
            
            if ($payroll) {
                $profileData['latest_payroll'] = [
                    'id' => (int)$payroll['id'],
                    'period' => $payroll['period_name'],
                    'net_salary' => floatval($payroll['net_salary']),
                    'status' => $payroll['status'],
                    'payment_date' => $payroll['payment_date']
                ];
            } else {
                $profileData['latest_payroll'] = null;
            }
            
            // Get KPI summary if exists
            $kpiStmt = $pdo->prepare("SELECT AVG(score) as avg_score, COUNT(*) as total_evaluations
                                     FROM kpi_evaluations
                                     WHERE employee_id = ?
                                     AND YEAR(created_at) = YEAR(CURDATE())");
            $kpiStmt->execute([$employeeId]);
            $kpi = $kpiStmt->fetch();
            
            $profileData['kpi_summary'] = [
                'average_score' => $kpi['avg_score'] ? round(floatval($kpi['avg_score']), 2) : null,
                'total_evaluations' => intval($kpi['total_evaluations']),
                'year' => date('Y')
            ];
        }
    }
    
    sendResponse(true, 'Complete profile retrieved successfully', $profileData);
    
} catch (PDOException $e) {
    error_log("Complete profile error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Complete profile error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
