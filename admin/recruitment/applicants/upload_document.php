<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/recruitment/applicants/');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/recruitment/applicants/', 'error', 'Invalid security token');
}

$applicant_id = $_POST['applicant_id'] ?? 0;
$document_type = $_POST['document_type'] ?? '';
$document_name = sanitize($_POST['document_name'] ?? '');

if (empty($applicant_id) || empty($document_type)) {
    redirect('/hrm/admin/recruitment/applicants/', 'error', 'Required fields missing');
}

// Validate file upload
if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    redirect('/hrm/admin/recruitment/applicants/', 'error', 'File upload failed');
}

try {
    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../../../uploads/applicant_documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Upload file
    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $maxSize = 5242880; // 5MB
    
    $result = uploadFile($_FILES['document_file'], $uploadDir, $allowedTypes, $maxSize);
    
    if (!$result['success']) {
        redirect('/hrm/admin/recruitment/applicants/', 'error', $result['message']);
    }
    
    // Use document name from form or filename
    if (empty($document_name)) {
        $document_name = pathinfo($_FILES['document_file']['name'], PATHINFO_FILENAME);
    }
    
    // Save to database
    $file_path = 'uploads/applicant_documents/' . $result['filename'];
    $file_size = $_FILES['document_file']['size'];
    
    $sql = "INSERT INTO applicant_documents (applicant_id, document_type, document_name, file_path, file_size) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$applicant_id, $document_type, $document_name, $file_path, $file_size]);
    
    redirect('/hrm/admin/recruitment/applicants/', 'success', 'Document uploaded successfully');
    
} catch (PDOException $e) {
    error_log("Error uploading document: " . $e->getMessage());
    redirect('/hrm/admin/recruitment/applicants/', 'error', 'Failed to upload document');
}
