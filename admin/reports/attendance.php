<?php
$page_title = 'Laporan Kehadiran';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get filter parameters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$department_id = $_GET['department'] ?? '';

$firstDay = "$year-$month-01";
$lastDay = date('Y-m-t', strtotime($firstDay));

try {
    // Build query with filters
    $where = ["a.attendance_date BETWEEN ? AND ?"];
    $params = [$firstDay, $lastDay];
    
    if ($department_id) {
        $where[] = "e.department_id = ?";
        $params[] = $department_id;
    }
    
    $whereClause = implode(" AND ", $where);
    
    // Get attendance statistics
    $statsStmt = $pdo->prepare("SELECT 
        COUNT(DISTINCT a.employee_id) as total_employees,
        COUNT(*) as total_records,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir_count,
        SUM(CASE WHEN a.status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat_count,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as izin_count,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as sakit_count,
        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as alpha_count,
        SUM(CASE WHEN a.status = 'Cuti' THEN 1 ELSE 0 END) as cuti_count
        FROM attendance a
        LEFT JOIN employees e ON a.employee_id = e.id
        WHERE $whereClause");
    $statsStmt->execute($params);
    $stats = $statsStmt->fetch();
    
    // Get attendance by department
    $deptStmt = $pdo->prepare("SELECT 
        d.department_name,
        COUNT(DISTINCT a.employee_id) as employee_count,
        COUNT(*) as total_attendance,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir_count,
        SUM(CASE WHEN a.status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat_count,
        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as alpha_count
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        LEFT JOIN attendance a ON e.id = a.employee_id AND $whereClause
        GROUP BY d.id
        ORDER BY hadir_count DESC");
    $deptStmt->execute($params);
    $byDepartment = $deptStmt->fetchAll();
    
    // Get daily attendance trend
    $trendStmt = $pdo->prepare("SELECT 
        DATE(attendance_date) as date,
        COUNT(*) as total_records,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir_count,
        SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat_count,
        SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha_count
        FROM attendance a
        LEFT JOIN employees e ON a.employee_id = e.id
        WHERE $whereClause
        GROUP BY DATE(attendance_date)
        ORDER BY attendance_date");
    $trendStmt->execute($params);
    $dailyTrend = $trendStmt->fetchAll();
    
    // Get employee attendance summary
    $empStmt = $pdo->prepare("SELECT 
        e.employee_code,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        d.department_name,
        COUNT(*) as total_attendance,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir_count,
        SUM(CASE WHEN a.status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat_count,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as izin_count,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as sakit_count,
        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as alpha_count,
        SUM(CASE WHEN a.status = 'Cuti' THEN 1 ELSE 0 END) as cuti_count,
        ROUND((SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as attendance_rate
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN attendance a ON e.id = a.employee_id AND $whereClause
        WHERE e.employment_status = 'Active'
        GROUP BY e.id
        HAVING total_attendance > 0
        ORDER BY attendance_rate DESC");
    $empStmt->execute($params);
    $employeeAttendance = $empStmt->fetchAll();
    
    // Get departments for filter
    $deptFilterStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $deptFilterStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching attendance report: " . $e->getMessage());
    $stats = [];
    $byDepartment = [];
    $dailyTrend = [];
    $employeeAttendance = [];
    $departments = [];
}

$totalRecords = $stats['total_records'] ?? 0;
$hadirPercent = $totalRecords > 0 ? round(($stats['hadir_count'] / $totalRecords) * 100, 1) : 0;
$terlambatPercent = $totalRecords > 0 ? round(($stats['terlambat_count'] / $totalRecords) * 100, 1) : 0;
$alphaPercent = $totalRecords > 0 ? round(($stats['alpha_count'] / $totalRecords) * 100, 1) : 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-calendar-check me-2"></i> Laporan Kehadiran</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item">Laporan</li>
                    <li class="breadcrumb-item active">Laporan Kehadiran</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button onclick="exportToExcel()" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-2"></i> Export Excel
            </button>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-2"></i> Cetak
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-select form-select-sm">
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($m == $month) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select form-select-sm">
                            <?php for($y = 2020; $y <= 2030; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Departemen</label>
                        <select name="department" class="form-select form-select-sm">
                            <option value="">Semua Departemen</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo ($department_id == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                            <a href="/hrm/admin/reports/attendance.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
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
                        <p class="text-muted mb-1">Total Kehadiran</p>
                        <h3 class="mb-0"><?php echo $totalRecords; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-users"></i> <?php echo $stats['total_employees'] ?? 0; ?> Karyawan
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-clipboard-check"></i>
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
                        <p class="text-muted mb-1">Hadir</p>
                        <h3 class="mb-0"><?php echo $stats['hadir_count'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> <?php echo $hadirPercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-user-check"></i>
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
                        <p class="text-muted mb-1">Terlambat</p>
                        <h3 class="mb-0"><?php echo $stats['terlambat_count'] ?? 0; ?></h3>
                        <small class="text-warning">
                            <i class="fas fa-clock"></i> <?php echo $terlambatPercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-exclamation-triangle"></i>
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
                        <p class="text-muted mb-1">Alpha</p>
                        <h3 class="mb-0"><?php echo $stats['alpha_count'] ?? 0; ?></h3>
                        <small class="text-danger">
                            <i class="fas fa-times-circle"></i> <?php echo $alphaPercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Daily Trend -->
    <div class="col-lg-8 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i> Tren Kehadiran Harian
                </h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Status Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Distribusi Status
                </h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Department Performance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i> Performa Kehadiran per Departemen
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Karyawan</th>
                                <th>Total Kehadiran</th>
                                <th>Hadir</th>
                                <th>Terlambat</th>
                                <th>Alpha</th>
                                <th>Tingkat Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byDepartment as $dept): ?>
                                <?php 
                                $deptRate = $dept['total_attendance'] > 0 ? round(($dept['hadir_count'] / $dept['total_attendance']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dept['department_name']); ?></strong></td>
                                    <td><?php echo $dept['employee_count']; ?></td>
                                    <td><?php echo $dept['total_attendance']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $dept['hadir_count']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $dept['terlambat_count']; ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo $dept['alpha_count']; ?></span></td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $deptRate; ?>%">
                                                <?php echo $deptRate; ?>%
                                            </div>
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

<!-- Employee Attendance Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Ringkasan Kehadiran Karyawan
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="attendanceTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Total</th>
                                <th>Hadir</th>
                                <th>Terlambat</th>
                                <th>Izin</th>
                                <th>Sakit</th>
                                <th>Alpha</th>
                                <th>Cuti</th>
                                <th>Tingkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employeeAttendance as $emp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($emp['employee_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?></td>
                                    <td><?php echo $emp['total_attendance']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $emp['hadir_count']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $emp['terlambat_count']; ?></span></td>
                                    <td><span class="badge bg-info"><?php echo $emp['izin_count']; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $emp['sakit_count']; ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo $emp['alpha_count']; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $emp['cuti_count']; ?></span></td>
                                    <td>
                                        <?php
                                        $rateClass = '';
                                        if ($emp['attendance_rate'] >= 90) $rateClass = 'bg-success';
                                        elseif ($emp['attendance_rate'] >= 75) $rateClass = 'bg-warning';
                                        else $rateClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $rateClass; ?>">
                                            <?php echo $emp['attendance_rate']; ?>%
                                        </span>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable('#attendanceTable', {
        order: [[10, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari karyawan..."
        }
    });
    
    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(fn($d) => date('d M', strtotime($d['date'])), $dailyTrend)); ?>,
            datasets: [
                {
                    label: 'Hadir',
                    data: <?php echo json_encode(array_column($dailyTrend, 'hadir_count')); ?>,
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Terlambat',
                    data: <?php echo json_encode(array_column($dailyTrend, 'terlambat_count')); ?>,
                    borderColor: 'rgba(255, 193, 7, 1)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Alpha',
                    data: <?php echo json_encode(array_column($dailyTrend, 'alpha_count')); ?>,
                    borderColor: 'rgba(220, 53, 69, 1)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Cuti'],
            datasets: [{
                data: [
                    <?php echo $stats['hadir_count'] ?? 0; ?>,
                    <?php echo $stats['terlambat_count'] ?? 0; ?>,
                    <?php echo $stats['izin_count'] ?? 0; ?>,
                    <?php echo $stats['sakit_count'] ?? 0; ?>,
                    <?php echo $stats['alpha_count'] ?? 0; ?>,
                    <?php echo $stats['cuti_count'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(0, 123, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});

function exportToExcel() {
    window.location.href = '/hrm/admin/reports/export_attendance.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>';
}
</script>
