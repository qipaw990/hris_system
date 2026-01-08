<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/departments/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/departments/index.php', 'error', 'Token keamanan tidak valid');
}

// Get form data
$department_name = sanitize($_POST['department_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');

// Validate
if (empty($department_name)) {
    redirect('/hrm/admin/departments/index.php', 'error', 'Nama departemen harus diisi');
}

try {
    // Check if department name exists
    $checkStmt = $pdo->prepare("SELECT id FROM departments WHERE department_name = ?");
    $checkStmt->execute([$department_name]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/departments/index.php', 'error', 'Nama departemen sudah ada');
    }
    
    // Insert department
    $sql = "INSERT INTO departments (department_name, description) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$department_name, $description]);
    
    redirect('/hrm/admin/departments/index.php', 'success', 'Departemen berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding department: " . $e->getMessage());
    redirect('/hrm/admin/departments/index.php', 'error', 'Gagal menambahkan departemen');
}
