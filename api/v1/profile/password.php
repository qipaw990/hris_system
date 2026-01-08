<?php
/**
 * Change Password
 * POST /api/v1/profile/password.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['current_password', 'new_password']);

$currentPassword = $input['current_password'];
$newPassword = $input['new_password'];
$confirmPassword = $input['confirm_password'] ?? '';

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }
    
    // Get user with password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch();
    
    // Verify current password
    if (!password_verify($currentPassword, $userData['password'])) {
        sendError('Current password is incorrect', 'AUTH_FAILED', 401);
    }
    
    // Validate new password
    if (strlen($newPassword) < 6) {
        sendError('New password must be at least 6 characters', 'VALIDATION_ERROR', 400);
    }
    
    if ($confirmPassword && $newPassword !== $confirmPassword) {
        sendError('New password and confirmation do not match', 'VALIDATION_ERROR', 400);
    }
    
    // Hash and update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $user['id']]);
    
    sendResponse(true, 'Password changed successfully', [
        'message' => 'Your password has been updated. Please use the new password for future logins.'
    ]);
    
} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
