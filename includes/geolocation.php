<?php
/**
 * Geolocation Helper Functions
 */

/**
 * Calculate distance between two GPS coordinates using Haversine formula
 * @param float $lat1 Latitude of point 1
 * @param float $lon1 Longitude of point 1
 * @param float $lat2 Latitude of point 2
 * @param float $lon2 Longitude of point 2
 * @return float Distance in meters
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Earth radius in meters
    
    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);
    $deltaLat = deg2rad($lat2 - $lat1);
    $deltaLon = deg2rad($lon2 - $lon1);
    
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1Rad) * cos($lat2Rad) *
         sin($deltaLon / 2) * sin($deltaLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    $distance = $earthRadius * $c;
    
    return round($distance, 2); // Return distance in meters
}

/**
 * Check if user location is within allowed radius of office
 * @param float $userLat User's latitude
 * @param float $userLon User's longitude
 * @param float $officeLat Office latitude
 * @param float $officeLon Office longitude
 * @param int $radiusMeters Allowed radius in meters
 * @return bool True if within radius, false otherwise
 */
function isWithinRadius($userLat, $userLon, $officeLat, $officeLon, $radiusMeters) {
    $distance = calculateDistance($userLat, $userLon, $officeLat, $officeLon);
    return $distance <= $radiusMeters;
}

/**
 * Get nearest office location from user's current position
 * @param float $latitude User's latitude
 * @param float $longitude User's longitude
 * @return array|null Office location data with distance, or null if none found
 */
function getNearestOffice($latitude, $longitude) {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM office_locations WHERE is_active = TRUE");
        $offices = $stmt->fetchAll();
        
        if (empty($offices)) {
            return null;
        }
        
        $nearestOffice = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($offices as $office) {
            $distance = calculateDistance(
                $latitude, 
                $longitude, 
                $office['latitude'], 
                $office['longitude']
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestOffice = $office;
                $nearestOffice['distance_meters'] = $distance;
            }
        }
        
        return $nearestOffice;
        
    } catch (PDOException $e) {
        error_log("Error getting nearest office: " . $e->getMessage());
        return null;
    }
}

/**
 * Validate if check-in/out is allowed based on location
 * @param float $latitude User's latitude
 * @param float $longitude User's longitude
 * @return array Result with success status, message, and office info
 */
function validateAttendanceLocation($latitude, $longitude) {
    $nearestOffice = getNearestOffice($latitude, $longitude);
    
    if (!$nearestOffice) {
        return [
            'success' => false,
            'message' => 'Tidak ada lokasi kantor yang aktif',
            'office' => null,
            'distance' => null
        ];
    }
    
    $distance = $nearestOffice['distance_meters'];
    $allowedRadius = $nearestOffice['radius_meters'];
    
    if ($distance <= $allowedRadius) {
        return [
            'success' => true,
            'message' => 'Lokasi valid',
            'office' => $nearestOffice,
            'distance' => $distance
        ];
    } else {
        return [
            'success' => false,
            'message' => sprintf(
                'Anda berada %d meter dari %s. Jarak maksimal: %d meter',
                round($distance),
                $nearestOffice['location_name'],
                $allowedRadius
            ),
            'office' => $nearestOffice,
            'distance' => $distance
        ];
    }
}
