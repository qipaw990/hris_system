<?php
/**
 * Process Registration
 * HRIS Management System
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/register.php');
}

// Get and sanitize form data
$username = sanitize($_POST['username'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

// Validate input
if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
    redirect('/hrm/register.php', 'error', 'Please fill in all fields');
}

// Validate username length
if (strlen($username) < 3) {
    redirect('/hrm/register.php', 'error', 'Username must be at least 3 characters long');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/hrm/register.php', 'error', 'Invalid email format');
}

// Validate password length
if (strlen($password) < 6) {
    redirect('/hrm/register.php', 'error', 'Password must be at least 6 characters long');
}

// Check if passwords match
if ($password !== $confirm_password) {
    redirect('/hrm/register.php', 'error', 'Passwords do not match');
}

// Validate role
$allowed_roles = ['admin', 'hr', 'employee'];
if (!in_array($role, $allowed_roles)) {
    redirect('/hrm/register.php', 'error', 'Invalid role selected');
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        redirect('/hrm/register.php', 'error', 'Username already exists');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirect('/hrm/register.php', 'error', 'Email already exists');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$username, $email, $hashed_password, $role]);
    
    // Redirect to login with success message
    redirect('/hrm/login.php', 'success', 'Account created successfully! Please login.');
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    redirect('/hrm/register.php', 'error', 'An error occurred. Please try again.');
}
