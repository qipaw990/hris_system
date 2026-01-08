<?php
/**
 * Get User Profile
 * GET /api/v1/profile/me.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

try {
    $userId = $tokenData['user_id'];
    
    // Get user and employee data
    $stmt = $pdo->prepare("SELECT u.*, e.*, d.department_name, p.position_name,
                           u.username, u.role, u.email as user_email
                           FROM users u
                           LEFT JOIN employees e ON u.employee_id = e.id
                           LEFT JOIN departments d ON e.department_id = d.id
                           LEFT JOIN positions p ON e.position_id = p.id
                           WHERE u.id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }
    
    // Build photo URL
    $photoUrl = null;
    if (!empty($user['photo'])) {
        // Get base URL from server
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host;
        
        // Remove /api/v1/profile/me.php from the path
        $scriptPath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); // Go up 3 levels
        
        $photoUrl = $baseUrl . $scriptPath . '/assets/uploads/' . $user['photo'];
    }
    
    // Prepare response
    $response = [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'email' => $user['user_email'] ?? $user['email'],
        'employee' => null
    ];
    
    // Add employee data if exists
    if ($user['employee_id']) {
        $response['employee'] = [
            'id' => $user['employee_id'],
            'employee_code' => $user['employee_code'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
            'email' => $user['email'],
            'phone' => $user['phone'],
            'date_of_birth' => $user['date_of_birth'],
            'gender' => $user['gender'],
            'address' => $user['address'],
            'department' => [
                'id' => $user['department_id'],
                'name' => $user['department_name']
            ],
            'position' => [
                'id' => $user['position_id'],
                'name' => $user['position_name']
            ],
            'hire_date' => $user['hire_date'],
            'employment_status' => $user['employment_status'],
            'photo' => $user['photo'],
            'photo_url' => $photoUrl
        ];
    }
    
    sendResponse(true, 'Profile retrieved successfully', $response);
    
} catch (PDOException $e) {
    error_log("Get profile error: " . $e->getMessage());
    sendError('Database error', 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Get profile error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
