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
$id = $_POST['id'] ?? 0;
$category_name = sanitize($_POST['category_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$weight = $_POST['weight'] ?? 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate
if (empty($id) || empty($category_name) || empty($weight)) {
    redirect('/hrm/admin/kpi/categories/', 'error', 'Data tidak lengkap');
}

try {
    // Update category
    $sql = "UPDATE kpi_categories 
            SET category_name = ?, description = ?, weight = ?, is_active = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_name, $description, $weight, $is_active, $id]);
    
    redirect('/hrm/admin/kpi/categories/', 'success', 'Kategori KPI berhasil diupdate');
    
} catch (PDOException $e) {
    error_log("Error updating KPI category: " . $e->getMessage());
    redirect('/hrm/admin/kpi/categories/', 'error', 'Gagal mengupdate kategori KPI');
}
