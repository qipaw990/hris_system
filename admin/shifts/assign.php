<?php
// Process POST before any output
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

// Handle form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeIds = $_POST['employee_ids'] ?? [];
    $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
    $isPermanent = isset($_POST['is_permanent']) ? 1 : 0;
    $endDate = $isPermanent ? null : ($_POST['end_date'] ?? null);
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = [];
    
    if (empty($employeeIds)) {
        $errors[] = 'Pilih minimal 1 karyawan';
    }
    
    if (!$isPermanent && empty($endDate)) {
        $errors[] = 'End date harus diisi untuk assignment temporary';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $insertStmt = $pdo->prepare("INSERT INTO employee_shifts 
                                        (employee_id, shift_id, effective_date, end_date, is_permanent, notes, assigned_by)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $successCount = 0;
            foreach ($employeeIds as $employeeId) {
                // Check for overlapping assignment
                $checkStmt = $pdo->prepare("SELECT id FROM employee_shifts 
                                           WHERE employee_id = ? 
                                           AND (end_date IS NULL OR end_date >= ?)
                                           AND effective_date <= ?");
                $checkStmt->execute([$employeeId, $effectiveDate, $endDate ?? '9999-12-31']);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt->execute([
                        $employeeId, $shiftId, $effectiveDate, 
                        $endDate, $isPermanent, $notes, $_SESSION['user_id']
                    ]);
                    $successCount++;
                }
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = "$successCount karyawan berhasil di-assign ke shift {$shift['shift_name']}";
            header('Location: /hrm/admin/shifts/index.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all employees
try {
    $empStmt = $pdo->query("SELECT e.id, e.employee_code, e.first_name, e.last_name, d.department_name,
                           (SELECT shift_id FROM employee_shifts es 
                            WHERE es.employee_id = e.id 
                            AND (es.end_date IS NULL OR es.end_date >= CURDATE())
                            ORDER BY es.effective_date DESC LIMIT 1) as current_shift_id
                           FROM employees e
                           LEFT JOIN departments d ON e.department_id = d.id
                           ORDER BY e.first_name, e.last_name");
    $employees = $empStmt->fetchAll();
    
    // Debug: Log employee count
    error_log("Employee count for shift assignment: " . count($employees));
    
} catch (PDOException $e) {
    error_log("Error fetching employees for shift assignment: " . $e->getMessage());
    $employees = [];
}

// NOW include header (after all processing)
$page_title = 'Assign Shift to Employees';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-users me-2"></i> Assign Shift</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/shifts/index.php">Shift Kerja</a></li>
                    <li class="breadcrumb-item active">Assign</li>
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

<!-- Shift Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4 class="mb-2"><?php echo htmlspecialchars($shift['shift_name']); ?></h4>
                <p class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                    <?php echo date('H:i', strtotime($shift['end_time'])); ?>
                    <span class="ms-3">
                        <i class="fas fa-coins me-2"></i>
                        Tunjangan: Rp <?php echo number_format($shift['shift_allowance'], 0, ',', '.'); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pilih Karyawan</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_date" class="form-control" 
                                   value="<?php echo $_POST['effective_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assignment Type</label>
                            <div class="form-check">
                                <input type="checkbox" name="is_permanent" class="form-check-input" 
                                       id="isPermanent" <?php echo !isset($_POST['is_permanent']) || isset($_POST['is_permanent']) ? 'checked' : ''; ?>
                                       onchange="document.getElementById('endDateField').style.display = this.checked ? 'none' : 'block'">
                                <label class="form-check-label" for="isPermanent">
                                    Permanent Assignment
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3" id="endDateField" style="display: none;">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?php echo $_POST['end_date'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Catatan tambahan..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Select Employees</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect All</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($employees)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Tidak ada karyawan ditemukan</strong><br>
                                    Pastikan sudah ada data karyawan di sistem. 
                                    <a href="/hrm/admin/employees/create.php">Tambah karyawan baru</a>
                                </div>
                            <?php else: ?>
                                <table class="table table-hover table-sm">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                            </th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Current Shift</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employees as $emp): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="employee_ids[]" 
                                                           value="<?php echo $emp['id']; ?>" 
                                                           class="employee-checkbox">
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($emp['employee_code']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?></td>
                                                <td>
                                                    <?php if ($emp['current_shift_id']): ?>
                                                        <span class="badge bg-info">Has Shift</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No Shift</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/hrm/admin/shifts/index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Assign Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
}
</script>
