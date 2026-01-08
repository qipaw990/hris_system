<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in and is admin/hr
requireLogin();
if (!in_array($_SESSION['role'], ['admin', 'hr'])) {
    $_SESSION['error'] = 'Akses ditolak';
    header('Location: /hrm/admin/locations/index.php');
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: /hrm/admin/locations/index.php');
    exit();
}

// Get and validate input
$id = $_POST['id'] ?? null;
$locationName = trim($_POST['location_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);
$radiusMeters = intval($_POST['radius_meters'] ?? 100);
$isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

// Validate required fields
if (empty($id) || empty($locationName) || $latitude == 0 || $longitude == 0) {
    $_SESSION['error'] = 'Semua field wajib diisi dengan benar';
    header('Location: /hrm/admin/locations/edit.php?id=' . $id);
    exit();
}

// Validate radius
if ($radiusMeters < 10 || $radiusMeters > 1000) {
    $_SESSION['error'] = 'Radius harus antara 10-1000 meter';
    header('Location: /hrm/admin/locations/edit.php?id=' . $id);
    exit();
}

try {
    // Check if location exists
    $checkStmt = $pdo->prepare("SELECT id FROM office_locations WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        $_SESSION['error'] = 'Lokasi tidak ditemukan';
        header('Location: /hrm/admin/locations/index.php');
        exit();
    }
    
    // Check for duplicate name (excluding current location)
    $duplicateStmt = $pdo->prepare("SELECT id FROM office_locations WHERE location_name = ? AND id != ?");
    $duplicateStmt->execute([$locationName, $id]);
    if ($duplicateStmt->fetch()) {
        $_SESSION['error'] = 'Nama lokasi sudah digunakan';
        header('Location: /hrm/admin/locations/edit.php?id=' . $id);
        exit();
    }
    
    // Update location
    $sql = "UPDATE office_locations SET 
            location_name = ?,
            address = ?,
            latitude = ?,
            longitude = ?,
            radius_meters = ?,
            is_active = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $locationName,
        $address,
        $latitude,
        $longitude,
        $radiusMeters,
        $isActive,
        $id
    ]);
    
    $_SESSION['success'] = 'Lokasi kantor berhasil diperbarui';
    header('Location: /hrm/admin/locations/index.php');
    exit();
    
} catch (PDOException $e) {
    error_log("Error updating location: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal memperbarui lokasi: ' . $e->getMessage();
    header('Location: /hrm/admin/locations/edit.php?id=' . $id);
    exit();
}
