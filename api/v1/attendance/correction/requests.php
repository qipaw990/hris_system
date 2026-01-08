<?php
/**
 * Get Attendance Correction Requests
 * GET /api/v1/attendance/correction/requests.php
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
    $status = $_GET['status'] ?? null;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $offset = ($page - 1) * $limit;
    
    // Build query
    $sql = "SELECT acr.*,
            CONCAT(u.username) as reviewed_by_name
            FROM attendance_correction_requests acr
            LEFT JOIN users u ON acr.reviewed_by = u.id
            WHERE acr.employee_id = ?";
    
    $params = [$employeeId];
    
    if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
        $sql .= " AND acr.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY acr.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM attendance_correction_requests WHERE employee_id = ?";
    $countParams = [$employeeId];
    
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
            'request_date' => $request['request_date'],
            'check_in_time' => $request['check_in_time'],
            'check_out_time' => $request['check_out_time'],
            'reason' => $request['reason'],
            'status' => $request['status'],
            'reviewed_by' => $request['reviewed_by_name'],
            'reviewed_at' => $request['reviewed_at'],
            'rejection_reason' => $request['rejection_reason'],
            'created_at' => $request['created_at']
        ];
    }
    
    sendResponse(true, 'Correction requests retrieved successfully', [
        'requests' => $formattedRequests,
        'pagination' => [
            'current_page' => (int)$page,
            'total_records' => (int)$total,
            'total_pages' => (int)ceil($total / $limit),
            'per_page' => (int)$limit
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Get correction requests error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Get correction requests error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
