<?php
$page_title = 'Laporan Karyawan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get filter parameters
$department_id = $_GET['department'] ?? '';
$position_id = $_GET['position'] ?? '';
$status = $_GET['status'] ?? '';

try {
    // Build query with filters
    $where = ["1=1"];
    $params = [];
    
    if ($department_id) {
        $where[] = "e.department_id = ?";
        $params[] = $department_id;
    }
    if ($position_id) {
        $where[] = "e.position_id = ?";
        $params[] = $position_id;
    }
    if ($status) {
        $where[] = "e.employment_status = ?";
        $params[] = $status;
    }
    
    $whereClause = implode(" AND ", $where);
    
    // Get employee statistics
    $statsStmt = $pdo->prepare("SELECT 
        COUNT(*) as total_employees,
        SUM(CASE WHEN employment_status = 'Active' THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN employment_status = 'Inactive' THEN 1 ELSE 0 END) as inactive_count,
        SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female_count,
        AVG(YEAR(CURDATE()) - YEAR(date_of_birth)) as avg_age,
        SUM(basic_salary) as total_salary_cost
        FROM employees e
        WHERE $whereClause");
    $statsStmt->execute($params);
    $stats = $statsStmt->fetch();
    
    // Get employees by department
    $deptStmt = $pdo->prepare("SELECT 
        d.department_name,
        COUNT(e.id) as employee_count,
        SUM(e.basic_salary) as total_salary
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id AND $whereClause
        GROUP BY d.id
        ORDER BY employee_count DESC");
    $deptStmt->execute($params);
    $byDepartment = $deptStmt->fetchAll();
    
    // Get employees by position
    $posStmt = $pdo->prepare("SELECT 
        p.position_name,
        p.level,
        COUNT(e.id) as employee_count,
        AVG(e.basic_salary) as avg_salary
        FROM positions p
        LEFT JOIN employees e ON p.id = e.position_id AND $whereClause
        GROUP BY p.id
        ORDER BY p.level DESC");
    $posStmt->execute($params);
    $byPosition = $posStmt->fetchAll();
    
    // Get employees by age group
    $ageStmt = $pdo->prepare("SELECT 
        CASE 
            WHEN YEAR(CURDATE()) - YEAR(date_of_birth) < 25 THEN '< 25'
            WHEN YEAR(CURDATE()) - YEAR(date_of_birth) BETWEEN 25 AND 35 THEN '25-35'
            WHEN YEAR(CURDATE()) - YEAR(date_of_birth) BETWEEN 36 AND 45 THEN '36-45'
            WHEN YEAR(CURDATE()) - YEAR(date_of_birth) BETWEEN 46 AND 55 THEN '46-55'
            ELSE '> 55'
        END as age_group,
        COUNT(*) as employee_count
        FROM employees e
        WHERE $whereClause
        GROUP BY age_group
        ORDER BY age_group");
    $ageStmt->execute($params);
    $byAge = $ageStmt->fetchAll();
    
    // Get all employees for table
    $empStmt = $pdo->prepare("SELECT e.*, 
        d.department_name,
        p.position_name,
        (SELECT COUNT(*) FROM contracts c WHERE c.employee_id = e.id AND c.contract_status = 'Active') as has_contract
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN positions p ON e.position_id = p.id
        WHERE $whereClause
        ORDER BY e.first_name");
    $empStmt->execute($params);
    $employees = $empStmt->fetchAll();
    
    // Get departments for filter
    $deptFilterStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $deptFilterStmt->fetchAll();
    
    // Get positions for filter
    $posFilterStmt = $pdo->query("SELECT * FROM positions ORDER BY position_name");
    $positions = $posFilterStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching employee report: " . $e->getMessage());
    $stats = [];
    $byDepartment = [];
    $byPosition = [];
    $byAge = [];
    $employees = [];
    $departments = [];
    $positions = [];
}

$activePercent = $stats['total_employees'] > 0 ? round(($stats['active_count'] / $stats['total_employees']) * 100, 1) : 0;
$malePercent = $stats['total_employees'] > 0 ? round(($stats['male_count'] / $stats['total_employees']) * 100, 1) : 0;
$femalePercent = $stats['total_employees'] > 0 ? round(($stats['female_count'] / $stats['total_employees']) * 100, 1) : 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-chart-bar me-2"></i> Laporan Karyawan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item">Laporan</li>
                    <li class="breadcrumb-item active">Laporan Karyawan</li>
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
                        <label class="form-label">Jabatan</label>
                        <select name="position" class="form-select form-select-sm">
                            <option value="">Semua Jabatan</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['id']; ?>" <?php echo ($position_id == $pos['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pos['position_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Active" <?php echo ($status == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                            <a href="/hrm/admin/reports/employee.php" class="btn btn-secondary btn-sm">
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
                        <p class="text-muted mb-1">Total Karyawan</p>
                        <h3 class="mb-0"><?php echo $stats['total_employees'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-user-check"></i> <?php echo $stats['active_count'] ?? 0; ?> Aktif
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-users"></i>
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
                        <p class="text-muted mb-1">Rata-rata Usia</p>
                        <h3 class="mb-0"><?php echo round($stats['avg_age'] ?? 0); ?> Tahun</h3>
                        <small class="text-info">
                            <i class="fas fa-birthday-cake"></i> Usia Karyawan
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-user-clock"></i>
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
                        <p class="text-muted mb-1">Pria</p>
                        <h3 class="mb-0"><?php echo $stats['male_count'] ?? 0; ?></h3>
                        <small class="text-primary">
                            <i class="fas fa-male"></i> <?php echo $malePercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-male"></i>
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
                        <p class="text-muted mb-1">Wanita</p>
                        <h3 class="mb-0"><?php echo $stats['female_count'] ?? 0; ?></h3>
                        <small class="text-danger">
                            <i class="fas fa-female"></i> <?php echo $femalePercent; ?>%
                        </small>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-female"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- By Department -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i> Karyawan per Departemen
                </h5>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- By Position -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-briefcase me-2"></i> Karyawan per Jabatan
                </h5>
            </div>
            <div class="card-body">
                <canvas id="positionChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Age Distribution & Salary -->
<div class="row mb-4">
    <!-- By Age Group -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Distribusi Usia
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($byAge as $age): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo $age['age_group']; ?> Tahun</span>
                            <span class="fw-bold"><?php echo $age['employee_count']; ?> Karyawan</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <?php 
                            $percent = $stats['total_employees'] > 0 ? ($age['employee_count'] / $stats['total_employees']) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-info" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Salary Cost -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i> Biaya Gaji per Departemen
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($byDepartment as $dept): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo htmlspecialchars($dept['department_name']); ?></span>
                            <span class="fw-bold"><?php echo formatCurrency($dept['total_salary']); ?></span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <?php 
                            $percent = $stats['total_salary_cost'] > 0 ? ($dept['total_salary'] / $stats['total_salary_cost']) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-success" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="alert alert-success mt-3 mb-0">
                    <div class="d-flex justify-content-between">
                        <strong>Total Biaya Gaji:</strong>
                        <strong><?php echo formatCurrency($stats['total_salary_cost']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Karyawan
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employeeTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                                <th>Gaji Pokok</th>
                                <th>Kontrak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($emp['position_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($emp['employment_status'] == 'Active') ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $emp['employment_status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatCurrency($emp['basic_salary']); ?></td>
                                    <td>
                                        <?php if ($emp['has_contract'] > 0): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Ada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-times"></i> Tidak</span>
                                        <?php endif; ?>
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
    initDataTable('#employeeTable', {
        order: [[1, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari karyawan..."
        }
    });
    
    // Department Chart
    new Chart(document.getElementById('departmentChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($byDepartment, 'department_name')); ?>,
            datasets: [{
                label: 'Jumlah Karyawan',
                data: <?php echo json_encode(array_column($byDepartment, 'employee_count')); ?>,
                backgroundColor: 'rgba(44, 44, 44, 0.8)',
                borderColor: 'rgba(44, 44, 44, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Position Chart
    new Chart(document.getElementById('positionChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($byPosition, 'position_name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($byPosition, 'employee_count')); ?>,
                backgroundColor: [
                    'rgba(44, 44, 44, 0.8)',
                    'rgba(108, 108, 108, 0.8)',
                    'rgba(74, 74, 74, 0.8)',
                    'rgba(58, 58, 58, 0.8)',
                    'rgba(90, 90, 90, 0.8)',
                    'rgba(80, 80, 80, 0.8)'
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
    window.location.href = '/hrm/admin/reports/export_employee.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>';
}
</script>
