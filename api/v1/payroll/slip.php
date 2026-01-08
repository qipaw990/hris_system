<?php
/**
 * Get Payroll Slip Detail
 * GET /api/v1/payroll/slip.php?id={slip_id}
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

$slipId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($slipId <= 0) {
    sendError('Invalid slip ID', 'VALIDATION_ERROR', 400);
}

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get payroll slip with details
    $sql = "SELECT ps.*, 
            pp.period_name,
            pp.period_year,
            pp.period_month,
            pp.payment_date as period_payment_date,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            e.employee_code,
            d.department_name,
            p.position_name
            FROM payroll_slips ps
            JOIN payroll_periods pp ON ps.period_id = pp.id
            JOIN employees e ON ps.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE ps.id = ? AND ps.employee_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slipId, $employeeId]);
    $slip = $stmt->fetch();
    
    if (!$slip) {
        sendError('Payroll slip not found', 'NOT_FOUND', 404);
    }
    
    // Get slip details (earnings and deductions)
    $detailsStmt = $pdo->prepare("SELECT * FROM payroll_slip_details 
                                  WHERE slip_id = ? 
                                  ORDER BY component_type DESC, component_name");
    $detailsStmt->execute([$slipId]);
    $details = $detailsStmt->fetchAll();
    
    // Separate earnings and deductions
    $earnings = [];
    $deductions = [];
    
    foreach ($details as $detail) {
        $item = [
            'component' => $detail['component_name'],
            'amount' => floatval($detail['amount'])
        ];
        
        if ($detail['component_type'] === 'Earning') {
            $earnings[] = $item;
        } else {
            $deductions[] = $item;
        }
    }
    
    // Format response to match Flutter UI
    $slipData = [
        'employee' => [
            'name' => $slip['employee_name'],
            'employee_code' => $slip['employee_code'],
            'department' => $slip['department_name'] ?? '-',
            'position' => $slip['position_name'] ?? '-'
        ],
        'period' => [
            'period' => $slip['period_name'],
            'payment_date' => $slip['period_payment_date'],
            'attendance_days' => (int)$slip['attendance_days'],
            'working_days' => (int)$slip['working_days'],
            'late_count' => (int)$slip['late_count']
        ],
        'earnings' => [
            'items' => $earnings,
            'total' => floatval($slip['total_earnings'])
        ],
        'deductions' => [
            'items' => $deductions,
            'total' => floatval($slip['total_deductions'])
        ],
        'summary' => [
            'basic_salary' => floatval($slip['basic_salary']),
            'total_earnings' => floatval($slip['total_earnings']),
            'total_deductions' => floatval($slip['total_deductions']),
            'net_salary' => floatval($slip['net_salary'])
        ],
        'status' => $slip['status'],
        'notes' => $slip['notes']
    ];
    
    sendResponse(true, 'Payroll slip retrieved successfully', $slipData);
    
} catch (PDOException $e) {
    error_log("Payroll slip error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Payroll slip error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
