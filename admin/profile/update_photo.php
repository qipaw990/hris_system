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

// Validate file upload
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    redirect('/hrm/admin/profile.php', 'error', 'Gagal upload foto');
}

try {
    // Get employee_id
    $stmt = $pdo->prepare("SELECT employee_id, photo FROM users u 
                          LEFT JOIN employees e ON u.employee_id = e.id 
                          WHERE u.id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user['employee_id']) {
        redirect('/hrm/admin/profile.php', 'error', 'User tidak memiliki data karyawan');
    }
    
    // Upload new photo
    $uploadDir = __DIR__ . '/../../assets/uploads/';
    $result = uploadFile($_FILES['photo'], $uploadDir);
    
    if (!$result['success']) {
        redirect('/hrm/admin/profile.php', 'error', $result['message']);
    }
    
    // Delete old photo if exists
    if (!empty($user['photo']) && file_exists($uploadDir . $user['photo'])) {
        unlink($uploadDir . $user['photo']);
    }
    
    // Update photo in database
    $sql = "UPDATE employees SET photo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$result['filename'], $user['employee_id']]);
    
    redirect('/hrm/admin/profile.php', 'success', 'Foto profil berhasil diupdate');
    
} catch (PDOException $e) {
    error_log("Error updating photo: " . $e->getMessage());
    redirect('/hrm/admin/profile.php', 'error', 'Gagal mengupdate foto');
}
