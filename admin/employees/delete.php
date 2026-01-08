<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

// Get employee ID
$id = $_GET['id'] ?? 0;

if (empty($id)) {
    redirect('/hrm/admin/employees/index.php', 'error', 'Invalid employee ID');
}

try {
    // Get employee details
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect('/hrm/admin/employees/index.php', 'error', 'Employee not found');
    }
    
    // Delete photo if exists
    if ($employee['photo']) {
        $photoPath = __DIR__ . '/../../uploads/employees/' . $employee['photo'];
        deleteFile($photoPath);
    }
    
    // Delete employee
    $deleteStmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    redirect('/hrm/admin/employees/index.php', 'success', 'Employee deleted successfully');
    
} catch (PDOException $e) {
    error_log("Error deleting employee: " . $e->getMessage());
    redirect('/hrm/admin/employees/index.php', 'error', 'Failed to delete employee');
}
