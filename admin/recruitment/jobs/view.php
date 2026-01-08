<?php
$page_title = 'Job Details';
include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/sidebar.php';

$id = $_GET['id'] ?? 0;

try {
    // Get job details
    $stmt = $pdo->prepare("SELECT jp.*, d.department_name, p.position_name,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jp.id) as total_applications,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jp.id AND status = 'Hired') as hired_count
        FROM job_postings jp
        LEFT JOIN departments d ON jp.department_id = d.id
        LEFT JOIN positions p ON jp.position_id = p.id
        WHERE jp.id = ?");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    
    if (!$job) {
        redirect('/hrm/admin/recruitment/jobs/', 'error', 'Job not found');
    }
    
    // Get applications for this job
    $appsStmt = $pdo->prepare("SELECT ja.*, 
        CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
        a.email, a.phone, a.years_of_experience
        FROM job_applications ja
        LEFT JOIN applicants a ON ja.applicant_id = a.id
        WHERE ja.job_id = ?
        ORDER BY ja.application_date DESC");
    $appsStmt->execute([$id]);
    $applications = $appsStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching job: " . $e->getMessage());
    redirect('/hrm/admin/recruitment/jobs/', 'error', 'Error loading job');
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="fas fa-briefcase me-2"></i> <?php echo htmlspecialchars($job['job_title']); ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/recruitment/dashboard.php">Recruitment</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/recruitment/jobs/">Jobs</a></li>
                    <li class="breadcrumb-item active">Job Details</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-md-end">
            <?php
            $statusClass = '';
            switch ($job['status']) {
                case 'Open': $statusClass = 'bg-success'; break;
                case 'Closed': $statusClass = 'bg-secondary'; break;
                case 'On Hold': $statusClass = 'bg-warning'; break;
            }
            ?>
            <span class="badge <?php echo $statusClass; ?> fs-6"><?php echo $job['status']; ?></span>
        </div>
    </div>
</div>

<!-- Job Details -->
<div class="row">
    <!-- Job Information -->
    <div class="col-lg-8 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Job Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Department</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($job['department_name'] ?? 'N/A'); ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Position</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($job['position_name'] ?? 'N/A'); ?></strong></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted">Employment Type</small>
                        <p class="mb-0"><span class="badge bg-info"><?php echo $job['employment_type']; ?></span></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Location</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($job['location']); ?></strong></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Vacancies</small>
                        <p class="mb-0"><strong><?php echo $job['vacancies']; ?> position(s)</strong></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Salary Range</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($job['salary_range'] ?? 'Negotiable'); ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Posted Date</small>
                        <p class="mb-0"><strong><?php echo formatDate($job['posted_date']); ?></strong></p>
                    </div>
                </div>
                
                <hr>
                
                <h6>Job Description</h6>
                <p><?php echo nl2br(htmlspecialchars($job['job_description'] ?? 'No description provided')); ?></p>
                
                <?php if (!empty($job['requirements'])): ?>
                <hr>
                <h6>Requirements</h6>
                <p><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($job['responsibilities'])): ?>
                <hr>
                <h6>Responsibilities</h6>
                <p><?php echo nl2br(htmlspecialchars($job['responsibilities'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Applications</small>
                    <h3 class="mb-0"><?php echo $job['total_applications']; ?></h3>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Hired</small>
                    <h3 class="mb-0 text-success"><?php echo $job['hired_count']; ?></h3>
                </div>
                <hr>
                <div>
                    <small class="text-muted">Remaining Vacancies</small>
                    <h3 class="mb-0 text-primary"><?php echo max(0, $job['vacancies'] - $job['hired_count']); ?></h3>
                </div>
            </div>
        </div>
        
        <div class="card fade-in">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar me-2"></i> Timeline</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">Posted</small>
                <p class="mb-2"><strong><?php echo formatDate($job['posted_date']); ?></strong></p>
                
                <small class="text-muted">Closing Date</small>
                <p class="mb-0"><strong><?php echo formatDate($job['closing_date'] ?? ''); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Applications -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Applications (<?php echo count($applications); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($applications)): ?>
                    <p class="text-muted text-center py-3">No applications yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Contact</th>
                                    <th>Experience</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($app['applicant_name']); ?></strong></td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars($app['email']); ?><br>
                                                <?php echo htmlspecialchars($app['phone'] ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td><?php echo $app['years_of_experience']; ?> years</td>
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
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $app['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
