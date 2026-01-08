<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/employees/index.php');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/employees/index.php', 'error', 'Invalid security token');
}

// Get employee ID
$id = $_POST['id'] ?? 0;
if (empty($id)) {
    redirect('/hrm/admin/employees/index.php', 'error', 'Invalid employee ID');
}

// Get and sanitize form data
$first_name = sanitize($_POST['first_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;
$gender = $_POST['gender'] ?? '';
$address = sanitize($_POST['address'] ?? '');
$department_id = $_POST['department_id'] ?? null;
$position_id = $_POST['position_id'] ?? null;
$hire_date = $_POST['hire_date'] ?? '';
$employment_status = $_POST['employment_status'] ?? 'Active';
$salary = $_POST['salary'] ?? null;
$current_photo = $_POST['current_photo'] ?? '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($email) || empty($gender) || empty($hire_date)) {
    redirect('/hrm/admin/employees/edit.php?id=' . $id, 'error', 'Please fill in all required fields');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/hrm/admin/employees/edit.php?id=' . $id, 'error', 'Invalid email format');
}

// Handle photo upload
$photoFilename = $current_photo;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = uploadFile($_FILES['photo'], __DIR__ . '/../../uploads/employees/');
    
    if ($uploadResult['success']) {
        // Delete old photo if exists
        if ($current_photo) {
            deleteFile(__DIR__ . '/../../uploads/employees/' . $current_photo);
        }
        $photoFilename = $uploadResult['filename'];
    } else {
        redirect('/hrm/admin/employees/edit.php?id=' . $id, 'error', $uploadResult['message']);
    }
}

try {
    // Check if email already exists for other employees
    $checkStmt = $pdo->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
    $checkStmt->execute([$email, $id]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/employees/edit.php?id=' . $id, 'error', 'Email already exists');
    }
    
    // Update employee
    $sql = "UPDATE employees SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, 
                date_of_birth = ?, gender = ?, address = ?, department_id = ?, 
                position_id = ?, hire_date = ?, employment_status = ?, salary = ?, photo = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $phone,
        $date_of_birth ?: null,
        $gender,
        $address,
        $department_id ?: null,
        $position_id ?: null,
        $hire_date,
        $employment_status,
        $salary ?: null,
        $photoFilename,
        $id
    ]);
    
    redirect('/hrm/admin/employees/view.php?id=' . $id, 'success', 'Employee updated successfully');
    
} catch (PDOException $e) {
    error_log("Error updating employee: " . $e->getMessage());
    redirect('/hrm/admin/employees/edit.php?id=' . $id, 'error', 'Failed to update employee. Please try again.');
}
