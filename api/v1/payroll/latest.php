<?php
/**
 * Get Latest Payroll Slip
 * GET /api/v1/payroll/latest.php
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
    
    // Check if payroll_records table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'payroll_records'");
    if ($tableCheck->rowCount() == 0) {
        // Table doesn't exist, return no payroll
        sendResponse(true, 'No payroll records found', [
            'has_payroll' => false,
            'slip' => null,
            'note' => 'Payroll system not configured'
        ]);
    }
    
    // Get latest payroll record
    $stmt = $pdo->prepare("SELECT pr.*, 
                          CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                          e.employee_code
                          FROM payroll_records pr
                          JOIN employees e ON pr.employee_id = e.id
                          WHERE pr.employee_id = ?
                          ORDER BY pr.period_start DESC
                          LIMIT 1");
    $stmt->execute([$employeeId]);
    $record = $stmt->fetch();
    
    if (!$record) {
        sendResponse(true, 'No payroll records found', [
            'has_payroll' => false,
            'slip' => null
        ]);
    }
    
    $slipData = [
        'id' => $record['id'],
        'period' => date('F Y', strtotime($record['period_start'])),
        'period_start' => $record['period_start'],
        'period_end' => $record['period_end'],
        'basic_salary' => floatval($record['basic_salary']),
        'total_earnings' => floatval($record['total_earnings']),
        'total_deductions' => floatval($record['total_deductions']),
        'net_salary' => floatval($record['net_salary']),
        'status' => $record['status'],
        'payment_date' => $record['payment_date']
    ];
    
    sendResponse(true, 'Latest payroll slip retrieved successfully', [
        'has_payroll' => true,
        'slip' => $slipData
    ]);
    
} catch (PDOException $e) {
    error_log("Latest payroll error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Latest payroll error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
