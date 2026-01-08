<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/settings.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/settings.php', 'error', 'Token keamanan tidak valid');
}

$category = $_POST['category'] ?? '';
unset($_POST['csrf_token'], $_POST['category']);

try {
    $pdo->beginTransaction();
    
    // Update each setting
    foreach ($_POST as $key => $value) {
        // Handle checkboxes (if not set, value is 0)
        if (!isset($_POST[$key]) && strpos($key, 'notification') !== false) {
            $value = '0';
        }
        
        $sql = "UPDATE system_settings 
                SET setting_value = ? 
                WHERE setting_key = ? AND setting_category = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$value, $key, $category]);
    }
    
    // Handle unchecked checkboxes for notification settings
    if ($category == 'notification') {
        $checkboxes = ['email_notifications', 'leave_approval_notification', 'payroll_notification', 'birthday_notification'];
        foreach ($checkboxes as $checkbox) {
            if (!isset($_POST[$checkbox])) {
                $sql = "UPDATE system_settings 
                        SET setting_value = '0' 
                        WHERE setting_key = ? AND setting_category = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$checkbox, $category]);
            }
        }
    }
    
    $pdo->commit();
    redirect('/hrm/admin/settings.php', 'success', 'Pengaturan berhasil disimpan');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error updating settings: " . $e->getMessage());
    redirect('/hrm/admin/settings.php', 'error', 'Gagal menyimpan pengaturan');
}
