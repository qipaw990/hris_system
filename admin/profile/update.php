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
$first_name = sanitize($_POST['first_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$address = sanitize($_POST['address'] ?? '');

if (empty($first_name) || empty($last_name)) {
    redirect('/hrm/admin/profile.php', 'error', 'Nama tidak boleh kosong');
}

try {
    $pdo->beginTransaction();
    
    // Get user email
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Try to find employee record by email
    $empStmt = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
    $empStmt->execute([$user['email']]);
    $employee = $empStmt->fetch();
    
    // Update employees table if employee record exists
    if ($employee) {
        $sql = "UPDATE employees 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $email, $phone, $address, $employee['id']]);
    }
    
    // Also update users table email if changed
    $userSql = "UPDATE users SET email = ? WHERE id = ?";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$email, $userId]);
    
    $pdo->commit();
    redirect('/hrm/admin/profile.php', 'success', 'Profil berhasil diupdate');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error updating profile: " . $e->getMessage());
    redirect('/hrm/admin/profile.php', 'error', 'Gagal mengupdate profil');
}
