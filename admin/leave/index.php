<?php
$page_title = 'Manajemen Cuti';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get current year
$currentYear = date('Y');
$selectedYear = $_GET['year'] ?? $currentYear;

try {
    // Get leave statistics
    $statsStmt = $pdo->prepare("SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN status = 'Approved' THEN total_days ELSE 0 END) as total_days_approved
        FROM leave_requests 
        WHERE YEAR(start_date) = ?");
    $statsStmt->execute([$selectedYear]);
    $stats = $statsStmt->fetch();
    
    // Get leave requests with employee info
    $leaveStmt = $pdo->prepare("SELECT lr.*, 
                                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                                e.employee_code,
                                d.department_name,
                                lt.leave_name,
                                lt.is_paid,
                                u.username as approved_by_name
                                FROM leave_requests lr
                                LEFT JOIN employees e ON lr.employee_id = e.id
                                LEFT JOIN departments d ON e.department_id = d.id
                                LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                                LEFT JOIN users u ON lr.approved_by = u.id
                                WHERE YEAR(lr.start_date) = ?
                                ORDER BY lr.created_at DESC");
    $leaveStmt->execute([$selectedYear]);
    $leaveRequests = $leaveStmt->fetchAll();
    
    // Get leave types
    $typesStmt = $pdo->query("SELECT * FROM leave_types ORDER BY leave_name");
    $leaveTypes = $typesStmt->fetchAll();
    
    // Get leave by type statistics
    $byTypeStmt = $pdo->prepare("SELECT 
                                 lt.leave_name,
                                 COUNT(lr.id) as request_count,
                                 SUM(CASE WHEN lr.status = 'Approved' THEN lr.total_days ELSE 0 END) as total_days
                                 FROM leave_types lt
                                 LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id 
                                 AND YEAR(lr.start_date) = ?
                                 GROUP BY lt.id
                                 ORDER BY total_days DESC");
    $byTypeStmt->execute([$selectedYear]);
    $leaveByType = $byTypeStmt->fetchAll();
    
    // Get employees list for add form
    $empStmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, employee_code 
                           FROM employees 
                           WHERE employment_status = 'Active'
                           ORDER BY first_name");
    $employees = $empStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching leave data: " . $e->getMessage());
    $stats = [];
    $leaveRequests = [];
    $leaveTypes = [];
    $leaveByType = [];
    $employees = [];
}

// Calculate percentages
$totalRequests = $stats['total_requests'] ?? 0;
$pendingPercent = $totalRequests > 0 ? round(($stats['pending_count'] / $totalRequests) * 100, 1) : 0;
$approvedPercent = $totalRequests > 0 ? round(($stats['approved_count'] / $totalRequests) * 100, 1) : 0;
$rejectedPercent = $totalRequests > 0 ? round(($stats['rejected_count'] / $totalRequests) * 100, 1) : 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-calendar-times me-2"></i> Manajemen Cuti</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Cuti</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <form method="GET" class="d-inline-flex gap-2">
                <select name="year" class="form-select form-select-sm" style="width: auto;">
                    <?php for($y = 2020; $y <= 2030; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            </form>
            <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                <i class="fas fa-plus me-2"></i> Ajukan Cuti
            </button>
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
                        <p class="text-muted mb-1">Total Pengajuan</p>
                        <h3 class="mb-0"><?php echo $totalRequests; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> Tahun <?php echo $selectedYear; ?>
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
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
                        <p class="text-muted mb-1">Menunggu</p>
                        <h3 class="mb-0"><?php echo $stats['pending_count'] ?? 0; ?></h3>
                        <small class="text-warning">
                            <i class="fas fa-clock"></i> <?php echo $pendingPercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-hourglass-half"></i>
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
                        <p class="text-muted mb-1">Disetujui</p>
                        <h3 class="mb-0"><?php echo $stats['approved_count'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check"></i> <?php echo $approvedPercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
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
                        <p class="text-muted mb-1">Total Hari</p>
                        <h3 class="mb-0"><?php echo $stats['total_days_approved'] ?? 0; ?></h3>
                        <small class="text-info">
                            <i class="fas fa-calendar-day"></i> Hari disetujui
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Insights Row -->
<div class="row mb-4">
    <!-- Leave by Type -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i> Cuti Berdasarkan Jenis
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($leaveByType as $type): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo htmlspecialchars($type['leave_name']); ?></span>
                            <span class="fw-bold"><?php echo $type['request_count']; ?> pengajuan (<?php echo $type['total_days']; ?> hari)</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <?php 
                            $maxDays = $stats['total_days_approved'] ?? 1;
                            $percent = $maxDays > 0 ? ($type['total_days'] / $maxDays) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-info" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Status Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-pie-chart me-2"></i> Distribusi Status
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Menunggu Persetujuan</span>
                        <span class="fw-bold"><?php echo $stats['pending_count'] ?? 0; ?> (<?php echo $pendingPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo $pendingPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Disetujui</span>
                        <span class="fw-bold"><?php echo $stats['approved_count'] ?? 0; ?> (<?php echo $approvedPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $approvedPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Ditolak</span>
                        <span class="fw-bold"><?php echo $stats['rejected_count'] ?? 0; ?> (<?php echo $rejectedPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-danger" style="width: <?php echo $rejectedPercent; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Requests Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Pengajuan Cuti
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="leaveTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Jenis Cuti</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Durasi</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaveRequests as $leave): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($leave['employee_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($leave['employee_code']); ?></small><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($leave['department_name'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($leave['leave_name']); ?>
                                        <?php if ($leave['is_paid']): ?>
                                            <br><small class="badge bg-success">Dibayar</small>
                                        <?php else: ?>
                                            <br><small class="badge bg-secondary">Tidak Dibayar</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($leave['start_date'], 'd M Y'); ?></td>
                                    <td><?php echo formatDate($leave['end_date'], 'd M Y'); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $leave['total_days']; ?> hari</span>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($leave['reason'], 0, 50)) . (strlen($leave['reason']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($leave['status']) {
                                            case 'Pending': $statusClass = 'bg-warning'; break;
                                            case 'Approved': $statusClass = 'bg-success'; break;
                                            case 'Rejected': $statusClass = 'bg-danger'; break;
                                            case 'Cancelled': $statusClass = 'bg-secondary'; break;
                                            default: $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($leave['status']); ?>
                                        </span>
                                        <?php if ($leave['approved_by_name']): ?>
                                            <br><small class="text-muted">oleh <?php echo htmlspecialchars($leave['approved_by_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($leave['status'] == 'Pending'): ?>
                                                <button onclick="approveLeave(<?php echo $leave['id']; ?>)" 
                                                        class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Setujui">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button onclick="rejectLeave(<?php echo $leave['id']; ?>)" 
                                                        class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Tolak">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Ajukan Cuti Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/leave/process_add.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Karyawan <span class="text-danger">*</span></label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">Pilih Karyawan</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['name'] . ' - ' . $emp['employee_code']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                        <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                            <option value="">Pilih Jenis Cuti</option>
                            <?php foreach ($leaveTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>" data-max="<?php echo $type['max_days']; ?>">
                                    <?php echo htmlspecialchars($type['leave_name']); ?> (Max: <?php echo $type['max_days']; ?> hari)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durasi</label>
                        <input type="text" class="form-control" id="duration_display" readonly>
                        <input type="hidden" id="total_days" name="total_days">
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i> Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable('#leaveTable', {
        order: [[2, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari pengajuan cuti..."
        }
    });
    
    // Calculate duration
    $('#start_date, #end_date').on('change', function() {
        var start = new Date($('#start_date').val());
        var end = new Date($('#end_date').val());
        
        if (start && end && end >= start) {
            var diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
            $('#duration_display').val(diff + ' hari');
            $('#total_days').val(diff);
        } else {
            $('#duration_display').val('');
            $('#total_days').val('');
        }
    });
});

function approveLeave(id) {
    Swal.fire({
        title: 'Setujui Cuti?',
        text: 'Pengajuan cuti akan disetujui',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/hrm/admin/leave/approve.php?id=' + id;
        }
    });
}

function rejectLeave(id) {
    Swal.fire({
        title: 'Tolak Cuti?',
        input: 'textarea',
        inputLabel: 'Alasan Penolakan',
        inputPlaceholder: 'Masukkan alasan penolakan...',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan penolakan harus diisi!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/hrm/admin/leave/reject.php?id=' + id + '&reason=' + encodeURIComponent(result.value);
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
