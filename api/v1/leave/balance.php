<?php
/**
 * Get Leave Balance
 * GET /api/v1/leave/balance.php
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
    
    // Check if leave_types table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'leave_types'");
    if ($tableCheck->rowCount() == 0) {
        // Table doesn't exist, return empty balance
        sendResponse(true, 'Leave balance retrieved successfully', [
            'balances' => [],
            'year' => date('Y'),
            'note' => 'Leave types not configured'
        ]);
    }
    
    // Get leave types and balances
    $stmt = $pdo->query("SELECT * FROM leave_types ORDER BY leave_name ASC");
    $leaveTypes = $stmt->fetchAll();
    
    $balances = [];
    foreach ($leaveTypes as $type) {
        // Get total used for this year
        $usedStmt = $pdo->prepare("SELECT COALESCE(SUM(total_days), 0) as total_used 
                                   FROM leave_requests 
                                   WHERE employee_id = ? 
                                   AND leave_type_id = ? 
                                   AND YEAR(start_date) = YEAR(CURDATE())
                                   AND status IN ('Approved', 'Pending')");
        $usedStmt->execute([$employeeId, $type['id']]);
        $used = $usedStmt->fetch()['total_used'];
        
        $balances[] = [
            'leave_type_id' => $type['id'],
            'leave_type' => $type['leave_name'],
            'total_days' => $type['max_days'],
            'used_days' => floatval($used),
            'remaining_days' => $type['max_days'] - floatval($used)
        ];
    }
    
    sendResponse(true, 'Leave balance retrieved successfully', [
        'balances' => $balances,
        'year' => date('Y')
    ]);
    
} catch (PDOException $e) {
    error_log("Leave balance error: " . $e->getMessage());
    // Send more detailed error in development
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Leave balance error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
