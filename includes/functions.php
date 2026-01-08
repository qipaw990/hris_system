<?php
/**
 * Helper Functions
 * HRIS Management System
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Format date to Indonesian format
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Format currency to Indonesian Rupiah
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Generate employee code
 */
function generateEmployeeCode($pdo) {
    $stmt = $pdo->query("SELECT employee_code FROM employees ORDER BY id DESC LIMIT 1");
    $lastCode = $stmt->fetchColumn();
    
    if ($lastCode) {
        $number = intval(substr($lastCode, 3)) + 1;
    } else {
        $number = 1;
    }
    
    return 'EMP' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

/**
 * Get system setting value
 */
function getSetting($key, $default = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    } catch (PDOException $e) {
        error_log("Error getting setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * Get all settings grouped by category
 */
function getAllSettings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM system_settings ORDER BY setting_category, setting_key");
        $allSettings = $stmt->fetchAll();
        
        $settings = [];
        foreach ($allSettings as $setting) {
            $settings[$setting['setting_category']][$setting['setting_key']] = $setting['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Error getting all settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Upload file handler
 */
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed size (2MB)'];
    }
    
    // Get file extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '.' . $fileExtension;
    $targetPath = $targetDir . $newFileName;
    
    // Create directory if not exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $targetPath];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

/**
 * Delete file
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Redirect with message
 */
function redirect($url, $type = null, $message = null) {
    if ($type && $message) {
        setFlashMessage($type, $message);
    }
    header("Location: $url");
    exit;
}

/**
 * Get employment status badge class
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Active':
            return 'bg-success';
        case 'Inactive':
            return 'bg-secondary';
        case 'On Leave':
            return 'bg-warning';
        case 'Terminated':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get gender icon
 */
function getGenderIcon($gender) {
    switch ($gender) {
        case 'Male':
            return '<i class="fas fa-mars text-primary"></i>';
        case 'Female':
            return '<i class="fas fa-venus text-danger"></i>';
        default:
            return '<i class="fas fa-genderless text-secondary"></i>';
    }
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dateOfBirth) {
    if (empty($dateOfBirth) || $dateOfBirth == '0000-00-00') {
        return '-';
    }
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    $age = $now->diff($dob);
    return $age->y . ' years';
}

/**
 * Get default avatar based on gender
 */
function getDefaultAvatar($gender) {
    if ($gender === 'Female') {
        return '/hrm/assets/images/avatar-female.png';
    }
    return '/hrm/assets/images/avatar-male.png';
}
