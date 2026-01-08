<?php
/**
 * Authentication Handler
 * HRIS Management System
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/login.php');
}

// Get form data
$username = sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($username) || empty($password)) {
    redirect('/hrm/login.php', 'error', 'Please fill in all fields');
}

try {
    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Verify user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        // Set remember me cookie if checked
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
        }
        
        // Redirect to admin dashboard
        redirect('/hrm/admin/index.php', 'success', 'Welcome back, ' . $user['username'] . '!');
    } else {
        // Invalid credentials
        redirect('/hrm/login.php', 'error', 'Invalid username or password');
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    redirect('/hrm/login.php', 'error', 'An error occurred. Please try again.');
}
