<?php
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT jp.*, d.department_name, p.position_name
        FROM job_postings jp
        LEFT JOIN departments d ON jp.department_id = d.id
        LEFT JOIN positions p ON jp.position_id = p.id
        WHERE jp.id = ? AND jp.status = 'Open'");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    
    if (!$job) {
        echo '<p class="text-danger">Job not found</p>';
        exit;
    }
    ?>
    
    <div class="row">
        <div class="col-md-8">
            <h3 class="mb-3"><?php echo htmlspecialchars($job['job_title']); ?></h3>
            
            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary"><?php echo $job['employment_type']; ?></span>
                <span class="badge bg-success">
                    <i class="fas fa-users me-1"></i> <?php echo $job['vacancies']; ?> position(s)
                </span>
            </div>
            
            <div class="mb-4">
                <h5><i class="fas fa-align-left me-2"></i> Job Description</h5>
                <p><?php echo nl2br(htmlspecialchars($job['job_description'] ?? 'No description provided')); ?></p>
            </div>
            
            <?php if (!empty($job['requirements'])): ?>
            <div class="mb-4">
                <h5><i class="fas fa-check-circle me-2"></i> Requirements</h5>
                <p><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($job['responsibilities'])): ?>
            <div class="mb-4">
                <h5><i class="fas fa-tasks me-2"></i> Responsibilities</h5>
                <p><?php echo nl2br(htmlspecialchars($job['responsibilities'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Job Information</h5>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Department</small>
                        <strong><?php echo htmlspecialchars($job['department_name'] ?? 'N/A'); ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Location</small>
                        <strong><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['location']); ?></strong>
                    </div>
                    
                    <?php if (!empty($job['salary_range'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Salary Range</small>
                        <strong><i class="fas fa-money-bill-wave me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?></strong>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Posted Date</small>
                        <strong><?php echo date('d M Y', strtotime($job['posted_date'])); ?></strong>
                    </div>
                    
                    <div class="mb-4">
                        <small class="text-muted d-block">Closing Date</small>
                        <strong class="text-danger"><?php echo date('d M Y', strtotime($job['closing_date'])); ?></strong>
                    </div>
                    
                    <button onclick="applyNow(<?php echo $job['id']; ?>)" class="btn btn-apply w-100">
                        <i class="fas fa-paper-plane me-2"></i> Apply Now
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php
} catch (PDOException $e) {
    error_log("Error fetching job details: " . $e->getMessage());
    echo '<p class="text-danger">Error loading job details</p>';
}
?>
