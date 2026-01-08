<?php
/**
 * Test Authorization Header
 * Test file to debug Authorization header issues
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');

// Collect all possible auth header locations
$debug = [
    'success' => true,
    'message' => 'Authorization header debug info',
    'headers_found' => [],
    'all_http_headers' => [],
    'server_vars' => []
];

// Check all possible locations
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $debug['headers_found']['HTTP_AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'];
}

if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $debug['headers_found']['REDIRECT_HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

if (isset($_SERVER['HTTP_X_AUTH_TOKEN'])) {
    $debug['headers_found']['HTTP_X_AUTH_TOKEN'] = $_SERVER['HTTP_X_AUTH_TOKEN'];
}

// Try apache_request_headers
if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $debug['headers_found']['apache_request_headers'] = $headers['Authorization'];
    }
    $debug['all_http_headers'] = $headers;
}

// Get all HTTP_* server variables
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || strpos($key, 'AUTH') !== false || strpos($key, 'REDIRECT') !== false) {
        $debug['server_vars'][$key] = $value;
    }
}

// Summary
$debug['has_authorization'] = !empty($debug['headers_found']);
$debug['recommendation'] = $debug['has_authorization'] 
    ? '✅ Authorization header is being passed correctly!' 
    : '❌ Authorization header is NOT being passed. Check .htaccess file.';

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
