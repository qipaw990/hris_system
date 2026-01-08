<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/recruitment/interviews/');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/recruitment/interviews/', 'error', 'Invalid security token');
}

$application_id = $_POST['application_id'] ?? 0;
$interview_type = $_POST['interview_type'] ?? 'In-Person';
$interview_date = $_POST['interview_date'] ?? '';
$interview_time = $_POST['interview_time'] ?? '';
$location = sanitize($_POST['location'] ?? '');
$meeting_link = sanitize($_POST['meeting_link'] ?? '');
$interviewer_name = sanitize($_POST['interviewer_name'] ?? '');
$notes = sanitize($_POST['notes'] ?? '');

if (empty($application_id) || empty($interview_date) || empty($interview_time)) {
    redirect('/hrm/admin/recruitment/interviews/', 'error', 'Required fields missing');
}

try {
    $sql = "INSERT INTO interviews (application_id, interview_type, interview_date, interview_time, location, meeting_link, interviewer_name, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$application_id, $interview_type, $interview_date, $interview_time, $location, $meeting_link, $interviewer_name, $notes]);
    
    // Update application status to Interview
    $updateSql = "UPDATE job_applications SET status = 'Interview' WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$application_id]);
    
    redirect('/hrm/admin/recruitment/interviews/', 'success', 'Interview scheduled successfully');
    
} catch (PDOException $e) {
    error_log("Error scheduling interview: " . $e->getMessage());
    redirect('/hrm/admin/recruitment/interviews/', 'error', 'Failed to schedule interview');
}
