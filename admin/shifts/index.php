<?php
$page_title = 'Work Shifts Management';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get filter
$status = $_GET['status'] ?? 'active';

try {
    // Build query
    $sql = "SELECT ws.*,
            COUNT(DISTINCT es.employee_id) as employee_count
            FROM work_shifts ws
            LEFT JOIN employee_shifts es ON ws.id = es.shift_id 
                AND (es.end_date IS NULL OR es.end_date >= CURDATE())
            WHERE 1=1";
    
    $params = [];
    
    if ($status === 'active') {
        $sql .= " AND ws.is_active = 1";
    } elseif ($status === 'inactive') {
        $sql .= " AND ws.is_active = 0";
    }
    
    $sql .= " GROUP BY ws.id ORDER BY ws.shift_code";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $shifts = $stmt->fetchAll();
    
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
                              COUNT(*) as total,
                              SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                              SUM(CASE WHEN is_night_shift = 1 THEN 1 ELSE 0 END) as night_shifts
                              FROM work_shifts");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching shifts: " . $e->getMessage());
    $shifts = [];
    $stats = ['total' => 0, 'active' => 0, 'night_shifts' => 0];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-clock me-2"></i> Shift Kerja</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Shift Kerja</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/shifts/shuffle.php" class="btn btn-warning me-2">
                <i class="fas fa-random me-2"></i> Shuffle Shift
            </a>
            <a href="/hrm/admin/shifts/create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Tambah Shift
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Shift</p>
                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Active Shifts</p>
                        <h3 class="mb-0"><?php echo $stats['active']; ?></h3>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Night Shifts</p>
                        <h3 class="mb-0"><?php echo $stats['night_shifts']; ?></h3>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-moon"></i>
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
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Shifts Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Daftar Shift</h5>
            </div>
            <div class="card-body">
                <?php if (empty($shifts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Belum ada shift</h4>
                        <p class="text-muted">Klik tombol "Tambah Shift" untuk membuat shift baru</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="shiftsTable">
                            <thead>
                                <tr>
                                    <th>Shift</th>
                                    <th>Jam Kerja</th>
                                    <th>Grace Period</th>
                                    <th>Tunjangan</th>
                                    <th>Karyawan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shifts as $shift): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($shift['shift_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($shift['shift_code']); ?></small>
                                            <?php if ($shift['is_night_shift']): ?>
                                                <span class="badge bg-dark ms-2"><i class="fas fa-moon"></i> Night</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-clock text-primary"></i>
                                            <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                                            <?php echo date('H:i', strtotime($shift['end_time'])); ?>
                                        </td>
                                        <td><?php echo $shift['grace_period_minutes']; ?> menit</td>
                                        <td>Rp <?php echo number_format($shift['shift_allowance'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $shift['employee_count']; ?> karyawan</span>
                                        </td>
                                        <td>
                                            <?php if ($shift['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/hrm/admin/shifts/manage.php?shift_id=<?php echo $shift['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Manage Employees">
                                                <i class="fas fa-users-cog"></i>
                                            </a>
                                            <a href="/hrm/admin/shifts/edit.php?id=<?php echo $shift['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($shift['employee_count'] == 0): ?>
                                                <a href="/hrm/admin/shifts/delete.php?id=<?php echo $shift['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Hapus shift ini?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Initialize DataTable
if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
    jQuery(document).ready(function($) {
        $('#shiftsTable').DataTable({
            order: [[0, 'asc']],
            pageLength: 25
        });
    });
}
</script>
