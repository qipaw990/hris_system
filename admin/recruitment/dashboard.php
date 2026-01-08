<?php
$page_title = 'Recruitment Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

try {
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM job_postings WHERE status = 'Open') as open_jobs,
        (SELECT COUNT(*) FROM job_applications WHERE status IN ('Applied', 'Screening', 'Interview')) as active_applications,
        (SELECT COUNT(*) FROM interviews WHERE status = 'Scheduled' AND interview_date >= CURDATE()) as upcoming_interviews,
        (SELECT COUNT(*) FROM job_applications WHERE status = 'Hired' AND MONTH(updated_at) = MONTH(CURDATE())) as hired_this_month");
    $stats = $statsStmt->fetch();
    
    // Get active job postings
    $jobsStmt = $pdo->query("SELECT jp.*, d.department_name, p.position_name,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jp.id) as application_count
        FROM job_postings jp
        LEFT JOIN departments d ON jp.department_id = d.id
        LEFT JOIN positions p ON jp.position_id = p.id
        WHERE jp.status = 'Open'
        ORDER BY jp.posted_date DESC
        LIMIT 5");
    $activeJobs = $jobsStmt->fetchAll();
    
    // Get recent applications
    $appsStmt = $pdo->query("SELECT ja.*, jp.job_title, 
        CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
        a.email, a.phone
        FROM job_applications ja
        LEFT JOIN job_postings jp ON ja.job_id = jp.id
        LEFT JOIN applicants a ON ja.applicant_id = a.id
        ORDER BY ja.application_date DESC
        LIMIT 10");
    $recentApps = $appsStmt->fetchAll();
    
    // Get upcoming interviews
    $interviewsStmt = $pdo->query("SELECT i.*, 
        CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
        jp.job_title
        FROM interviews i
        LEFT JOIN job_applications ja ON i.application_id = ja.id
        LEFT JOIN applicants a ON ja.applicant_id = a.id
        LEFT JOIN job_postings jp ON ja.job_id = jp.id
        WHERE i.status = 'Scheduled' AND i.interview_date >= CURDATE()
        ORDER BY i.interview_date, i.interview_time
        LIMIT 5");
    $upcomingInterviews = $interviewsStmt->fetchAll();
    
    // Get application status distribution
    $statusStmt = $pdo->query("SELECT status, COUNT(*) as count 
        FROM job_applications 
        GROUP BY status");
    $statusDist = $statusStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching recruitment data: " . $e->getMessage());
    $stats = [];
    $activeJobs = [];
    $recentApps = [];
    $upcomingInterviews = [];
    $statusDist = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-user-tie me-2"></i> Recruitment Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Recruitment</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/recruitment/jobs/" class="btn btn-primary btn-sm">
                <i class="fas fa-briefcase me-2"></i> Manage Jobs
            </a>
            <a href="/hrm/admin/recruitment/applicants/" class="btn btn-success btn-sm">
                <i class="fas fa-users me-2"></i> View Applicants
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Open Positions</p>
                        <h3 class="mb-0"><?php echo $stats['open_jobs'] ?? 0; ?></h3>
                        <small class="text-primary">
                            <i class="fas fa-briefcase"></i> Active Jobs
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Active Applications</p>
                        <h3 class="mb-0"><?php echo $stats['active_applications'] ?? 0; ?></h3>
                        <small class="text-info">
                            <i class="fas fa-file-alt"></i> In Progress
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Upcoming Interviews</p>
                        <h3 class="mb-0"><?php echo $stats['upcoming_interviews'] ?? 0; ?></h3>
                        <small class="text-warning">
                            <i class="fas fa-calendar-alt"></i> Scheduled
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Hired This Month</p>
                        <h3 class="mb-0"><?php echo $stats['hired_this_month'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-user-check"></i> New Hires
                        </small>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Active Job Postings -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i> Active Job Postings</h5>
                <a href="/hrm/admin/recruitment/jobs/" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($activeJobs)): ?>
                    <p class="text-muted text-center py-3">No active job postings</p>
                <?php else: ?>
                    <?php foreach ($activeJobs as $job): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="/hrm/admin/recruitment/jobs/view.php?id=<?php echo $job['id']; ?>">
                                            <?php echo htmlspecialchars($job['job_title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['department_name'] ?? 'N/A'); ?> |
                                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                                    </small>
                                </div>
                                <span class="badge bg-info"><?php echo $job['application_count']; ?> applicants</span>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Posted: <?php echo formatDate($job['posted_date']); ?> | 
                                    Closes: <?php echo formatDate($job['closing_date']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Application Status Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Application Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Applications & Upcoming Interviews -->
<div class="row">
    <!-- Recent Applications -->
    <div class="col-lg-7 mb-4">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> Recent Applications</h5>
                <a href="/hrm/admin/recruitment/applications/" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Position</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApps as $app): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['applicant_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td><?php echo formatDate($app['application_date']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($app['status']) {
                                            case 'Applied': $statusClass = 'bg-secondary'; break;
                                            case 'Screening': $statusClass = 'bg-info'; break;
                                            case 'Interview': $statusClass = 'bg-warning'; break;
                                            case 'Offered': $statusClass = 'bg-primary'; break;
                                            case 'Hired': $statusClass = 'bg-success'; break;
                                            case 'Rejected': $statusClass = 'bg-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $app['status']; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Interviews -->
    <div class="col-lg-5 mb-4">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Upcoming Interviews</h5>
                <a href="/hrm/admin/recruitment/interviews/" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingInterviews)): ?>
                    <p class="text-muted text-center py-3">No upcoming interviews</p>
                <?php else: ?>
                    <?php foreach ($upcomingInterviews as $interview): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($interview['applicant_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($interview['job_title']); ?></small>
                                </div>
                                <span class="badge bg-info"><?php echo $interview['interview_type']; ?></span>
                            </div>
                            <div class="mt-2">
                                <small>
                                    <i class="fas fa-calendar me-1"></i> <?php echo formatDate($interview['interview_date']); ?>
                                    <i class="fas fa-clock ms-2 me-1"></i> <?php echo date('H:i', strtotime($interview['interview_time'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Status Distribution Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($statusDist, 'status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($statusDist, 'count')); ?>,
                backgroundColor: [
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
