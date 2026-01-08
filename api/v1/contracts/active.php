<?php
/**
 * Get Active Contract
 * GET /api/v1/contracts/active.php
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
    
    // Get active contract
    $stmt = $pdo->prepare("SELECT c.*, ct.type_name
                          FROM contracts c
                          JOIN contract_types ct ON c.contract_type_id = ct.id
                          WHERE c.employee_id = ? 
                          AND c.status = 'Active'
                          AND c.start_date <= CURDATE()
                          AND (c.end_date IS NULL OR c.end_date >= CURDATE())
                          ORDER BY c.start_date DESC
                          LIMIT 1");
    $stmt->execute([$employeeId]);
    $contract = $stmt->fetch();
    
    if (!$contract) {
        sendResponse(true, 'No active contract found', [
            'has_active_contract' => false,
            'contract' => null
        ]);
    }
    
    // Calculate remaining days
    $remainingDays = null;
    if ($contract['end_date']) {
        $end = new DateTime($contract['end_date']);
        $today = new DateTime();
        if ($today < $end) {
            $interval = $today->diff($end);
            $remainingDays = $interval->days;
        }
    }
    
    $contractData = [
        'id' => $contract['id'],
        'contract_number' => $contract['contract_number'],
        'contract_type' => $contract['type_name'],
        'start_date' => $contract['start_date'],
        'end_date' => $contract['end_date'],
        'remaining_days' => $remainingDays,
        'salary' => floatval($contract['salary']),
        'status' => $contract['status']
    ];
    
    sendResponse(true, 'Active contract retrieved successfully', [
        'has_active_contract' => true,
        'contract' => $contractData
    ]);
    
} catch (PDOException $e) {
    error_log("Active contract error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
