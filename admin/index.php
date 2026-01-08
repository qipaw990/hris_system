<?php
$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

// Get statistics
try {
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees WHERE employment_status = 'Active'");
    $totalEmployees = $stmt->fetch()['total'];
    
    // Total departments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
    $totalDepartments = $stmt->fetch()['total'];
    
    // Total positions
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM positions");
    $totalPositions = $stmt->fetch()['total'];
    
    // New employees this month
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees 
                         WHERE MONTH(hire_date) = MONTH(CURRENT_DATE()) 
                         AND YEAR(hire_date) = YEAR(CURRENT_DATE())");
    $newEmployees = $stmt->fetch()['total'];
    
    // Recent employees
    $stmt = $pdo->query("SELECT e.*, d.department_name, p.position_name 
                         FROM employees e
                         LEFT JOIN departments d ON e.department_id = d.id
                         LEFT JOIN positions p ON e.position_id = p.id
                         ORDER BY e.created_at DESC LIMIT 5");
    $recentEmployees = $stmt->fetchAll();
    
    // Employees by department
    $stmt = $pdo->query("SELECT d.department_name, COUNT(e.id) as count 
                         FROM departments d
                         LEFT JOIN employees e ON d.id = e.department_id AND e.employment_status = 'Active'
                         GROUP BY d.id, d.department_name
                         ORDER BY count DESC");
    $employeesByDept = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="text-muted">
                <i class="fas fa-calendar-alt me-2"></i>
                <?php echo date('l, d F Y'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row fade-in">
    <!-- Total Employees -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">
                            Total Employees
                        </div>
                        <div class="h3 mb-0 font-weight-bold">
                            <?php echo number_format($totalEmployees); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/hrm/admin/employees/index.php" class="btn btn-sm btn-primary">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Departments -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">
                            Departments
                        </div>
                        <div class="h3 mb-0 font-weight-bold">
                            <?php echo number_format($totalDepartments); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon success">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/hrm/admin/departments/index.php" class="btn btn-sm btn-success">
                        Manage <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Positions -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">
                            Positions
                        </div>
                        <div class="h3 mb-0 font-weight-bold">
                            <?php echo number_format($totalPositions); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon warning">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/hrm/admin/positions/index.php" class="btn btn-sm btn-warning">
                        Manage <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Employees -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">
                            New This Month
                        </div>
                        <div class="h3 mb-0 font-weight-bold">
                            <?php echo number_format($newEmployees); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon danger">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/hrm/admin/employees/add.php" class="btn btn-sm btn-danger">
                        Add New <i class="fas fa-plus ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Employees -->
    <div class="col-lg-8 mb-4">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i> Recent Employees
                </h5>
                <a href="/hrm/admin/employees/index.php" class="btn btn-sm btn-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentEmployees)): ?>
                                <?php foreach ($recentEmployees as $emp): ?>
                                    <tr>
                                        <td>
                                            <?php if ($emp['photo']): ?>
                                                <img src="/hrm/uploads/employees/<?php echo htmlspecialchars($emp['photo']); ?>" 
                                                     alt="Photo" class="employee-photo" style="width: 40px; height: 40px;">
                                            <?php else: ?>
                                                <img src="<?php echo getDefaultAvatar($emp['gender']); ?>" 
                                                     alt="Avatar" class="employee-photo" style="width: 40px; height: 40px;">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($emp['employee_code']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($emp['position_name'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($emp['employment_status']); ?>">
                                                <?php echo htmlspecialchars($emp['employment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/hrm/admin/employees/view.php?id=<?php echo $emp['id']; ?>" 
                                               class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No employees found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Employees by Department -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Employees by Department
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($employeesByDept)): ?>
                    <?php foreach ($employeesByDept as $dept): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><?php echo htmlspecialchars($dept['department_name']); ?></span>
                                <span class="badge bg-primary"><?php echo $dept['count']; ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <?php 
                                $percentage = $totalEmployees > 0 ? ($dept['count'] / $totalEmployees) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-gradient-primary" role="progressbar" 
                                     style="width: <?php echo $percentage; ?>%" 
                                     aria-valuenow="<?php echo $percentage; ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/hrm/admin/employees/add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i> Add New Employee
                    </a>
                    <a href="/hrm/admin/departments/index.php" class="btn btn-success">
                        <i class="fas fa-building me-2"></i> Manage Departments
                    </a>
                    <a href="/hrm/admin/positions/index.php" class="btn btn-warning">
                        <i class="fas fa-briefcase me-2"></i> Manage Positions
                    </a>
                    <a href="/hrm/admin/reports/employee.php" class="btn btn-info">
                        <i class="fas fa-file-alt me-2"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
