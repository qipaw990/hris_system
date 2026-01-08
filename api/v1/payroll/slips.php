<?php
/**
 * Get Payroll Slips
 * GET /api/v1/payroll/slips.php
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
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;
    $offset = ($page - 1) * $limit;
    
    // Get year filter if provided
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    
    // Check if payroll_slips table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'payroll_slips'");
    if ($tableCheck->rowCount() == 0) {
        // Table doesn't exist, return empty
        sendResponse(true, 'No payroll slips found', [
            'slips' => [],
            'pagination' => [
                'current_page' => (int)$page,
                'total_records' => 0,
                'total_pages' => 0,
                'per_page' => (int)$limit
            ],
            'year' => $year,
            'note' => 'Payroll system not configured'
        ]);
    }
    
    // Get payroll slips
    $sql = "SELECT ps.*, 
            pp.period_name,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            e.employee_code
            FROM payroll_slips ps
            JOIN payroll_periods pp ON ps.period_id = pp.id
            JOIN employees e ON ps.employee_id = e.id
            WHERE ps.employee_id = ? AND pp.period_year = ?
            ORDER BY pp.period_year DESC, pp.period_month DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId, $year, $limit, $offset]);
    $records = $stmt->fetchAll();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total 
                                FROM payroll_slips ps
                                JOIN payroll_periods pp ON ps.period_id = pp.id
                                WHERE ps.employee_id = ? AND pp.period_year = ?");
    $countStmt->execute([$employeeId, $year]);
    $total = $countStmt->fetch()['total'];
    
    // Format records
    $formattedRecords = [];
    foreach ($records as $record) {
        $formattedRecords[] = [
            'id' => (int)$record['id'],
            'period' => $record['period_name'],
            'period_id' => (int)$record['period_id'],
            'basic_salary' => floatval($record['basic_salary']),
            'total_earnings' => floatval($record['total_earnings']),
            'total_deductions' => floatval($record['total_deductions']),
            'net_salary' => floatval($record['net_salary']),
            'status' => $record['status'],
            'payment_date' => $record['payment_date'],
            'attendance_days' => (int)$record['attendance_days'],
            'working_days' => (int)$record['working_days']
        ];
    }
    
    sendResponse(true, 'Payroll slips retrieved successfully', [
        'slips' => $formattedRecords,
        'pagination' => [
            'current_page' => (int)$page,
            'total_records' => (int)$total,
            'total_pages' => (int)ceil($total / $limit),
            'per_page' => (int)$limit
        ],
        'year' => $year
    ]);
    
} catch (PDOException $e) {
    error_log("Payroll slips error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Payroll slips error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
