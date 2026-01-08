<?php
/**
 * Get System Configuration
 * GET /api/v1/config.php
 * Returns system configuration for mobile app
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    // Get work hours configuration (you can store this in database later)
    $workConfig = [
        'work_start_time' => '08:00:00',
        'work_end_time' => '17:00:00',
        'late_tolerance_minutes' => 15,
        'break_duration_minutes' => 60,
        'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
        'weekend_days' => ['Saturday', 'Sunday']
    ];
    
    // Get timezone info
    $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $timezoneInfo = [
        'timezone' => 'Asia/Jakarta',
        'timezone_name' => 'Western Indonesia Time',
        'timezone_abbr' => 'WIB',
        'offset' => '+07:00',
        'current_time' => $now->format('H:i:s'),
        'current_date' => $now->format('Y-m-d')
    ];
    
    // Get app configuration
    $appConfig = [
        'app_name' => 'HRIS Mobile',
        'app_version' => '1.0.0',
        'api_version' => 'v1',
        'min_app_version' => '1.0.0',
        'force_update' => false
    ];
    
    // Get attendance configuration
    $attendanceConfig = [
        'geofencing_enabled' => true,
        'default_radius_meters' => 100,
        'allow_outside_radius' => false,
        'require_photo' => false,
        'auto_checkout_enabled' => false,
        'auto_checkout_time' => '18:00:00'
    ];
    
    sendResponse(true, 'System configuration retrieved successfully', [
        'timezone' => $timezoneInfo,
        'work_hours' => $workConfig,
        'app' => $appConfig,
        'attendance' => $attendanceConfig
    ]);
    
} catch (Exception $e) {
    error_log("Config error: " . $e->getMessage());
    sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}
