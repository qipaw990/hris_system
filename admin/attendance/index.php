<?php
$page_title = 'Kehadiran Karyawan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');
$selectedMonth = $_GET['month'] ?? $currentMonth;
$selectedYear = $_GET['year'] ?? $currentYear;
$selectedShift = $_GET['shift'] ?? 'all';

// Calculate date range
$firstDay = "$selectedYear-$selectedMonth-01";
$lastDay = date('Y-m-t', strtotime($firstDay));
$today = date('Y-m-d');

try {
    // Get attendance statistics for selected month
    $statsQuery = "SELECT 
        COUNT(DISTINCT employee_id) as total_employees,
        COUNT(*) as total_records,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir_count,
        SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat_count,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin_count,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit_count,
        SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha_count,
        SUM(CASE WHEN status = 'Cuti' THEN 1 ELSE 0 END) as cuti_count
        FROM attendance 
        WHERE attendance_date BETWEEN ? AND ?";
    
    $statsParams = [$firstDay, $lastDay];
    
    if ($selectedShift !== 'all') {
        $statsQuery .= " AND shift_id = ?";
        $statsParams[] = $selectedShift;
    }
    
    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute($statsParams);
    $stats = $statsStmt->fetch();
    
    // Get today's attendance with shift info
    $todayQuery = "SELECT a.*, 
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                e.employee_code,
                d.department_name,
                ws.shift_name,
                ws.start_time,
                ws.end_time,
                ws.grace_period_minutes
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN work_shifts ws ON a.shift_id = ws.id
                WHERE a.attendance_date = ?";
    
    $todayParams = [$today];
    
    if ($selectedShift !== 'all') {
        $todayQuery .= " AND a.shift_id = ?";
        $todayParams[] = $selectedShift;
    }
    
    $todayQuery .= " ORDER BY a.check_in ASC";
    
    $todayStmt = $pdo->prepare($todayQuery);
    $todayStmt->execute($todayParams);
    $todayAttendance = $todayStmt->fetchAll();
    
    // Get attendance records for selected month with shift info
    $attendanceQuery = "SELECT a.*, 
                     CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                     e.employee_code,
                     d.department_name,
                     ws.shift_name,
                     ws.start_time,
                     ws.end_time,
                     ws.grace_period_minutes
                     FROM attendance a
                     LEFT JOIN employees e ON a.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN work_shifts ws ON a.shift_id = ws.id
                     WHERE a.attendance_date BETWEEN ? AND ?";
    
    $attendanceParams = [$firstDay, $lastDay];
    
    if ($selectedShift !== 'all') {
        $attendanceQuery .= " AND a.shift_id = ?";
        $attendanceParams[] = $selectedShift;
    }
    
    $attendanceQuery .= " ORDER BY a.attendance_date DESC, a.check_in ASC";
    
    $attendanceStmt = $pdo->prepare($attendanceQuery);
    $attendanceStmt->execute($attendanceParams);
    $attendanceRecords = $attendanceStmt->fetchAll();
    
    // Get top performers (most present)
    $topPerformersStmt = $pdo->prepare("SELECT 
                                        e.id,
                                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                                        e.employee_code,
                                        COUNT(*) as attendance_count,
                                        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir_count
                                        FROM employees e
                                        LEFT JOIN attendance a ON e.id = a.employee_id 
                                        AND a.attendance_date BETWEEN ? AND ?
                                        WHERE e.employment_status = 'Active'
                                        GROUP BY e.id
                                        ORDER BY hadir_count DESC
                                        LIMIT 5");
    $topPerformersStmt->execute([$firstDay, $lastDay]);
    $topPerformers = $topPerformersStmt->fetchAll();
    
    // Get all shifts for filter
    $shiftsStmt = $pdo->query("SELECT id, shift_name FROM work_shifts WHERE is_active = 1 ORDER BY shift_code");
    $shifts = $shiftsStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching attendance: " . $e->getMessage());
    $stats = [];
    $todayAttendance = [];
    $attendanceRecords = [];
    $topPerformers = [];
    $shifts = [];
}

// Calculate percentages
$totalRecords = $stats['total_records'] ?? 0;
$hadirPercent = $totalRecords > 0 ? round(($stats['hadir_count'] / $totalRecords) * 100, 1) : 0;
$terlambatPercent = $totalRecords > 0 ? round(($stats['terlambat_count'] / $totalRecords) * 100, 1) : 0;
$izinPercent = $totalRecords > 0 ? round(($stats['izin_count'] / $totalRecords) * 100, 1) : 0;
$sakitPercent = $totalRecords > 0 ? round(($stats['sakit_count'] / $totalRecords) * 100, 1) : 0;
$alphaPercent = $totalRecords > 0 ? round(($stats['alpha_count'] / $totalRecords) * 100, 1) : 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-calendar-check me-2"></i> Kehadiran Karyawan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Kehadiran</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <form method="GET" class="d-inline-flex gap-2">
                <select name="shift" class="form-select form-select-sm" style="width: auto;">
                    <option value="all" <?php echo ($selectedShift === 'all') ? 'selected' : ''; ?>>Semua Shift</option>
                    <?php foreach ($shifts as $shift): ?>
                        <option value="<?php echo $shift['id']; ?>" <?php echo ($selectedShift == $shift['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($shift['shift_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="month" class="form-select form-select-sm" style="width: auto;">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($m == $selectedMonth) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-select form-select-sm" style="width: auto;">
                    <?php for($y = 2020; $y <= 2030; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            </form>
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
                        <h3 class="mb-0"><?php echo number_format($totalRecords); ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> <?php echo $stats['total_employees'] ?? 0; ?> Karyawan
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

<!-- Insights Row -->
<div class="row mb-4">
    <!-- Status Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Distribusi Status
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Hadir</span>
                        <span class="fw-bold"><?php echo $stats['hadir_count'] ?? 0; ?> (<?php echo $hadirPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $hadirPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Terlambat</span>
                        <span class="fw-bold"><?php echo $stats['terlambat_count'] ?? 0; ?> (<?php echo $terlambatPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo $terlambatPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Izin</span>
                        <span class="fw-bold"><?php echo $stats['izin_count'] ?? 0; ?> (<?php echo $izinPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" style="width: <?php echo $izinPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Sakit</span>
                        <span class="fw-bold"><?php echo $stats['sakit_count'] ?? 0; ?> (<?php echo $sakitPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-secondary" style="width: <?php echo $sakitPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Alpha</span>
                        <span class="fw-bold"><?php echo $stats['alpha_count'] ?? 0; ?> (<?php echo $alphaPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-danger" style="width: <?php echo $alphaPercent; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Performers -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i> Karyawan Terbaik
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Karyawan</th>
                                <th>Hadir</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            foreach ($topPerformers as $performer): 
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($rank == 1): ?>
                                            <i class="fas fa-trophy text-warning"></i>
                                        <?php elseif ($rank == 2): ?>
                                            <i class="fas fa-medal text-secondary"></i>
                                        <?php elseif ($rank == 3): ?>
                                            <i class="fas fa-award text-danger"></i>
                                        <?php else: ?>
                                            <?php echo $rank; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($performer['employee_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($performer['employee_code']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $performer['hadir_count']; ?></span>
                                    </td>
                                    <td><?php echo $performer['attendance_count']; ?></td>
                                </tr>
                            <?php 
                            $rank++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Attendance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day me-2"></i> Kehadiran Hari Ini - <?php echo formatDate($today, 'd F Y'); ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($todayAttendance) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Shift</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayAttendance as $att): 
                                    // Calculate duration
                                    $duration = '-';
                                    if ($att['check_in'] && $att['check_out']) {
                                        $checkIn = new DateTime($att['check_in']);
                                        $checkOut = new DateTime($att['check_out']);
                                        $diff = $checkIn->diff($checkOut);
                                        $duration = $diff->h . 'j ' . $diff->i . 'm';
                                    }
                                    
                                    // Check if late
                                    $isLate = false;
                                    $lateMinutes = 0;
                                    if ($att['check_in'] && $att['start_time']) {
                                        $checkInTime = new DateTime($att['check_in']);
                                        $shiftStart = new DateTime($att['attendance_date'] . ' ' . $att['start_time']);
                                        $gracePeriod = $att['grace_period_minutes'] ?? 0;
                                        $shiftStart->modify("+{$gracePeriod} minutes");
                                        
                                        if ($checkInTime > $shiftStart) {
                                            $isLate = true;
                                            $lateDiff = $shiftStart->diff($checkInTime);
                                            $lateMinutes = ($lateDiff->h * 60) + $lateDiff->i;
                                        }
                                    }
                                ?>
                                    <tr <?php echo $isLate ? 'class="table-warning"' : ''; ?>>
                                        <td>
                                            <strong><?php echo htmlspecialchars($att['employee_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($att['employee_code']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($att['department_name'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($att['shift_name']): ?>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($att['shift_name']); ?></span><br>
                                                <small class="text-muted">
                                                    <?php echo date('H:i', strtotime($att['start_time'])); ?> - 
                                                    <?php echo date('H:i', strtotime($att['end_time'])); ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">No Shift</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($att['check_in']): ?>
                                                <i class="fas fa-sign-in-alt text-success"></i>
                                                <?php echo date('H:i', strtotime($att['check_in'])); ?>
                                                <?php if ($isLate): ?>
                                                    <br><small class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> +<?php echo $lateMinutes; ?> menit
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($att['check_out']): ?>
                                                <i class="fas fa-sign-out-alt text-danger"></i>
                                                <?php echo date('H:i', strtotime($att['check_out'])); ?>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Belum Check Out</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($att['status']) {
                                                case 'Hadir': $statusClass = 'bg-success'; break;
                                                case 'Terlambat': $statusClass = 'bg-warning'; break;
                                                case 'Izin': $statusClass = 'bg-info'; break;
                                                case 'Sakit': $statusClass = 'bg-secondary'; break;
                                                case 'Alpha': $statusClass = 'bg-danger'; break;
                                                case 'Cuti': $statusClass = 'bg-primary'; break;
                                                default: $statusClass = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($att['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $duration; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada data kehadiran untuk hari ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- All Attendance Records -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Riwayat Kehadiran - <?php echo date('F Y', strtotime($firstDay)); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="attendanceTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Shift</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRecords as $record): ?>
                                <tr>
                                    <td><?php echo formatDate($record['attendance_date'], 'd M Y'); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($record['employee_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($record['employee_code']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['department_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($record['shift_name']): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($record['shift_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $record['check_in'] ? date('H:i', strtotime($record['check_in'])) : '-'; ?></td>
                                    <td><?php echo $record['check_out'] ? date('H:i', strtotime($record['check_out'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($record['status']) {
                                            case 'Hadir': $statusClass = 'bg-success'; break;
                                            case 'Terlambat': $statusClass = 'bg-warning'; break;
                                            case 'Izin': $statusClass = 'bg-info'; break;
                                            case 'Sakit': $statusClass = 'bg-secondary'; break;
                                            case 'Alpha': $statusClass = 'bg-danger'; break;
                                            case 'Cuti': $statusClass = 'bg-primary'; break;
                                            default: $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['notes'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable('#attendanceTable', {
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari kehadiran..."
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
