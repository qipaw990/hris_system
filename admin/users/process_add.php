<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

// Admin only access
if ($_SESSION['role'] !== 'Admin') {
    redirect('/hrm/admin/index.php', 'error', 'Akses ditolak');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/users/add.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/users/add.php', 'error', 'Token keamanan tidak valid');
}

$username = sanitize($_POST['username'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$role = $_POST['role'] ?? 'Employee';
$employee_id = !empty($_POST['employee_id']) ? intval($_POST['employee_id']) : null;
$status = $_POST['status'] ?? 'Active';

// Validation
if (empty($username) || empty($email) || empty($password)) {
    redirect('/hrm/admin/users/add.php', 'error', 'Username, email, dan password harus diisi');
}

if ($password !== $password_confirm) {
    redirect('/hrm/admin/users/add.php', 'error', 'Password dan konfirmasi password tidak cocok');
}

if (strlen($password) < 6) {
    redirect('/hrm/admin/users/add.php', 'error', 'Password minimal 6 karakter');
}

if (!in_array($role, ['Admin', 'HR', 'Employee'])) {
    redirect('/hrm/admin/users/add.php', 'error', 'Role tidak valid');
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        redirect('/hrm/admin/users/add.php', 'error', 'Username sudah digunakan');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirect('/hrm/admin/users/add.php', 'error', 'Email sudah digunakan');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (username, email, password, role, employee_id, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $email, $hashedPassword, $role, $employee_id, $status]);
    
    redirect('/hrm/admin/users/index.php', 'success', 'User berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding user: " . $e->getMessage());
    redirect('/hrm/admin/users/add.php', 'error', 'Gagal menambahkan user');
}
