<?php
/**
 * Get Contract Detail
 * GET /api/v1/contracts/detail.php?id={contract_id}
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

$contractId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($contractId <= 0) {
    sendError('Invalid contract ID', 'VALIDATION_ERROR', 400);
}

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get contract detail
    $stmt = $pdo->prepare("SELECT c.*, ct.type_name, ct.description as type_description,
                          CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                          e.employee_code, d.department_name, p.position_name
                          FROM contracts c
                          JOIN contract_types ct ON c.contract_type_id = ct.id
                          JOIN employees e ON c.employee_id = e.id
                          LEFT JOIN departments d ON e.department_id = d.id
                          LEFT JOIN positions p ON e.position_id = p.id
                          WHERE c.id = ? AND c.employee_id = ?");
    $stmt->execute([$contractId, $employeeId]);
    $contract = $stmt->fetch();
    
    if (!$contract) {
        sendError('Contract not found', 'NOT_FOUND', 404);
    }
    
    // Calculate contract duration
    $start = new DateTime($contract['start_date']);
    $end = $contract['end_date'] ? new DateTime($contract['end_date']) : null;
    $today = new DateTime();
    
    $durationMonths = null;
    $remainingDays = null;
    
    if ($end) {
        $interval = $start->diff($end);
        $durationMonths = ($interval->y * 12) + $interval->m;
        
        if ($today < $end) {
            $remaining = $today->diff($end);
            $remainingDays = $remaining->days;
        }
    }
    
    // Format response
    $contractData = [
        'id' => $contract['id'],
        'contract_number' => $contract['contract_number'],
        'employee' => [
            'name' => $contract['employee_name'],
            'code' => $contract['employee_code'],
            'department' => $contract['department_name'],
            'position' => $contract['position_name']
        ],
        'contract_type' => [
            'name' => $contract['type_name'],
            'description' => $contract['type_description']
        ],
        'period' => [
            'start_date' => $contract['start_date'],
            'end_date' => $contract['end_date'],
            'duration_months' => $durationMonths,
            'remaining_days' => $remainingDays
        ],
        'salary' => floatval($contract['salary']),
        'terms' => $contract['terms'],
        'status' => $contract['status'],
        'file' => [
            'has_file' => !empty($contract['contract_file']),
            'filename' => $contract['contract_file'],
            'url' => $contract['contract_file'] ? '/hrm/uploads/contracts/' . $contract['contract_file'] : null
        ]
    ];
    
    sendResponse(true, 'Contract detail retrieved successfully', $contractData);
    
} catch (PDOException $e) {
    error_log("Contract detail error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
