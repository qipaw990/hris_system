<?php
/**
 * Attendance History
 * GET /api/v1/attendance/history.php
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
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 30;
    $offset = ($page - 1) * $limit;
    
    // Get month filter if provided
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    
    // Get attendance history
    $sql = "SELECT a.*, ol.location_name, ol.address
            FROM attendance a
            LEFT JOIN office_locations ol ON a.office_location_id = ol.id
            WHERE a.employee_id = ? AND DATE_FORMAT(a.attendance_date, '%Y-%m') = ?
            ORDER BY a.attendance_date DESC, a.check_in DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId, $month, $limit, $offset]);
    $records = $stmt->fetchAll();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendance 
                                WHERE employee_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?");
    $countStmt->execute([$employeeId, $month]);
    $total = $countStmt->fetch()['total'];
    
    // Format records
    $formattedRecords = [];
    foreach ($records as $record) {
        $formattedRecords[] = [
            'id' => (int)$record['id'],
            'date' => $record['attendance_date'],
            'check_in_time' => $record['check_in'],
            'check_out_time' => $record['check_out'],
            'status' => $record['status'],
            'office' => [
                'name' => $record['location_name'] ?? '',
                'address' => $record['address'] ?? ''
            ],
            'distance' => $record['distance_meters'] ? (int)$record['distance_meters'] : 0
        ];
    }
    
    sendResponse(true, 'Attendance history retrieved successfully', [
        'records' => $formattedRecords,
        'pagination' => [
            'current_page' => (int)$page,
            'total_records' => (int)$total,
            'total_pages' => (int)ceil($total / $limit),
            'per_page' => (int)$limit
        ],
        'month' => $month
    ]);
    
} catch (PDOException $e) {
    error_log("Attendance history error: " . $e->getMessage());
    sendError('Database error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Attendance history error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
