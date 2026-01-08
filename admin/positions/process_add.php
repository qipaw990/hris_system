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
$position_name = sanitize($_POST['position_name'] ?? '');
$level = $_POST['level'] ?? null;
$description = sanitize($_POST['description'] ?? '');

// Validate
if (empty($position_name)) {
    redirect('/hrm/admin/positions/index.php', 'error', 'Nama jabatan harus diisi');
}

try {
    // Check if position name exists
    $checkStmt = $pdo->prepare("SELECT id FROM positions WHERE position_name = ?");
    $checkStmt->execute([$position_name]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/positions/index.php', 'error', 'Nama jabatan sudah ada');
    }
    
    // Insert position
    $sql = "INSERT INTO positions (position_name, level, description) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$position_name, $level ?: null, $description]);
    
    redirect('/hrm/admin/positions/index.php', 'success', 'Jabatan berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding position: " . $e->getMessage());
    redirect('/hrm/admin/positions/index.php', 'error', 'Gagal menambahkan jabatan');
}
