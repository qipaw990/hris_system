<?php
/**
 * Debug User-Employee Linking
 * Test endpoint to check user-employee relationship
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Require authentication
$tokenData = requireAuth();

try {
    $userId = $tokenData['user_id'];
    
    // Get user data
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    
    // Try to find employee by employee_id
    $employeeById = null;
    if (!empty($user['employee_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$user['employee_id']]);
        $employeeById = $stmt->fetch();
    }
    
    // Try to find employee by email
    $employeeByEmail = null;
    if (!empty($user['email'])) {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->execute([$user['email']]);
        $employeeByEmail = $stmt->fetch();
    }
    
    // Get current user using the function
    $currentUser = getCurrentUser($userId);
    
    sendResponse(true, 'Debug info retrieved', [
        'token_data' => $tokenData,
        'user_from_db' => $user,
        'employee_by_id' => $employeeById ? [
            'id' => $employeeById['id'],
            'employee_code' => $employeeById['employee_code'],
            'name' => $employeeById['first_name'] . ' ' . $employeeById['last_name'],
            'email' => $employeeById['email']
        ] : null,
        'employee_by_email' => $employeeByEmail ? [
            'id' => $employeeByEmail['id'],
            'employee_code' => $employeeByEmail['employee_code'],
            'name' => $employeeByEmail['first_name'] . ' ' . $employeeByEmail['last_name'],
            'email' => $employeeByEmail['email']
        ] : null,
        'current_user_function_result' => $currentUser,
        'has_employee_link' => !empty($currentUser['employee_id']),
        'recommendation' => !empty($currentUser['employee_id']) 
            ? '✅ Employee link found!' 
            : '❌ No employee link. Check users.employee_id or users.email matches employees.email'
    ]);
    
} catch (PDOException $e) {
    error_log("Debug error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
