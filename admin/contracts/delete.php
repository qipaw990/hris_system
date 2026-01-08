<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    redirect('/hrm/admin/contracts/index.php', 'error', 'Invalid contract ID');
}

try {
    // Get contract details
    $stmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ?");
    $stmt->execute([$id]);
    $contract = $stmt->fetch();
    
    if (!$contract) {
        redirect('/hrm/admin/contracts/index.php', 'error', 'Contract not found');
    }
    
    // Delete file if exists
    if ($contract['contract_file']) {
        $filePath = __DIR__ . '/../../uploads/contracts/' . $contract['contract_file'];
        deleteFile($filePath);
    }
    
    // Delete contract
    $deleteStmt = $pdo->prepare("DELETE FROM contracts WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    redirect('/hrm/admin/contracts/index.php', 'success', 'Contract deleted successfully');
    
} catch (PDOException $e) {
    error_log("Error deleting contract: " . $e->getMessage());
    redirect('/hrm/admin/contracts/index.php', 'error', 'Failed to delete contract');
}
