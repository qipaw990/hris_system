<?php
/**
 * Get Leave Requests
 * GET /api/v1/leave/requests.php
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
    $sql = "SELECT lr.*, lt.leave_name as leave_type_name
            FROM leave_requests lr
            JOIN leave_types lt ON lr.leave_type_id = lt.id
            WHERE lr.employee_id = ?";
    
    $params = [$employeeId];
    
    if ($status) {
        $sql .= " AND lr.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY lr.created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
    // Format requests
    $formattedRequests = [];
    foreach ($requests as $request) {
        $formattedRequests[] = [
            'id' => (int)$request['id'],
            'leave_type' => $request['leave_type_name'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'days_requested' => (int)$request['total_days'],
            'reason' => $request['reason'],
            'status' => $request['status'],
            'created_at' => $request['created_at']
        ];
    }
    
    sendResponse(true, 'Leave requests retrieved successfully', [
        'requests' => $formattedRequests,
        'count' => count($formattedRequests)
    ]);
    
} catch (PDOException $e) {
    error_log("Leave requests error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Leave requests error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
