<?php
$page_title = 'Auto-Absent Records';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get date filter
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedMonth = $_GET['month'] ?? date('Y-m');

try {
    // Get auto-absent records for selected date
    $stmt = $pdo->prepare("SELECT a.*, 
                          CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                          e.employee_code,
                          d.department_name
                          FROM attendance a
                          JOIN employees e ON a.employee_id = e.id
                          LEFT JOIN departments d ON e.department_id = d.id
                          WHERE a.attendance_date = ? 
                          AND a.status = 'Alpha'
                          AND a.notes LIKE '%Auto-marked%'
                          ORDER BY e.employee_code");
    $stmt->execute([$selectedDate]);
    $records = $stmt->fetchAll();
    
    // Get monthly summary
    $summaryStmt = $pdo->prepare("SELECT 
                                  COUNT(*) as total_absent,
                                  COUNT(DISTINCT employee_id) as affected_employees
                                  FROM attendance
                                  WHERE DATE_FORMAT(attendance_date, '%Y-%m') = ?
                                  AND status = 'Alpha'
                                  AND notes LIKE '%Auto-marked%'");
    $summaryStmt->execute([$selectedMonth]);
    $summary = $summaryStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching auto-absent records: " . $e->getMessage());
    $records = [];
    $summary = ['total_absent' => 0, 'affected_employees' => 0];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-user-clock me-2"></i> Auto-Absent Records</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/attendance/index.php">Kehadiran</a></li>
                    <li class="breadcrumb-item active">Auto-Absent</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/attendance/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Auto-Absent Bulan Ini</p>
                        <h3 class="mb-0"><?php echo $summary['total_absent']; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> <?php echo date('F Y', strtotime($selectedMonth . '-01')); ?>
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Karyawan Terdampak</p>
                        <h3 class="mb-0"><?php echo $summary['affected_employees']; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-users"></i> Unique employees
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-users"></i>
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
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $selectedDate; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bulan (untuk summary)</label>
                        <input type="month" name="month" class="form-control" value="<?php echo $selectedMonth; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Records Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Auto-Absent Records - <?php echo formatDate($selectedDate, 'd F Y'); ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($records)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 class="text-muted">Tidak ada auto-absent untuk tanggal ini</h4>
                        <p class="text-muted">Semua karyawan sudah check-in atau memiliki izin yang sah</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="autoAbsentTable">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['employee_code']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($record['employee_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['department_name'] ?? '-'); ?></td>
                                        <td><?php echo formatDate($record['attendance_date'], 'd M Y'); ?></td>
                                        <td>
                                            <span class="badge bg-danger">Alpha</span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($record['notes']); ?></small>
                                        </td>
                                        <td>
                                            <button onclick="overrideStatus(<?php echo $record['id']; ?>, '<?php echo $record['employee_name']; ?>')" 
                                                    class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Override Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
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

<!-- Override Modal -->
<div class="modal fade" id="overrideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Override Status Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/attendance/process_override.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="attendance_id" id="override_attendance_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Karyawan</label>
                        <input type="text" class="form-control" id="override_employee_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Baru <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="">Pilih Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Cuti">Cuti</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan Override <span class="text-danger">*</span></label>
                        <textarea name="override_reason" class="form-control" rows="3" required 
                                  placeholder="Jelaskan alasan mengubah status kehadiran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#autoAbsentTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });
});

function overrideStatus(attendanceId, employeeName) {
    document.getElementById('override_attendance_id').value = attendanceId;
    document.getElementById('override_employee_name').value = employeeName;
    
    const modal = new bootstrap.Modal(document.getElementById('overrideModal'));
    modal.show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
