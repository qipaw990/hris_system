<?php
/**
 * Get Office Locations
 * GET /api/v1/attendance/offices.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    // Get all active office locations
    $stmt = $pdo->query("SELECT id, location_name, address, latitude, longitude, radius_meters 
                         FROM office_locations 
                         WHERE is_active = TRUE 
                         ORDER BY location_name ASC");
    $offices = $stmt->fetchAll();
    
    sendResponse(true, 'Office locations retrieved successfully', [
        'offices' => $offices,
        'count' => count($offices)
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting offices: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
