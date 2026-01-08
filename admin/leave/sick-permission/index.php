<?php
$page_title = 'Sick & Permission Requests';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

// Get filter parameters
$requestType = $_GET['request_type'] ?? 'All';
$status = $_GET['status'] ?? 'Pending';

try {
    // Build query
    $sql = "SELECT spr.*,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            e.employee_code,
            d.department_name
            FROM sick_permission_requests spr
            JOIN employees e ON spr.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE 1=1";
    
    $params = [];
    
    if ($requestType !== 'All') {
        $sql .= " AND spr.request_type = ?";
        $params[] = $requestType;
    }
    
    if ($status !== 'All') {
        $sql .= " AND spr.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY spr.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
                              COUNT(*) as total,
                              SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                              SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                              SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                              SUM(CASE WHEN request_type = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                              SUM(CASE WHEN request_type = 'Izin' THEN 1 ELSE 0 END) as izin
                              FROM sick_permission_requests");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching sick/permission requests: " . $e->getMessage());
    $requests = [];
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'sakit' => 0, 'izin' => 0];
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-notes-medical me-2"></i> Sakit & Izin</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Sakit & Izin</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total</p>
                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Pending</p>
                        <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Approved</p>
                        <h3 class="mb-0"><?php echo $stats['approved']; ?></h3>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Rejected</p>
                        <h3 class="mb-0"><?php echo $stats['rejected']; ?></h3>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Sakit</p>
                        <h3 class="mb-0"><?php echo $stats['sakit']; ?></h3>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-thermometer"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Izin</p>
                        <h3 class="mb-0"><?php echo $stats['izin']; ?></h3>
                    </div>
                    <div class="stats-icon bg-secondary">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipe</label>
                        <select name="request_type" class="form-select">
                            <option value="All" <?php echo $requestType === 'All' ? 'selected' : ''; ?>>Semua Tipe</option>
                            <option value="Sakit" <?php echo $requestType === 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                            <option value="Izin" <?php echo $requestType === 'Izin' ? 'selected' : ''; ?>>Izin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="All" <?php echo $status === 'All' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Requests Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Daftar Request</h5>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada request</h4>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="requestsTable">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Tipe</th>
                                    <th>Tanggal</th>
                                    <th>Durasi</th>
                                    <th>Alasan</th>
                                    <th>Lampiran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['employee_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($request['employee_code']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $request['request_type'] === 'Sakit' ? 'bg-info' : 'bg-secondary'; ?>">
                                                <?php echo $request['request_type']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($request['start_date'])); ?>
                                            <?php if ($request['start_date'] !== $request['end_date']): ?>
                                                <br><small class="text-muted">s/d <?php echo date('d M Y', strtotime($request['end_date'])); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $request['total_days']; ?> hari</td>
                                        <td><small><?php echo htmlspecialchars(substr($request['reason'], 0, 50)) . (strlen($request['reason']) > 50 ? '...' : ''); ?></small></td>
                                        <td>
                                            <?php if ($request['attachment']): ?>
                                                <a href="/hrm/assets/uploads/<?php echo $request['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-paperclip"></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = '';
                                            switch ($request['status']) {
                                                case 'Pending': $badgeClass = 'bg-warning'; break;
                                                case 'Approved': $badgeClass = 'bg-success'; break;
                                                case 'Rejected': $badgeClass = 'bg-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $request['status']; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] === 'Pending'): ?>
                                                <a href="/hrm/admin/leave/sick-permission/process_approve.php?id=<?php echo $request['id']; ?>" 
                                                   class="btn btn-sm btn-success" 
                                                   onclick="return confirm('Approve request ini?')">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                                <button onclick="rejectRequest(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['employee_name']); ?>')" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i> Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/leave/sick-permission/process_reject.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="request_id" id="reject_request_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Reject request dari: <strong id="reject_employee_name"></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required 
                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i> Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
function rejectRequest(requestId, employeeName) {
    document.getElementById('reject_request_id').value = requestId;
    document.getElementById('reject_employee_name').textContent = employeeName;
    
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

// Initialize DataTable if jQuery is available
if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
    jQuery(document).ready(function($) {
        $('#requestsTable').DataTable({
            order: [[2, 'desc']],
            pageLength: 25
        });
    });
}
</script>
