<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/locations/add.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/locations/add.php', 'error', 'Token keamanan tidak valid');
}

$location_name = sanitize($_POST['location_name'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);
$radius_meters = intval($_POST['radius_meters'] ?? 100);
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

// Validation
if (empty($location_name)) {
    redirect('/hrm/admin/locations/add.php', 'error', 'Nama lokasi harus diisi');
}

if ($latitude == 0 || $longitude == 0) {
    redirect('/hrm/admin/locations/add.php', 'error', 'Koordinat lokasi harus dipilih di peta');
}

if ($radius_meters < 10 || $radius_meters > 1000) {
    redirect('/hrm/admin/locations/add.php', 'error', 'Radius harus antara 10-1000 meter');
}

try {
    $sql = "INSERT INTO office_locations (location_name, address, latitude, longitude, radius_meters, is_active) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$location_name, $address, $latitude, $longitude, $radius_meters, $is_active]);
    
    redirect('/hrm/admin/locations/index.php', 'success', 'Lokasi kantor berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding location: " . $e->getMessage());
    redirect('/hrm/admin/locations/add.php', 'error', 'Gagal menambahkan lokasi kantor');
}
