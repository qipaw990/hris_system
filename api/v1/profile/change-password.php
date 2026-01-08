<?php
/**
 * Change Password
 * POST /api/v1/profile/change-password.php
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
validateRequired($input, ['current_password', 'new_password', 'confirm_password']);

$currentPassword = $input['current_password'];
$newPassword = $input['new_password'];
$confirmPassword = $input['confirm_password'];

try {
    $userId = $tokenData['user_id'];
    
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        sendError('Password saat ini salah', 'INVALID_CURRENT_PASSWORD', 400);
    }
    
    // Validate new password
    if (strlen($newPassword) < 6) {
        sendError('Password baru minimal 6 karakter', 'PASSWORD_TOO_SHORT', 400);
    }
    
    // Check if new password same as current
    if ($currentPassword === $newPassword) {
        sendError('Password baru tidak boleh sama dengan password lama', 'SAME_PASSWORD', 400);
    }
    
    // Validate password confirmation
    if ($newPassword !== $confirmPassword) {
        sendError('Konfirmasi password tidak cocok', 'PASSWORD_MISMATCH', 400);
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $updateStmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $userId]);
    
    // Try to log the password change (optional, won't fail if table doesn't exist)
    try {
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) 
                                  VALUES (?, 'password_change', 'Password changed via mobile app', NOW())");
        $logStmt->execute([$userId]);
    } catch (PDOException $logError) {
        // Ignore if activity_logs table doesn't exist
        error_log("Activity log failed (table may not exist): " . $logError->getMessage());
    }
    
    sendResponse(true, 'Password berhasil diubah', [
        'user_id' => $userId,
        'username' => $user['username'],
        'changed_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    sendError('Database error', 'SERVER_ERROR', 500);
} catch (Exception $e) {
    error_log("Change password error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
