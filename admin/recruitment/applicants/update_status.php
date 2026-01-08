<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request');
}

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

try {
    $sql = "UPDATE job_applications SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $id]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Error updating status: " . $e->getMessage());
    echo json_encode(['success' => false]);
}
