<?php
/**
 * Authentication Helper
 * Token-based authentication for API
 */

require_once __DIR__ . '/config.php';

/**
 * Generate API token
 */
function generateToken($userId, $username, $role) {
    $payload = [
        'user_id' => $userId,
        'username' => $username,
        'role' => $role,
        'issued_at' => time(),
        'expires_at' => time() + (7 * 24 * 60 * 60) // 7 days
    ];
    
    $secret = 'YOUR_SECRET_KEY_CHANGE_THIS'; // Change this in production
    $token = base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), $secret);
    
    return $token;
}

/**
 * Validate and decode token
 */
function validateToken($token) {
    if (empty($token)) {
        return false;
    }
    
    // Remove "Bearer " prefix if present
    $token = str_replace('Bearer ', '', $token);
    
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        return false;
    }
    
    $payload = json_decode(base64_decode($parts[0]), true);
    $signature = $parts[1];
    
    $secret = 'YOUR_SECRET_KEY_CHANGE_THIS';
    $expectedSignature = hash_hmac('sha256', json_encode($payload), $secret);
    
    if ($signature !== $expectedSignature) {
        return false;
    }
    
    // Check expiration
    if ($payload['expires_at'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Require authentication
 */
function requireAuth() {
    $authHeader = getAuthHeader();
    
    if (!$authHeader) {
        sendError('Authorization header missing', 'AUTH_REQUIRED', 401);
    }
    
    $tokenData = validateToken($authHeader);
    
    if (!$tokenData) {
        sendError('Invalid or expired token', 'INVALID_TOKEN', 401);
    }
    
    return $tokenData;
}

/**
 * Get current user from database
 */
function getCurrentUser($userId) {
    global $pdo;
    
    try {
        // First, get user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'Active'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return null;
        }
        
        // Try to find employee by employee_id column first
        $employee = null;
        if (!empty($user['employee_id'])) {
            $empStmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
            $empStmt->execute([$user['employee_id']]);
            $employee = $empStmt->fetch();
        }
        
        // If not found, try to find by email
        if (!$employee && !empty($user['email'])) {
            $empStmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
            $empStmt->execute([$user['email']]);
            $employee = $empStmt->fetch();
        }
        
        // Merge user and employee data
        $result = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status'],
            'employee_id' => $employee ? $employee['id'] : null,
            'employee_code' => $employee ? $employee['employee_code'] : null,
            'first_name' => $employee ? $employee['first_name'] : null,
            'last_name' => $employee ? $employee['last_name'] : null,
            'photo' => $employee ? $employee['photo'] : null
        ];
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}
