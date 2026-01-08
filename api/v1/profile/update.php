<?php
/**
 * Update Profile
 * PUT /api/v1/profile/update.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Require authentication
$tokenData = requireAuth();

// Only allow POST (simulating PUT)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

// Get JSON input
$input = getJsonInput();

try {
    $user = getCurrentUser($tokenData['user_id']);
    
    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }
    
    $userId = $user['id'];
    $employeeId = $user['employee_id'];
    
    // Update user email if provided
    if (isset($input['email']) && !empty($input['email'])) {
        $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            sendError('Invalid email format', 'VALIDATION_ERROR', 400);
        }
        
        // Check if email already exists for other users
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->execute([$email, $userId]);
        if ($checkStmt->fetch()) {
            sendError('Email already in use', 'VALIDATION_ERROR', 400);
        }
        
        $updateUserStmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $updateUserStmt->execute([$email, $userId]);
    }
    
    // Update employee data if user has employee record
    if ($employeeId) {
        $updateFields = [];
        $updateParams = [];
        
        if (isset($input['phone'])) {
            $updateFields[] = "phone = ?";
            $updateParams[] = $input['phone'];
        }
        
        if (isset($input['address'])) {
            $updateFields[] = "address = ?";
            $updateParams[] = $input['address'];
        }
        
        if (!empty($updateFields)) {
            $sql = "UPDATE employees SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $updateParams[] = $employeeId;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateParams);
        }
    }
    
    // Get updated user data
    $updatedUser = getCurrentUser($userId);
    
    $userData = [
        'id' => $updatedUser['id'],
        'username' => $updatedUser['username'],
        'email' => $updatedUser['email'],
        'role' => $updatedUser['role']
    ];
    
    if ($updatedUser['employee_id']) {
        $empStmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $empStmt->execute([$updatedUser['employee_id']]);
        $emp = $empStmt->fetch();
        
        if ($emp) {
            $userData['employee'] = [
                'id' => $emp['id'],
                'employee_code' => $emp['employee_code'],
                'first_name' => $emp['first_name'],
                'last_name' => $emp['last_name'],
                'phone' => $emp['phone'],
                'address' => $emp['address'],
                'photo' => $emp['photo'] ? '/hrm/uploads/employees/' . $emp['photo'] : null
            ];
        }
    }
    
    sendResponse(true, 'Profile updated successfully', $userData);
    
} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    sendError('Server error', 'SERVER_ERROR', 500);
}
