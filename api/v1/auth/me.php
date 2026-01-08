<?php
/**
 * Get Current User Info
 * GET /api/v1/auth/me.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }
    
    $userData = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'status' => $user['status']
    ];
    
    if ($user['employee_id']) {
        $userData['employee'] = [
            'id' => $user['employee_id'],
            'employee_code' => $user['employee_code'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'full_name' => $user['first_name'] . ' ' . $user['last_name'],
            'photo' => $user['photo'] ? '/hrm/uploads/employees/' . $user['photo'] : null
        ];
    }
    
    sendResponse(true, 'User info retrieved successfully', $userData);
    
} catch (PDOException $e) {
    error_log("Get user info error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
