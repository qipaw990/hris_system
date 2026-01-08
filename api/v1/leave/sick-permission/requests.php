<?php
/**
 * Get Sick Leave and Permission Requests History
 * GET /api/v1/leave/sick-permission/requests.php
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user || !$user['employee_id']) {
        sendError('Employee data not found', 'EMPLOYEE_NOT_FOUND', 404);
    }
    
    $employeeId = $user['employee_id'];
    
    // Get filter parameters
    $leaveType = $_GET['leave_type'] ?? null; // 'Sakit' or 'Izin'
    $status = $_GET['status'] ?? null;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $offset = ($page - 1) * $limit;
    
    // Build query
    $sql = "SELECT * FROM sick_permission_requests
            WHERE employee_id = ?";
    
    $params = [$employeeId];
    
    if ($leaveType && in_array($leaveType, ['Sakit', 'Izin'])) {
        $sql .= " AND request_type = ?";
        $params[] = $leaveType;
    }
    
    if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total 
                 FROM sick_permission_requests
                 WHERE employee_id = ?";
    
    $countParams = [$employeeId];
    
    if ($leaveType && in_array($leaveType, ['Sakit', 'Izin'])) {
        $countSql .= " AND request_type = ?";
        $countParams[] = $leaveType;
    }
    
    if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
        $countSql .= " AND status = ?";
        $countParams[] = $status;
    }
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch()['total'];
    
    // Format requests
    $formattedRequests = [];
    foreach ($requests as $request) {
        $formattedRequests[] = [
            'id' => (int)$request['id'],
            'leave_type' => $request['request_type'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'total_days' => (int)$request['total_days'],
            'reason' => $request['reason'],
            'status' => $request['status'],
            'has_attachment' => !empty($request['attachment']),
            'attachment_url' => $request['attachment'] 
                ? '/hrm/assets/uploads/' . $request['attachment']
                : null,
            'created_at' => $request['created_at'],
            'approved_at' => $request['approved_at'],
            'approved_by' => $request['approved_by'],
            'rejection_reason' => $request['rejection_reason']
        ];
    }
    
    sendResponse(true, 'Requests retrieved successfully', [
        'requests' => $formattedRequests,
        'pagination' => [
            'current_page' => (int)$page,
            'total_records' => (int)$total,
            'total_pages' => (int)ceil($total / $limit),
            'per_page' => (int)$limit
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Get sick/permission requests error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Get sick/permission requests error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
