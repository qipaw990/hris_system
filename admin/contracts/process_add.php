<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/contracts/add.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/contracts/add.php', 'error', 'Invalid security token');
}

// Get form data
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

// Validate
if (empty($contract_number) || empty($employee_id) || empty($contract_type) || empty($start_date)) {
    redirect('/hrm/admin/contracts/add.php', 'error', 'Please fill in all required fields');
}

// Handle file upload
$contractFile = null;
if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = uploadFile($_FILES['contract_file'], __DIR__ . '/../../uploads/contracts/', ['pdf'], 5242880); // 5MB
    
    if ($uploadResult['success']) {
        $contractFile = $uploadResult['filename'];
    } else {
        redirect('/hrm/admin/contracts/add.php', 'error', $uploadResult['message']);
    }
}

try {
    // Check if contract number exists
    $checkStmt = $pdo->prepare("SELECT id FROM contracts WHERE contract_number = ?");
    $checkStmt->execute([$contract_number]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/contracts/add.php', 'error', 'Contract number already exists');
    }
    
    // Insert contract
    $sql = "INSERT INTO contracts (
                contract_number, employee_id, contract_type, start_date, end_date,
                salary, job_title, department_id, position_id, contract_status,
                contract_file, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $contract_number,
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
        $_SESSION['user_id']
    ]);
    
    redirect('/hrm/admin/contracts/index.php', 'success', 'Contract added successfully');
    
} catch (PDOException $e) {
    error_log("Error adding contract: " . $e->getMessage());
    if ($contractFile) {
        deleteFile(__DIR__ . '/../../uploads/contracts/' . $contractFile);
    }
    redirect('/hrm/admin/contracts/add.php', 'error', 'Failed to add contract');
}
