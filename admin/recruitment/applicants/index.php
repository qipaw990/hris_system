<?php
$page_title = 'Applicants';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

try {
    // Get all applications with applicant and job details
    $appsStmt = $pdo->query("SELECT ja.*, 
        CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
        a.email, a.phone, a.years_of_experience, a.expected_salary,
        jp.job_title, d.department_name,
        (SELECT COUNT(*) FROM applicant_documents WHERE applicant_id = a.id) as document_count
        FROM job_applications ja
        LEFT JOIN applicants a ON ja.applicant_id = a.id
        LEFT JOIN job_postings jp ON ja.job_id = jp.id
        LEFT JOIN departments d ON jp.department_id = d.id
        ORDER BY ja.application_date DESC");
    $applications = $appsStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching applicants: " . $e->getMessage());
    $applications = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-users me-2"></i> Applicants</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/recruitment/dashboard.php">Recruitment</a></li>
                    <li class="breadcrumb-item active">Applicants</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Applicants Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> All Applications</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="applicantsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Position Applied</th>
                                <th>Department</th>
                                <th>Experience</th>
                                <th>Expected Salary</th>
                                <th>Documents</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['applicant_name']); ?></strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($app['email']); ?><br>
                                            <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($app['phone'] ?? 'N/A'); ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($app['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo $app['years_of_experience']; ?> years</td>
                                    <td><?php echo formatCurrency($app['expected_salary'] ?? 0); ?></td>
                                    <td>
                                        <button onclick="viewDocuments(<?php echo $app['applicant_id']; ?>)" 
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-alt me-1"></i> <?php echo $app['document_count']; ?> docs
                                        </button>
                                    </td>
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
                                        <select class="form-select form-select-sm <?php echo $statusClass; ?>" 
                                                onchange="updateStatus(<?php echo $app['id']; ?>, this.value)">
                                            <option value="Applied" <?php echo $app['status'] == 'Applied' ? 'selected' : ''; ?>>Applied</option>
                                            <option value="Screening" <?php echo $app['status'] == 'Screening' ? 'selected' : ''; ?>>Screening</option>
                                            <option value="Interview" <?php echo $app['status'] == 'Interview' ? 'selected' : ''; ?>>Interview</option>
                                            <option value="Offered" <?php echo $app['status'] == 'Offered' ? 'selected' : ''; ?>>Offered</option>
                                            <option value="Hired" <?php echo $app['status'] == 'Hired' ? 'selected' : ''; ?>>Hired</option>
                                            <option value="Rejected" <?php echo $app['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $app['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td>
                                        <button onclick='viewApplicant(<?php echo json_encode($app); ?>)' 
                                                class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="uploadDocument(<?php echo $app['applicant_id']; ?>)" 
                                                class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Upload Document">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                        <button onclick="scheduleInterview(<?php echo $app['id']; ?>)" 
                                                class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Schedule Interview">
                                            <i class="fas fa-calendar-plus"></i>
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

<!-- View Applicant Modal -->
<div class="modal fade" id="viewApplicantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i> Applicant Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="applicantDetails">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>
</div>

<!-- View Documents Modal -->
<div class="modal fade" id="viewDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i> Applicant Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="documentsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/hrm/admin/recruitment/applicants/upload_document.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="applicant_id" id="upload_applicant_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i> Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Select type...</option>
                            <option value="Resume">Resume/CV</option>
                            <option value="Cover Letter">Cover Letter</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Portfolio">Portfolio</option>
                            <option value="ID Card">ID Card</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input type="text" name="document_name" class="form-control" placeholder="e.g. John Doe Resume">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="document_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG. Max 5MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    initDataTable('#applicantsTable', {
        order: [[5, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search applicants..."
        }
    });
});

function updateStatus(appId, newStatus) {
    if (confirm('Update application status to ' + newStatus + '?')) {
        $.post('/hrm/admin/recruitment/applicants/update_status.php', {
            id: appId,
            status: newStatus,
            csrf_token: '<?php echo generateCSRFToken(); ?>'
        }, function(response) {
            location.reload();
        });
    }
}

function viewApplicant(app) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Personal Information</h6>
                <table class="table table-sm">
                    <tr><td>Name:</td><td><strong>${app.applicant_name}</strong></td></tr>
                    <tr><td>Email:</td><td>${app.email}</td></tr>
                    <tr><td>Phone:</td><td>${app.phone || 'N/A'}</td></tr>
                    <tr><td>Experience:</td><td>${app.years_of_experience} years</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Application Info</h6>
                <table class="table table-sm">
                    <tr><td>Position:</td><td><strong>${app.job_title}</strong></td></tr>
                    <tr><td>Department:</td><td>${app.department_name || 'N/A'}</td></tr>
                    <tr><td>Applied:</td><td>${app.application_date}</td></tr>
                    <tr><td>Status:</td><td><span class="badge bg-info">${app.status}</span></td></tr>
                </table>
            </div>
        </div>
        ${app.notes ? `<hr><h6>Notes</h6><p>${app.notes}</p>` : ''}
    `;
    $('#applicantDetails').html(html);
    $('#viewApplicantModal').modal('show');
}

function scheduleInterview(appId) {
    window.location.href = '/hrm/admin/recruitment/interviews/?app_id=' + appId;
}

function viewDocuments(applicantId) {
    $.get('/hrm/admin/recruitment/applicants/get_documents.php?applicant_id=' + applicantId, function(response) {
        $('#documentsContent').html(response);
        $('#viewDocumentsModal').modal('show');
    });
}

function uploadDocument(applicantId) {
    $('#upload_applicant_id').val(applicantId);
    $('#uploadDocumentModal').modal('show');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
