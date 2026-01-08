<?php
/**
 * Get Employee Contracts
 * GET /api/v1/contracts/list.php
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
    
    // Get status filter if provided
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    // Build query
    $sql = "SELECT c.*, ct.type_name,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            e.employee_code
            FROM contracts c
            JOIN contract_types ct ON c.contract_type_id = ct.id
            JOIN employees e ON c.employee_id = e.id
            WHERE c.employee_id = ?";
    
    $params = [$employeeId];
    
    if ($status) {
        $sql .= " AND c.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY c.start_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contracts = $stmt->fetchAll();
    
    // Format contracts
    $formattedContracts = [];
    foreach ($contracts as $contract) {
        $formattedContracts[] = [
            'id' => $contract['id'],
            'contract_number' => $contract['contract_number'],
            'contract_type' => $contract['type_name'],
            'start_date' => $contract['start_date'],
            'end_date' => $contract['end_date'],
            'salary' => floatval($contract['salary']),
            'status' => $contract['status'],
            'has_file' => !empty($contract['contract_file'])
        ];
    }
    
    sendResponse(true, 'Contracts retrieved successfully', [
        'contracts' => $formattedContracts,
        'count' => count($formattedContracts)
    ]);
    
} catch (PDOException $e) {
    error_log("Contracts list error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
