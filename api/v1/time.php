<?php
/**
 * Get Server Time
 * GET /api/v1/time.php
 * Returns current server time in Jakarta timezone
 */

require_once __DIR__ . '/config.php';

// Get current time in various formats
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

sendResponse(true, 'Server time retrieved successfully', [
    'timezone' => 'Asia/Jakarta',
    'timezone_offset' => '+07:00',
    'current_time' => $now->format('H:i:s'),
    'current_date' => $now->format('Y-m-d'),
    'current_datetime' => $now->format('Y-m-d H:i:s'),
    'timestamp' => $now->getTimestamp(),
    'day_of_week' => $now->format('l'),
    'formatted' => [
        'time_12h' => $now->format('h:i:s A'),
        'date_indo' => $now->format('d/m/Y'),
        'datetime_indo' => $now->format('d/m/Y H:i:s'),
        'full' => $now->format('l, d F Y H:i:s')
    ]
]);
