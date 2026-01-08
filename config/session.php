<?php
/**
 * Session Configuration
 * HRIS Management System
 */

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Session lifetime (1 hour)
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /hrm/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!hasRole('admin') && !hasRole('hr')) {
        header('Location: /hrm/admin/index.php');
        exit;
    }
}
