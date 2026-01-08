<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/contracts/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/contracts/index.php', 'error', 'Invalid security token');
}

// Get form data
$id = $_POST['id'] ?? 0;
$contract_number = sanitize($_POST['contract_number'] ?? '');
$employee_id = $_POST['employee_id'] ?? '';
$contract_type = $_POST['contract_type'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? null;
$salary = $_POST['salary'] ?? null;
$job_title = sanitize($_POST['job_title'] ?? '');
$department_id = $_POST['department_id'] ?? null;
$position_id = $_POST['position_id'] ?? null;
$contract_status = $_POST['contract_status'] ?? 'Active';
$notes = sanitize($_POST['notes'] ?? '');
$old_file = $_POST['old_file'] ?? '';

// Validate
if (empty($id) || empty($contract_number) || empty($employee_id) || empty($contract_type) || empty($start_date)) {
    redirect('/hrm/admin/contracts/edit.php?id=' . $id, 'error', 'Please fill in all required fields');
}

// Check if contract exists
try {
    $checkStmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ?");
    $checkStmt->execute([$id]);
    $existingContract = $checkStmt->fetch();
    
    if (!$existingContract) {
        redirect('/hrm/admin/contracts/index.php', 'error', 'Contract not found');
    }
} catch (PDOException $e) {
    error_log("Error checking contract: " . $e->getMessage());
    redirect('/hrm/admin/contracts/index.php', 'error', 'Error updating contract');
}

// Handle file upload
$contractFile = $old_file;
if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = uploadFile($_FILES['contract_file'], __DIR__ . '/../../uploads/contracts/', ['pdf'], 5242880); // 5MB
    
    if ($uploadResult['success']) {
        // Delete old file if exists
        if ($old_file) {
            deleteFile(__DIR__ . '/../../uploads/contracts/' . $old_file);
        }
        $contractFile = $uploadResult['filename'];
    } else {
        redirect('/hrm/admin/contracts/edit.php?id=' . $id, 'error', $uploadResult['message']);
    }
}

try {
    // Update contract
    $sql = "UPDATE contracts SET 
                employee_id = ?,
                contract_type = ?,
                start_date = ?,
                end_date = ?,
                salary = ?,
                job_title = ?,
                department_id = ?,
                position_id = ?,
                contract_status = ?,
                contract_file = ?,
                notes = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $employee_id,
        $contract_type,
        $start_date,
        $end_date ?: null,
        $salary ?: null,
        $job_title,
        $department_id ?: null,
        $position_id ?: null,
        $contract_status,
        $contractFile,
        $notes,
        $id
    ]);
    
    redirect('/hrm/admin/contracts/view.php?id=' . $id, 'success', 'Contract updated successfully');
    
} catch (PDOException $e) {
    error_log("Error updating contract: " . $e->getMessage());
    redirect('/hrm/admin/contracts/edit.php?id=' . $id, 'error', 'Failed to update contract');
}
