<?php
$page_title = 'Edit Shift';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

$shiftId = $_GET['id'] ?? 0;

// Get shift data
try {
    $stmt = $pdo->prepare("SELECT * FROM work_shifts WHERE id = ?");
    $stmt->execute([$shiftId]);
    $shift = $stmt->fetch();
    
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shiftName = trim($_POST['shift_name'] ?? '');
    $shiftCode = strtoupper(trim($_POST['shift_code'] ?? ''));
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $gracePeriod = intval($_POST['grace_period'] ?? 15);
    $shiftAllowance = floatval($_POST['shift_allowance'] ?? 0);
    $isNightShift = isset($_POST['is_night_shift']) ? 1 : 0;
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($shiftName)) $errors[] = 'Nama shift harus diisi';
    if (empty($shiftCode)) $errors[] = 'Kode shift harus diisi';
    if (empty($startTime)) $errors[] = 'Jam mulai harus diisi';
    if (empty($endTime)) $errors[] = 'Jam selesai harus diisi';
    
    // Check duplicate code (except current shift)
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM work_shifts WHERE shift_code = ? AND id != ?");
        $checkStmt->execute([$shiftCode, $shiftId]);
        if ($checkStmt->fetch()) {
            $errors[] = 'Kode shift sudah digunakan';
        }
    }
    
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE work_shifts SET
                                        shift_name = ?,
                                        shift_code = ?,
                                        start_time = ?,
                                        end_time = ?,
                                        grace_period_minutes = ?,
                                        shift_allowance = ?,
                                        is_night_shift = ?,
                                        description = ?,
                                        is_active = ?
                                        WHERE id = ?");
            $updateStmt->execute([
                $shiftName, $shiftCode, $startTime, $endTime,
                $gracePeriod, $shiftAllowance, $isNightShift, $description,
                $isActive, $shiftId
            ]);
            
            $_SESSION['success'] = 'Shift berhasil diupdate';
            header('Location: /hrm/admin/shifts/index.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
    
    // Reload shift data with form values
    $shift = array_merge($shift, $_POST);
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-edit me-2"></i> Edit Shift</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/shifts/index.php">Shift Kerja</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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

<!-- Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Edit Shift</h5>
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Shift <span class="text-danger">*</span></label>
                            <input type="text" name="shift_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($shift['shift_name']); ?>" 
                                   placeholder="e.g., Shift Pagi" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Shift <span class="text-danger">*</span></label>
                            <input type="text" name="shift_code" class="form-control" 
                                   value="<?php echo htmlspecialchars($shift['shift_code']); ?>" 
                                   placeholder="e.g., PAGI" required maxlength="20">
                            <small class="text-muted">Huruf kapital, tanpa spasi</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($shift['start_time']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($shift['end_time']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grace Period (menit)</label>
                            <input type="number" name="grace_period" class="form-control" 
                                   value="<?php echo htmlspecialchars($shift['grace_period_minutes']); ?>" 
                                   min="0" max="60">
                            <small class="text-muted">Toleransi keterlambatan</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tunjangan Shift (Rp)</label>
                            <input type="number" name="shift_allowance" class="form-control" 
                                   value="<?php echo htmlspecialchars($shift['shift_allowance']); ?>" 
                                   min="0" step="1000">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_night_shift" class="form-check-input" 
                                   id="isNightShift" <?php echo $shift['is_night_shift'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isNightShift">
                                <i class="fas fa-moon me-1"></i> Shift Malam (Tunjangan Ekstra)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" 
                                   id="isActive" <?php echo $shift['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isActive">
                                <i class="fas fa-check-circle me-1"></i> Shift Aktif
                            </label>
                            <small class="d-block text-muted">Nonaktifkan jika shift tidak digunakan lagi</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Keterangan tambahan..."><?php echo htmlspecialchars($shift['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/hrm/admin/shifts/index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informasi</h5>
            </div>
            <div class="card-body">
                <h6>Data Shift:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Kode:</strong><br>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($shift['shift_code']); ?></span>
                    </li>
                    <li class="mb-2">
                        <strong>Jam Kerja:</strong><br>
                        <small class="text-muted">
                            <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                            <?php echo date('H:i', strtotime($shift['end_time'])); ?>
                        </small>
                    </li>
                    <li class="mb-2">
                        <strong>Status:</strong><br>
                        <?php if ($shift['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </li>
                </ul>
                
                <hr>
                
                <h6>Peringatan:</h6>
                <ul class="small">
                    <li>Perubahan shift akan mempengaruhi karyawan yang sudah di-assign</li>
                    <li>Pastikan jam kerja sudah sesuai</li>
                    <li>Nonaktifkan shift jika tidak digunakan lagi</li>
                </ul>
            </div>
        </div>
        
        <!-- Assigned Employees Info -->
        <?php
        $assignedStmt = $pdo->prepare("SELECT COUNT(*) as total FROM employee_shifts 
                                       WHERE shift_id = ? AND (end_date IS NULL OR end_date >= CURDATE())");
        $assignedStmt->execute([$shiftId]);
        $assignedCount = $assignedStmt->fetch()['total'];
        ?>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Karyawan Assigned</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary"><?php echo $assignedCount; ?></h2>
                <p class="text-muted mb-0">Karyawan di shift ini</p>
                <a href="/hrm/admin/shifts/manage.php?shift_id=<?php echo $shiftId; ?>" 
                   class="btn btn-sm btn-outline-primary mt-3">
                    <i class="fas fa-users-cog me-1"></i> Manage Employees
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
