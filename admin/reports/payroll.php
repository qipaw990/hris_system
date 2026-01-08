<?php
$page_title = 'Laporan Penggajian';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get filter parameters
$year = $_GET['year'] ?? date('Y');
$department_id = $_GET['department'] ?? '';

try {
    // Build query with filters
    $where = ["YEAR(pp.payment_date) = ?"];
    $params = [$year];
    
    if ($department_id) {
        $where[] = "e.department_id = ?";
        $params[] = $department_id;
    }
    
    $whereClause = implode(" AND ", $where);
    
    // Get payroll statistics
    $statsStmt = $pdo->prepare("SELECT 
        COUNT(DISTINCT pp.id) as total_periods,
        COUNT(DISTINCT ps.employee_id) as total_employees,
        SUM(ps.basic_salary) as total_basic_salary,
        SUM(ps.total_earnings) as total_earnings,
        SUM(ps.total_deductions) as total_deductions,
        SUM(ps.net_salary) as total_net_salary,
        AVG(ps.net_salary) as avg_net_salary
        FROM payroll_periods pp
        LEFT JOIN payroll_slips ps ON pp.id = ps.period_id
        LEFT JOIN employees e ON ps.employee_id = e.id
        WHERE $whereClause AND pp.status IN ('Processed', 'Paid')");
    $statsStmt->execute($params);
    $stats = $statsStmt->fetch();
    
    // Get payroll by month
    $monthlyStmt = $pdo->prepare("SELECT 
        pp.period_month,
        pp.period_name,
        COUNT(DISTINCT ps.employee_id) as employee_count,
        SUM(ps.total_earnings) as total_earnings,
        SUM(ps.total_deductions) as total_deductions,
        SUM(ps.net_salary) as total_net_salary
        FROM payroll_periods pp
        LEFT JOIN payroll_slips ps ON pp.id = ps.period_id
        LEFT JOIN employees e ON ps.employee_id = e.id
        WHERE $whereClause AND pp.status IN ('Processed', 'Paid')
        GROUP BY pp.id
        ORDER BY pp.period_month");
    $monthlyStmt->execute($params);
    $monthlyData = $monthlyStmt->fetchAll();
    
    // Get payroll by department
    $deptStmt = $pdo->prepare("SELECT 
        d.department_name,
        COUNT(DISTINCT ps.employee_id) as employee_count,
        SUM(ps.basic_salary) as total_basic_salary,
        SUM(ps.total_earnings) as total_earnings,
        SUM(ps.total_deductions) as total_deductions,
        SUM(ps.net_salary) as total_net_salary,
        AVG(ps.net_salary) as avg_net_salary
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        LEFT JOIN payroll_slips ps ON e.id = ps.employee_id
        LEFT JOIN payroll_periods pp ON ps.period_id = pp.id
        WHERE $whereClause AND pp.status IN ('Processed', 'Paid')
        GROUP BY d.id
        ORDER BY total_net_salary DESC");
    $deptStmt->execute($params);
    $byDepartment = $deptStmt->fetchAll();
    
    // Get component breakdown
    $componentStmt = $pdo->prepare("SELECT 
        psd.component_name,
        psd.component_type,
        SUM(psd.amount) as total_amount,
        COUNT(DISTINCT psd.slip_id) as usage_count
        FROM payroll_slip_details psd
        LEFT JOIN payroll_slips ps ON psd.slip_id = ps.id
        LEFT JOIN payroll_periods pp ON ps.period_id = pp.id
        LEFT JOIN employees e ON ps.employee_id = e.id
        WHERE $whereClause AND pp.status IN ('Processed', 'Paid')
        GROUP BY psd.component_id
        ORDER BY psd.component_type, total_amount DESC");
    $componentStmt->execute($params);
    $components = $componentStmt->fetchAll();
    
    // Separate earnings and deductions
    $earnings = array_filter($components, fn($c) => $c['component_type'] == 'Earning');
    $deductions = array_filter($components, fn($c) => $c['component_type'] == 'Deduction');
    
    // Get employee payroll summary
    $empStmt = $pdo->prepare("SELECT 
        e.employee_code,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        d.department_name,
        p.position_name,
        COUNT(DISTINCT ps.id) as payment_count,
        AVG(ps.basic_salary) as avg_basic_salary,
        AVG(ps.total_earnings) as avg_earnings,
        AVG(ps.total_deductions) as avg_deductions,
        AVG(ps.net_salary) as avg_net_salary,
        SUM(ps.net_salary) as total_received
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN positions p ON e.position_id = p.id
        LEFT JOIN payroll_slips ps ON e.id = ps.employee_id
        LEFT JOIN payroll_periods pp ON ps.period_id = pp.id
        WHERE $whereClause AND pp.status IN ('Processed', 'Paid')
        GROUP BY e.id
        HAVING payment_count > 0
        ORDER BY total_received DESC");
    $empStmt->execute($params);
    $employeePayroll = $empStmt->fetchAll();
    
    // Get departments for filter
    $deptFilterStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $deptFilterStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching payroll report: " . $e->getMessage());
    $stats = [];
    $monthlyData = [];
    $byDepartment = [];
    $components = [];
    $earnings = [];
    $deductions = [];
    $employeePayroll = [];
    $departments = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-file-invoice-dollar me-2"></i> Laporan Penggajian</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item">Laporan</li>
                    <li class="breadcrumb-item active">Laporan Penggajian</li>
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
                    <div class="col-md-4">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select form-select-sm">
                            <?php for($y = 2020; $y <= 2030; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                            <a href="/hrm/admin/reports/payroll.php" class="btn btn-secondary btn-sm">
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
                        <p class="text-muted mb-1">Total Periode</p>
                        <h3 class="mb-0"><?php echo $stats['total_periods'] ?? 0; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> Periode Gaji
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-calendar-alt"></i>
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
                        <p class="text-muted mb-1">Total Pendapatan</p>
                        <h4 class="mb-0"><?php echo formatCurrency($stats['total_earnings'] ?? 0); ?></h4>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> Gross Salary
                        </small>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-money-bill-wave"></i>
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
                        <p class="text-muted mb-1">Total Potongan</p>
                        <h4 class="mb-0"><?php echo formatCurrency($stats['total_deductions'] ?? 0); ?></h4>
                        <small class="text-danger">
                            <i class="fas fa-arrow-down"></i> Deductions
                        </small>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-minus-circle"></i>
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
                        <p class="text-muted mb-1">Total Net</p>
                        <h4 class="mb-0"><?php echo formatCurrency($stats['total_net_salary'] ?? 0); ?></h4>
                        <small class="text-info">
                            <i class="fas fa-wallet"></i> Take Home Pay
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Monthly Trend -->
    <div class="col-lg-8 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i> Tren Penggajian Bulanan
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Component Breakdown -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Breakdown Komponen
                </h5>
            </div>
            <div class="card-body">
                <h6 class="text-success">Pendapatan</h6>
                <?php foreach (array_slice($earnings, 0, 5) as $comp): ?>
                    <div class="mb-2">
                        <small><?php echo htmlspecialchars($comp['component_name']); ?>:</small>
                        <strong class="float-end"><?php echo formatCurrency($comp['total_amount']); ?></strong>
                    </div>
                <?php endforeach; ?>
                
                <hr>
                
                <h6 class="text-danger">Potongan</h6>
                <?php foreach (array_slice($deductions, 0, 5) as $comp): ?>
                    <div class="mb-2">
                        <small><?php echo htmlspecialchars($comp['component_name']); ?>:</small>
                        <strong class="float-end"><?php echo formatCurrency($comp['total_amount']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Department Payroll -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i> Penggajian per Departemen
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Karyawan</th>
                                <th>Total Gaji Pokok</th>
                                <th>Total Pendapatan</th>
                                <th>Total Potongan</th>
                                <th>Total Net</th>
                                <th>Rata-rata Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byDepartment as $dept): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dept['department_name']); ?></strong></td>
                                    <td><?php echo $dept['employee_count']; ?></td>
                                    <td><?php echo formatCurrency($dept['total_basic_salary']); ?></td>
                                    <td class="text-success"><?php echo formatCurrency($dept['total_earnings']); ?></td>
                                    <td class="text-danger"><?php echo formatCurrency($dept['total_deductions']); ?></td>
                                    <td><strong><?php echo formatCurrency($dept['total_net_salary']); ?></strong></td>
                                    <td><?php echo formatCurrency($dept['avg_net_salary']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>TOTAL</th>
                                <th><?php echo $stats['total_employees'] ?? 0; ?></th>
                                <th><?php echo formatCurrency($stats['total_basic_salary'] ?? 0); ?></th>
                                <th class="text-success"><?php echo formatCurrency($stats['total_earnings'] ?? 0); ?></th>
                                <th class="text-danger"><?php echo formatCurrency($stats['total_deductions'] ?? 0); ?></th>
                                <th><strong><?php echo formatCurrency($stats['total_net_salary'] ?? 0); ?></strong></th>
                                <th><?php echo formatCurrency($stats['avg_net_salary'] ?? 0); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Payroll Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Ringkasan Penggajian Karyawan
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="payrollTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Periode</th>
                                <th>Rata-rata Gaji Pokok</th>
                                <th>Rata-rata Pendapatan</th>
                                <th>Rata-rata Potongan</th>
                                <th>Rata-rata Net</th>
                                <th>Total Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employeePayroll as $emp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($emp['employee_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($emp['position_name'] ?? '-'); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $emp['payment_count']; ?>x</span></td>
                                    <td><?php echo formatCurrency($emp['avg_basic_salary']); ?></td>
                                    <td class="text-success"><?php echo formatCurrency($emp['avg_earnings']); ?></td>
                                    <td class="text-danger"><?php echo formatCurrency($emp['avg_deductions']); ?></td>
                                    <td><?php echo formatCurrency($emp['avg_net_salary']); ?></td>
                                    <td><strong><?php echo formatCurrency($emp['total_received']); ?></strong></td>
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
    initDataTable('#payrollTable', {
        order: [[9, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari karyawan..."
        }
    });
    
    // Monthly Chart
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($monthlyData, 'period_name')); ?>,
            datasets: [
                {
                    label: 'Total Pendapatan',
                    data: <?php echo json_encode(array_column($monthlyData, 'total_earnings')); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Potongan',
                    data: <?php echo json_encode(array_column($monthlyData, 'total_deductions')); ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Net',
                    data: <?php echo json_encode(array_column($monthlyData, 'total_net_salary')); ?>,
                    backgroundColor: 'rgba(44, 44, 44, 0.8)',
                    borderColor: 'rgba(44, 44, 44, 1)',
                    borderWidth: 1
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
});

function exportToExcel() {
    window.location.href = '/hrm/admin/reports/export_payroll.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>';
}
</script>
