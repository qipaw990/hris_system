<?php
$page_title = 'Interviews';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

try {
    // Get all interviews
    $interviewsStmt = $pdo->query("SELECT i.*, 
        CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
        a.email, a.phone,
        jp.job_title, d.department_name
        FROM interviews i
        LEFT JOIN job_applications ja ON i.application_id = ja.id
        LEFT JOIN applicants a ON ja.applicant_id = a.id
        LEFT JOIN job_postings jp ON ja.job_id = jp.id
        LEFT JOIN departments d ON jp.department_id = d.id
        ORDER BY i.interview_date DESC, i.interview_time DESC");
    $interviews = $interviewsStmt->fetchAll();
    
    // Get pending applications for scheduling
    $pendingApps = $pdo->query("SELECT ja.id, 
        CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
        jp.job_title
        FROM job_applications ja
        LEFT JOIN applicants a ON ja.applicant_id = a.id
        LEFT JOIN job_postings jp ON ja.job_id = jp.id
        WHERE ja.status IN ('Screening', 'Interview')
        ORDER BY ja.application_date DESC")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching interviews: " . $e->getMessage());
    $interviews = [];
    $pendingApps = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-calendar-alt me-2"></i> Interviews</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/recruitment/dashboard.php">Recruitment</a></li>
                    <li class="breadcrumb-item active">Interviews</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                <i class="fas fa-calendar-plus me-2"></i> Schedule Interview
            </button>
        </div>
    </div>
</div>

<!-- Interviews Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> All Interviews</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="interviewsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Position</th>
                                <th>Interview Type</th>
                                <th>Date & Time</th>
                                <th>Location/Link</th>
                                <th>Interviewer</th>
                                <th>Status</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interviews as $interview): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($interview['applicant_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($interview['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($interview['job_title']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $interview['interview_type']; ?></span></td>
                                    <td>
                                        <i class="fas fa-calendar me-1"></i> <?php echo formatDate($interview['interview_date']); ?><br>
                                        <i class="fas fa-clock me-1"></i> <?php echo date('H:i', strtotime($interview['interview_time'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($interview['interview_type'] == 'Video' && !empty($interview['meeting_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($interview['meeting_link']); ?>" target="_blank">
                                                <i class="fas fa-video"></i> Join Meeting
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($interview['location'] ?? 'TBD'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($interview['interviewer_name'] ?? 'TBD'); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($interview['status']) {
                                            case 'Scheduled': $statusClass = 'bg-primary'; break;
                                            case 'Completed': $statusClass = 'bg-success'; break;
                                            case 'Cancelled': $statusClass = 'bg-danger'; break;
                                            case 'Rescheduled': $statusClass = 'bg-warning'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $interview['status']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($interview['rating'] > 0): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $interview['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <small class="text-muted">Not rated</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick='viewInterview(<?php echo json_encode($interview); ?>)' 
                                                class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
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

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/hrm/admin/recruitment/interviews/process_schedule.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i> Schedule Interview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Application <span class="text-danger">*</span></label>
                        <select name="application_id" class="form-select" required>
                            <option value="">Choose applicant...</option>
                            <?php foreach ($pendingApps as $app): ?>
                                <option value="<?php echo $app['id']; ?>">
                                    <?php echo htmlspecialchars($app['applicant_name'] . ' - ' . $app['job_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Type</label>
                            <select name="interview_type" class="form-select" id="interviewType">
                                <option value="In-Person">In-Person</option>
                                <option value="Video">Video Call</option>
                                <option value="Phone">Phone</option>
                                <option value="Technical">Technical</option>
                                <option value="HR">HR Interview</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interviewer Name</label>
                            <input type="text" name="interviewer_name" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Date <span class="text-danger">*</span></label>
                            <input type="date" name="interview_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Time <span class="text-danger">*</span></label>
                            <input type="time" name="interview_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="locationField">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Office - Meeting Room A">
                    </div>
                    
                    <div class="mb-3" id="linkField" style="display: none;">
                        <label class="form-label">Meeting Link</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://zoom.us/j/...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Interview Modal -->
<div class="modal fade" id="viewInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i> Interview Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="interviewDetails">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    initDataTable('#interviewsTable', {
        order: [[3, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search interviews..."
        }
    });
    
    // Toggle location/link field based on interview type
    $('#interviewType').change(function() {
        if ($(this).val() === 'Video') {
            $('#locationField').hide();
            $('#linkField').show();
        } else {
            $('#locationField').show();
            $('#linkField').hide();
        }
    });
});

function viewInterview(interview) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Applicant Information</h6>
                <table class="table table-sm">
                    <tr><td>Name:</td><td><strong>${interview.applicant_name}</strong></td></tr>
                    <tr><td>Email:</td><td>${interview.email}</td></tr>
                    <tr><td>Phone:</td><td>${interview.phone || 'N/A'}</td></tr>
                    <tr><td>Position:</td><td>${interview.job_title}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Interview Details</h6>
                <table class="table table-sm">
                    <tr><td>Type:</td><td><span class="badge bg-info">${interview.interview_type}</span></td></tr>
                    <tr><td>Date:</td><td>${interview.interview_date}</td></tr>
                    <tr><td>Time:</td><td>${interview.interview_time}</td></tr>
                    <tr><td>Interviewer:</td><td>${interview.interviewer_name || 'TBD'}</td></tr>
                    <tr><td>Status:</td><td><span class="badge bg-primary">${interview.status}</span></td></tr>
                </table>
            </div>
        </div>
        ${interview.feedback ? `<hr><h6>Feedback</h6><p>${interview.feedback}</p>` : ''}
        ${interview.notes ? `<hr><h6>Notes</h6><p>${interview.notes}</p>` : ''}
    `;
    $('#interviewDetails').html(html);
    $('#viewInterviewModal').modal('show');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
