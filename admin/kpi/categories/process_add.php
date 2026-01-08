<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/kpi/categories/');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/kpi/categories/', 'error', 'Token keamanan tidak valid');
}

// Get form data
$category_name = sanitize($_POST['category_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$weight = $_POST['weight'] ?? 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate
if (empty($category_name) || empty($weight)) {
    redirect('/hrm/admin/kpi/categories/', 'error', 'Data tidak lengkap');
}

try {
    // Insert category
    $sql = "INSERT INTO kpi_categories (category_name, description, weight, is_active) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_name, $description, $weight, $is_active]);
    
    redirect('/hrm/admin/kpi/categories/', 'success', 'Kategori KPI berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding KPI category: " . $e->getMessage());
    redirect('/hrm/admin/kpi/categories/', 'error', 'Gagal menambahkan kategori KPI');
}
