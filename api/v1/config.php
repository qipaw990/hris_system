<?php
/**
 * API Configuration
 * Core configuration for REST API
 */

// Set timezone to Jakarta
date_default_timezone_set('Asia/Jakarta');

// Start output buffering to prevent any accidental output
ob_start();

// Suppress all errors/warnings to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include dependencies - wrap in try-catch for API-friendly errors
try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/geolocation.php';
} catch (Exception $e) {
    // Clear any output buffer
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error',
        'error_code' => 'SERVER_ERROR'
    ]);
    exit();
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    // Clear any output buffer
    if (ob_get_length()) ob_end_clean();
    
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Send error response
 */
function sendError($message, $errorCode = 'ERROR', $httpCode = 400) {
    // Clear any output buffer
    if (ob_get_length()) ob_end_clean();
    
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $errorCode
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Get JSON input
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

/**
 * Validate required fields
 */
function validateRequired($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            sendError("Field '$field' is required", 'VALIDATION_ERROR', 400);
        }
    }
}

/**
 * Get Authorization header (handles Apache quirks)
 */
function getAuthHeader() {
    $authHeader = null;
    
    // Method 1: Check Apache-specific variable (most common)
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    // Method 2: Check redirect variable (from .htaccess rewrite)
    elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    // Method 3: Check custom header fallback
    elseif (isset($_SERVER['HTTP_X_AUTH_TOKEN'])) {
        $authHeader = 'Bearer ' . trim($_SERVER['HTTP_X_AUTH_TOKEN']);
    }
    // Method 4: Try getallheaders() if available
    elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = trim($headers['Authorization']);
        } elseif (isset($headers['authorization'])) {
            $authHeader = trim($headers['authorization']);
        }
    }
    
    return $authHeader;
}
