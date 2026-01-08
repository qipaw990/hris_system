<?php
$page_title = 'Manage Employee Shifts';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

$shiftId = $_GET['shift_id'] ?? 0;

// Get shift details
try {
    $shiftStmt = $pdo->prepare("SELECT * FROM work_shifts WHERE id = ?");
    $shiftStmt->execute([$shiftId]);
    $shift = $shiftStmt->fetch();
    
    if (!$shift) {
        $_SESSION['error'] = 'Shift tidak ditemukan';
        header('Location: /hrm/admin/shifts/index.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error';
    header('Location: /hrm/admin/shifts/index.php');
    exit();
}

// Handle quick assign/unassign
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $employeeId = $_POST['employee_id'] ?? 0;
    $action = $_POST['action'];
    
    try {
        if ($action === 'assign') {
            // End current shift assignment (set to yesterday so it ends immediately)
            $pdo->prepare("UPDATE employee_shifts SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                          WHERE employee_id = ? AND (end_date IS NULL OR end_date >= CURDATE())")
                ->execute([$employeeId]);
            
            // Assign new shift
            $pdo->prepare("INSERT INTO employee_shifts 
                          (employee_id, shift_id, effective_date, is_permanent, assigned_by)
                          VALUES (?, ?, CURDATE(), 1, ?)")
                ->execute([$employeeId, $shiftId, $_SESSION['user_id']]);
            
            $_SESSION['success'] = 'Karyawan berhasil di-assign ke shift';
        } elseif ($action === 'unassign') {
            // End shift assignment (set to yesterday so it ends immediately)
            $pdo->prepare("UPDATE employee_shifts SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                          WHERE employee_id = ? AND shift_id = ? AND (end_date IS NULL OR end_date >= CURDATE())")
                ->execute([$employeeId, $shiftId]);
            
            $_SESSION['success'] = 'Karyawan berhasil di-unassign dari shift';
        }
        
        header("Location: /hrm/admin/shifts/manage.php?shift_id={$shiftId}");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

// Get filter parameters
$searchUnassigned = $_GET['search'] ?? '';
$departmentFilter = $_GET['department'] ?? '';

// Get employees assigned to this shift
$assignedStmt = $pdo->prepare("SELECT e.*, d.department_name, es.effective_date, es.end_date
                               FROM employees e
                               LEFT JOIN departments d ON e.department_id = d.id
                               INNER JOIN employee_shifts es ON e.id = es.employee_id
                               WHERE es.shift_id = ?
                               AND (es.end_date IS NULL OR es.end_date >= CURDATE())
                               ORDER BY e.first_name, e.last_name");
$assignedStmt->execute([$shiftId]);
$assignedEmployees = $assignedStmt->fetchAll();

// Get employees NOT assigned to this shift with filters
$unassignedQuery = "SELECT e.*, d.department_name,
                    (SELECT ws.shift_name FROM employee_shifts es2
                     JOIN work_shifts ws ON es2.shift_id = ws.id
                     WHERE es2.employee_id = e.id
                     AND (es2.end_date IS NULL OR es2.end_date >= CURDATE())
                     ORDER BY es2.effective_date DESC LIMIT 1) as current_shift
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    WHERE e.id NOT IN (
                        SELECT employee_id FROM employee_shifts
                        WHERE shift_id = ?
                        AND (end_date IS NULL OR end_date >= CURDATE())
                    )";

$unassignedParams = [$shiftId];

// Add search filter
if (!empty($searchUnassigned)) {
    $unassignedQuery .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
    $searchTerm = "%{$searchUnassigned}%";
    $unassignedParams[] = $searchTerm;
    $unassignedParams[] = $searchTerm;
    $unassignedParams[] = $searchTerm;
}

// Add department filter
if (!empty($departmentFilter)) {
    $unassignedQuery .= " AND e.department_id = ?";
    $unassignedParams[] = $departmentFilter;
}

$unassignedQuery .= " ORDER BY e.first_name, e.last_name";

$unassignedStmt = $pdo->prepare($unassignedQuery);
$unassignedStmt->execute($unassignedParams);
$unassignedEmployees = $unassignedStmt->fetchAll();

// Get all departments for filter
$deptStmt = $pdo->query("SELECT id, department_name FROM departments ORDER BY department_name");
$departments = $deptStmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<style>
.employee-card {
    transition: all 0.3s ease;
    cursor: pointer;
}
.employee-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.card-container {
    max-height: 600px;
    overflow-y: auto;
}
.shift-badge {
    font-size: 0.85rem;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-users-cog me-2"></i> Manage Shift Assignment</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/shifts/index.php">Shift Kerja</a></li>
                    <li class="breadcrumb-item active">Manage</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/shifts/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Shift Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2"><?php echo htmlspecialchars($shift['shift_name']); ?></h3>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                            <?php echo date('H:i', strtotime($shift['end_time'])); ?>
                            <span class="ms-3">
                                <i class="fas fa-hourglass-half me-2"></i>
                                Grace: <?php echo $shift['grace_period_minutes']; ?> menit
                            </span>
                            <?php if ($shift['shift_allowance'] > 0): ?>
                                <span class="ms-3">
                                    <i class="fas fa-coins me-2"></i>
                                    Tunjangan: Rp <?php echo number_format($shift['shift_allowance'], 0, ',', '.'); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <h2 class="mb-0"><?php echo count($assignedEmployees); ?></h2>
                        <small>Karyawan Assigned</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="row">
    <!-- Unassigned Employees -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-3">
                    <i class="fas fa-user-plus text-muted me-2"></i>
                    Karyawan Belum Di-Assign
                    <span class="badge bg-secondary float-end"><?php echo count($unassignedEmployees); ?></span>
                </h5>
                
                <!-- Filter Form -->
                <form method="GET" class="row g-2">
                    <input type="hidden" name="shift_id" value="<?php echo $shiftId; ?>">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari nama atau kode karyawan..." 
                               value="<?php echo htmlspecialchars($searchUnassigned); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="department" class="form-select form-select-sm">
                            <option value="">Semua Departemen</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                        <?php echo ($departmentFilter == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($searchUnassigned) || !empty($departmentFilter)): ?>
                    <div class="mt-2">
                        <a href="?shift_id=<?php echo $shiftId; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear Filter
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body card-container">
                <?php if (empty($unassignedEmployees)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <p>Semua karyawan sudah di-assign ke shift</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($unassignedEmployees as $emp): ?>
                        <div class="employee-card card mb-2">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-id-badge me-1"></i>
                                            <?php echo htmlspecialchars($emp['employee_code']); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?>
                                        </small>
                                        <?php if ($emp['current_shift']): ?>
                                            <br>
                                            <span class="badge bg-info shift-badge mt-1">
                                                <i class="fas fa-clock me-1"></i>
                                                Current: <?php echo htmlspecialchars($emp['current_shift']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="employee_id" value="<?php echo $emp['id']; ?>">
                                            <input type="hidden" name="action" value="assign">
                                            <button type="submit" class="btn btn-sm btn-success" title="Assign ke shift ini">
                                                <i class="fas fa-plus"></i> Assign
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Assigned Employees -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-check me-2"></i>
                    Karyawan Sudah Di-Assign
                    <span class="badge bg-light text-dark float-end"><?php echo count($assignedEmployees); ?></span>
                </h5>
            </div>
            <div class="card-body card-container">
                <?php if (empty($assignedEmployees)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users-slash fa-3x mb-3"></i>
                        <p>Belum ada karyawan di-assign ke shift ini</p>
                        <small>Assign karyawan dari kolom sebelah kiri</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($assignedEmployees as $emp): ?>
                        <div class="employee-card card mb-2 border-success">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-id-badge me-1"></i>
                                            <?php echo htmlspecialchars($emp['employee_code']); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?>
                                        </small>
                                        <br>
                                        <span class="badge bg-success shift-badge mt-1">
                                            <i class="fas fa-calendar me-1"></i>
                                            Sejak: <?php echo date('d M Y', strtotime($emp['effective_date'])); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="employee_id" value="<?php echo $emp['id']; ?>">
                                            <input type="hidden" name="action" value="unassign">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Unassign karyawan dari shift ini?')"
                                                    title="Unassign dari shift ini">
                                                <i class="fas fa-times"></i> Unassign
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-primary"><?php echo count($assignedEmployees); ?></h3>
                <p class="text-muted mb-0">Karyawan di Shift Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-secondary"><?php echo count($unassignedEmployees); ?></h3>
                <p class="text-muted mb-0">Belum Di-Assign</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-success"><?php echo count($assignedEmployees) + count($unassignedEmployees); ?></h3>
                <p class="text-muted mb-0">Total Karyawan</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Auto-hide success/error messages
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 3000);
</script>
