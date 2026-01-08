<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/positions/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/positions/index.php', 'error', 'Token keamanan tidak valid');
}

// Get form data
$id = $_POST['id'] ?? 0;
$position_name = sanitize($_POST['position_name'] ?? '');
$level = $_POST['level'] ?? null;
$description = sanitize($_POST['description'] ?? '');

// Validate
if (empty($id) || empty($position_name)) {
    redirect('/hrm/admin/positions/index.php', 'error', 'Data tidak lengkap');
}

try {
    // Check if position exists
    $checkStmt = $pdo->prepare("SELECT id FROM positions WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        redirect('/hrm/admin/positions/index.php', 'error', 'Jabatan tidak ditemukan');
    }
    
    // Check if new name already exists (excluding current position)
    $checkNameStmt = $pdo->prepare("SELECT id FROM positions WHERE position_name = ? AND id != ?");
    $checkNameStmt->execute([$position_name, $id]);
    if ($checkNameStmt->fetch()) {
        redirect('/hrm/admin/positions/index.php', 'error', 'Nama jabatan sudah digunakan');
    }
    
    // Update position
    $sql = "UPDATE positions SET position_name = ?, level = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$position_name, $level ?: null, $description, $id]);
    
    redirect('/hrm/admin/positions/index.php', 'success', 'Jabatan berhasil diupdate');
    
} catch (PDOException $e) {
    error_log("Error updating position: " . $e->getMessage());
    redirect('/hrm/admin/positions/index.php', 'error', 'Gagal mengupdate jabatan');
}
