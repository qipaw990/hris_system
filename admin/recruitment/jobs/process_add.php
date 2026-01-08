<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/recruitment/jobs/');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/recruitment/jobs/', 'error', 'Invalid security token');
}

$job_title = sanitize($_POST['job_title'] ?? '');
$department_id = $_POST['department_id'] ?? null;
$position_id = $_POST['position_id'] ?? null;
$job_description = sanitize($_POST['job_description'] ?? '');
$requirements = sanitize($_POST['requirements'] ?? '');
$responsibilities = sanitize($_POST['responsibilities'] ?? '');
$salary_range = sanitize($_POST['salary_range'] ?? '');
$employment_type = $_POST['employment_type'] ?? 'Full-Time';
$location = sanitize($_POST['location'] ?? '');
$vacancies = $_POST['vacancies'] ?? 1;
$posted_date = $_POST['posted_date'] ?? date('Y-m-d');
$closing_date = $_POST['closing_date'] ?? null;

if (empty($job_title)) {
    redirect('/hrm/admin/recruitment/jobs/', 'error', 'Job title is required');
}

try {
    $sql = "INSERT INTO job_postings (job_title, department_id, position_id, job_description, requirements, responsibilities, salary_range, employment_type, location, vacancies, posted_date, closing_date, posted_by, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_title, $department_id, $position_id, $job_description, $requirements, $responsibilities, $salary_range, $employment_type, $location, $vacancies, $posted_date, $closing_date, $_SESSION['user_id']]);
    
    redirect('/hrm/admin/recruitment/jobs/', 'success', 'Job posted successfully');
    
} catch (PDOException $e) {
    error_log("Error posting job: " . $e->getMessage());
    redirect('/hrm/admin/recruitment/jobs/', 'error', 'Failed to post job');
}
