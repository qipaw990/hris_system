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

$id = $_POST['id'] ?? 0;
$job_title = sanitize($_POST['job_title'] ?? '');
$department_id = $_POST['department_id'] ?? null;
$location = sanitize($_POST['location'] ?? '');
$status = $_POST['status'] ?? 'Open';
$closing_date = $_POST['closing_date'] ?? null;

if (empty($id) || empty($job_title)) {
    redirect('/hrm/admin/recruitment/jobs/', 'error', 'Invalid data');
}

try {
    $sql = "UPDATE job_postings 
            SET job_title = ?, department_id = ?, location = ?, status = ?, closing_date = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_title, $department_id, $location, $status, $closing_date, $id]);
    
    redirect('/hrm/admin/recruitment/jobs/', 'success', 'Job updated successfully');
    
} catch (PDOException $e) {
    error_log("Error updating job: " . $e->getMessage());
    redirect('/hrm/admin/recruitment/jobs/', 'error', 'Failed to update job');
}
