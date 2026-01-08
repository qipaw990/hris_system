<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/kpi/indicators/');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/kpi/indicators/', 'error', 'Token keamanan tidak valid');
}

// Get form data
$category_id = $_POST['category_id'] ?? 0;
$indicator_name = sanitize($_POST['indicator_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$measurement_type = $_POST['measurement_type'] ?? 'Numeric';
$target_value = $_POST['target_value'] ?? 0;
$weight = $_POST['weight'] ?? 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate
if (empty($category_id) || empty($indicator_name)) {
    redirect('/hrm/admin/kpi/indicators/', 'error', 'Data tidak lengkap');
}

try {
    // Insert indicator
    $sql = "INSERT INTO kpi_indicators (category_id, indicator_name, description, measurement_type, target_value, weight, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id, $indicator_name, $description, $measurement_type, $target_value, $weight, $is_active]);
    
    redirect('/hrm/admin/kpi/indicators/', 'success', 'Indikator KPI berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding KPI indicator: " . $e->getMessage());
    redirect('/hrm/admin/kpi/indicators/', 'error', 'Gagal menambahkan indikator KPI');
}
