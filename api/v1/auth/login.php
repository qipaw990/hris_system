<?php
/**
 * Login Endpoint
 * POST /api/v1/auth/login.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['username', 'password']);

$username = $input['username'];
$password = $input['password'];

try {
    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'Active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendError('Invalid username or password', 'AUTH_FAILED', 401);
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendError('Invalid username or password', 'AUTH_FAILED', 401);
    }
    
    // Get employee data if linked
    $employee = null;
    if ($user['email']) {
        $empStmt = $pdo->prepare("SELECT id, employee_code, first_name, last_name, photo, department_id, position_id 
                                  FROM employees WHERE email = ?");
        $empStmt->execute([$user['email']]);
        $employee = $empStmt->fetch();
    }
    
    // Generate token
    $token = generateToken($user['id'], $user['username'], $user['role']);
    
    // Prepare response
    $userData = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
    
    if ($employee) {
        $userData['employee'] = [
            'id' => $employee['id'],
            'employee_code' => $employee['employee_code'],
            'first_name' => $employee['first_name'],
            'last_name' => $employee['last_name'],
            'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'photo' => $employee['photo'] ? '/hrm/uploads/employees/' . $employee['photo'] : null
        ];
    }
    
    sendResponse(true, 'Login successful', [
        'token' => $token,
        'user' => $userData
    ]);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
