<?php
/**
 * Logout Endpoint
 * POST /api/v1/auth/logout.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

// In a real implementation, you would invalidate the token in database
// For now, we just return success and client should delete the token

sendResponse(true, 'Logout successful', [
    'message' => 'Token has been invalidated. Please delete the token from your device.'
]);
