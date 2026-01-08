<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/employees/add.php');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/employees/add.php', 'error', 'Invalid security token');
}

// Get and sanitize form data
$first_name = sanitize($_POST['first_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;
$gender = $_POST['gender'] ?? '';
$address = sanitize($_POST['address'] ?? '');
$employee_code = sanitize($_POST['employee_code'] ?? '');
$department_id = $_POST['department_id'] ?? null;
$position_id = $_POST['position_id'] ?? null;
$hire_date = $_POST['hire_date'] ?? '';
$employment_status = $_POST['employment_status'] ?? 'Active';
$salary = $_POST['salary'] ?? null;

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($email) || empty($gender) || empty($employee_code) || empty($hire_date)) {
    redirect('/hrm/admin/employees/add.php', 'error', 'Please fill in all required fields');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/hrm/admin/employees/add.php', 'error', 'Invalid email format');
}

// Handle photo upload
$photoFilename = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = uploadFile($_FILES['photo'], __DIR__ . '/../../uploads/employees/');
    
    if ($uploadResult['success']) {
        $photoFilename = $uploadResult['filename'];
    } else {
        redirect('/hrm/admin/employees/add.php', 'error', $uploadResult['message']);
    }
}

try {
    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/employees/add.php', 'error', 'Email already exists');
    }
    
    // Check if employee code already exists
    $checkStmt = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ?");
    $checkStmt->execute([$employee_code]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/employees/add.php', 'error', 'Employee code already exists');
    }
    
    // Insert employee
    $sql = "INSERT INTO employees (
                employee_code, first_name, last_name, email, phone, 
                date_of_birth, gender, address, department_id, position_id, 
                hire_date, employment_status, salary, photo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $employee_code,
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
        $photoFilename
    ]);
    
    redirect('/hrm/admin/employees/index.php', 'success', 'Employee added successfully');
    
} catch (PDOException $e) {
    error_log("Error adding employee: " . $e->getMessage());
    
    // Delete uploaded photo if database insert fails
    if ($photoFilename) {
        deleteFile(__DIR__ . '/../../uploads/employees/' . $photoFilename);
    }
    
    redirect('/hrm/admin/employees/add.php', 'error', 'Failed to add employee. Please try again.');
}
