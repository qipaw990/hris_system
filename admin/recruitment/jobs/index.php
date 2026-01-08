<?php
$page_title = 'Job Postings';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

try {
    // Get all job postings
    $jobsStmt = $pdo->query("SELECT jp.*, d.department_name, p.position_name,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jp.id) as application_count
        FROM job_postings jp
        LEFT JOIN departments d ON jp.department_id = d.id
        LEFT JOIN positions p ON jp.position_id = p.id
        ORDER BY jp.posted_date DESC");
    $jobs = $jobsStmt->fetchAll();
    
    // Get departments and positions for form
    $depts = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
    $positions = $pdo->query("SELECT * FROM positions ORDER BY position_name")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching jobs: " . $e->getMessage());
    $jobs = [];
    $depts = [];
    $positions = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-briefcase me-2"></i> Job Postings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/recruitment/dashboard.php">Recruitment</a></li>
                    <li class="breadcrumb-item active">Job Postings</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addJobModal">
                <i class="fas fa-plus me-2"></i> Post New Job
            </button>
        </div>
    </div>
</div>

<!-- Jobs Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> All Job Postings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="jobsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Department</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Vacancies</th>
                                <th>Applications</th>
                                <th>Posted Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($job['job_title']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($job['position_name'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($job['location']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $job['employment_type']; ?></span></td>
                                    <td><?php echo $job['vacancies']; ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $job['application_count']; ?></span>
                                    </td>
                                    <td><?php echo formatDate($job['posted_date']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($job['status']) {
                                            case 'Open': $statusClass = 'bg-success'; break;
                                            case 'Closed': $statusClass = 'bg-secondary'; break;
                                            case 'On Hold': $statusClass = 'bg-warning'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $job['status']; ?></span>
                                    </td>
                                    <td>
                                        <a href="/hrm/admin/recruitment/jobs/view.php?id=<?php echo $job['id']; ?>" 
                                           class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick='editJob(<?php echo json_encode($job); ?>)' 
                                                class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Job Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="/hrm/admin/recruitment/jobs/process_add.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Post New Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" name="job_title" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Vacancies</label>
                            <input type="number" name="vacancies" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($depts as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Position</label>
                            <select name="position_id" class="form-select">
                                <option value="">Select Position</option>
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?php echo $pos['id']; ?>"><?php echo htmlspecialchars($pos['position_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" class="form-select">
                                <option value="Full-Time">Full-Time</option>
                                <option value="Part-Time">Part-Time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="Jakarta">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Range</label>
                            <input type="text" name="salary_range" class="form-control" placeholder="e.g. Rp 10,000,000 - Rp 15,000,000">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Posted Date</label>
                            <input type="date" name="posted_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Closing Date</label>
                            <input type="date" name="closing_date" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Description</label>
                        <textarea name="job_description" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Requirements</label>
                        <textarea name="requirements" class="form-control" rows="4" placeholder="List requirements (one per line)"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Responsibilities</label>
                        <textarea name="responsibilities" class="form-control" rows="4" placeholder="List responsibilities (one per line)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="editJobModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="/hrm/admin/recruitment/jobs/process_edit.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Job Posting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" id="edit_job_title" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="edit_department_id" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($depts as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="edit_location" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Closing Date</label>
                            <input type="date" name="closing_date" id="edit_closing_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    initDataTable('#jobsTable', {
        order: [[6, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search jobs..."
        }
    });
});

function editJob(job) {
    $('#edit_id').val(job.id);
    $('#edit_job_title').val(job.job_title);
    $('#edit_department_id').val(job.department_id);
    $('#edit_location').val(job.location);
    $('#edit_status').val(job.status);
    $('#edit_closing_date').val(job.closing_date);
    $('#editJobModal').modal('show');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
