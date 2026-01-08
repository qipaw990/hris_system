<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /hrm/careers/');
    exit;
}

// Get form data
$job_id = $_POST['job_id'] ?? 0;
$first_name = sanitize($_POST['first_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$education_level = sanitize($_POST['education_level'] ?? '');
$years_of_experience = $_POST['years_of_experience'] ?? 0;
$current_company = sanitize($_POST['current_company'] ?? '');
$current_position = sanitize($_POST['current_position'] ?? '');
$expected_salary = $_POST['expected_salary'] ?? 0;
$cover_letter = sanitize($_POST['cover_letter'] ?? '');
$linkedin_url = sanitize($_POST['linkedin_url'] ?? '');
$portfolio_url = sanitize($_POST['portfolio_url'] ?? '');

// Validate required fields
if (empty($job_id) || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    header('Location: /hrm/careers/?error=missing_fields');
    exit;
}

// Validate resume upload
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /hrm/careers/?error=resume_required');
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Check if applicant already exists by email
    $checkStmt = $pdo->prepare("SELECT id FROM applicants WHERE email = ?");
    $checkStmt->execute([$email]);
    $existingApplicant = $checkStmt->fetch();
    
    if ($existingApplicant) {
        $applicant_id = $existingApplicant['id'];
        
        // Update existing applicant
        $updateSql = "UPDATE applicants 
                     SET first_name = ?, last_name = ?, phone = ?, address = ?, 
                         education_level = ?, years_of_experience = ?, current_company = ?, 
                         current_position = ?, expected_salary = ?, cover_letter = ?, 
                         linkedin_url = ?, portfolio_url = ?
                     WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$first_name, $last_name, $phone, $address, $education_level, 
                             $years_of_experience, $current_company, $current_position, 
                             $expected_salary, $cover_letter, $linkedin_url, $portfolio_url, $applicant_id]);
    } else {
        // Create new applicant
        $insertSql = "INSERT INTO applicants (first_name, last_name, email, phone, address, 
                     education_level, years_of_experience, current_company, current_position, 
                     expected_salary, cover_letter, linkedin_url, portfolio_url) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$first_name, $last_name, $email, $phone, $address, $education_level, 
                             $years_of_experience, $current_company, $current_position, 
                             $expected_salary, $cover_letter, $linkedin_url, $portfolio_url]);
        $applicant_id = $pdo->lastInsertId();
    }
    
    // Upload resume
    $uploadDir = __DIR__ . '/../uploads/applicant_documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['pdf', 'doc', 'docx'];
    $maxSize = 5242880; // 5MB
    
    $result = uploadFile($_FILES['resume'], $uploadDir, $allowedTypes, $maxSize);
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    // Save resume to documents table
    $file_path = 'uploads/applicant_documents/' . $result['filename'];
    $file_size = $_FILES['resume']['size'];
    $document_name = $first_name . ' ' . $last_name . ' - Resume';
    
    $docSql = "INSERT INTO applicant_documents (applicant_id, document_type, document_name, file_path, file_size) 
               VALUES (?, 'Resume', ?, ?, ?)";
    $docStmt = $pdo->prepare($docSql);
    $docStmt->execute([$applicant_id, $document_name, $file_path, $file_size]);
    
    // Create job application
    $appSql = "INSERT INTO job_applications (job_id, applicant_id, application_date, status) 
               VALUES (?, ?, CURDATE(), 'Applied')";
    $appStmt = $pdo->prepare($appSql);
    $appStmt->execute([$job_id, $applicant_id]);
    
    $pdo->commit();
    
    header('Location: /hrm/careers/?success=application_submitted');
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error submitting application: " . $e->getMessage());
    header('Location: /hrm/careers/?error=submission_failed');
}
