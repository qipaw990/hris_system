<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

// Admin only access
if ($_SESSION['role'] !== 'Admin') {
    redirect('/hrm/admin/index.php', 'error', 'Akses ditolak');
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('/hrm/admin/users/index.php', 'error', 'ID user tidak valid');
}

// Cannot delete self
if ($id == $_SESSION['user_id']) {
    redirect('/hrm/admin/users/index.php', 'error', 'Tidak dapat menghapus akun sendiri');
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect('/hrm/admin/users/index.php', 'error', 'User tidak ditemukan');
    }
    
    // Check if this is the last admin
    if ($user['role'] == 'Admin') {
        $stmt = $pdo->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'Admin' AND status = 'Active'");
        $result = $stmt->fetch();
        if ($result['admin_count'] <= 1) {
            redirect('/hrm/admin/users/index.php', 'error', 'Tidak dapat menghapus admin terakhir');
        }
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    redirect('/hrm/admin/users/index.php', 'success', 'User berhasil dihapus');
    
} catch (PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage());
    redirect('/hrm/admin/users/index.php', 'error', 'Gagal menghapus user');
}
