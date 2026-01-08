<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/profile.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/profile.php', 'error', 'Token keamanan tidak valid');
}

$userId = $_SESSION['user_id'];
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate
if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    redirect('/hrm/admin/profile.php', 'error', 'Semua field harus diisi');
}

if ($new_password !== $confirm_password) {
    redirect('/hrm/admin/profile.php', 'error', 'Password baru tidak cocok');
}

if (strlen($new_password) < 6) {
    redirect('/hrm/admin/profile.php', 'error', 'Password minimal 6 karakter');
}

try {
    // Get current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        redirect('/hrm/admin/profile.php', 'error', 'Password lama tidak sesuai');
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashed_password, $userId]);
    
    redirect('/hrm/admin/profile.php', 'success', 'Password berhasil diubah');
    
} catch (PDOException $e) {
    error_log("Error changing password: " . $e->getMessage());
    redirect('/hrm/admin/profile.php', 'error', 'Gagal mengubah password');
}
