<?php
/**
 * Debug Authentication
 * Test if Authorization header is being received
 */

header('Content-Type: application/json');

$debug = [
    'headers_received' => [],
    'server_vars' => [],
    'auth_header_methods' => []
];

// Check all possible header locations
$debug['server_vars']['HTTP_AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT SET';
$debug['server_vars']['REDIRECT_HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NOT SET';
$debug['server_vars']['HTTP_X_AUTH_TOKEN'] = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? 'NOT SET';

// Try apache_request_headers
if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $debug['headers_received'] = $headers;
}

// Try getallheaders
if (function_exists('getallheaders')) {
    $debug['getallheaders'] = getallheaders();
}

// Test getAuthHeader function
require_once __DIR__ . '/../../config.php';
$authHeader = getAuthHeader();
$debug['getAuthHeader_result'] = $authHeader ?? 'NULL';

// Test with sample token
if ($authHeader) {
    require_once __DIR__ . '/../../auth.php';
    $tokenData = validateToken($authHeader);
    $debug['token_validation'] = $tokenData ? 'VALID' : 'INVALID';
    if ($tokenData) {
        $debug['token_data'] = $tokenData;
    }
}

echo json_encode($debug, JSON_PRETTY_PRINT);
