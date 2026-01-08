<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('/hrm/admin/locations/index.php', 'error', 'ID lokasi tidak valid');
}

try {
    // Check if location exists
    $stmt = $pdo->prepare("SELECT * FROM office_locations WHERE id = ?");
    $stmt->execute([$id]);
    $location = $stmt->fetch();
    
    if (!$location) {
        redirect('/hrm/admin/locations/index.php', 'error', 'Lokasi tidak ditemukan');
    }
    
    // Delete location
    $stmt = $pdo->prepare("DELETE FROM office_locations WHERE id = ?");
    $stmt->execute([$id]);
    
    redirect('/hrm/admin/locations/index.php', 'success', 'Lokasi berhasil dihapus');
    
} catch (PDOException $e) {
    error_log("Error deleting location: " . $e->getMessage());
    redirect('/hrm/admin/locations/index.php', 'error', 'Gagal menghapus lokasi');
}
