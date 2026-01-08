<?php
$page_title = 'Create New Shift';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

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
    
    $errors = [];
    
    // Validation
    if (empty($shiftName)) $errors[] = 'Nama shift harus diisi';
    if (empty($shiftCode)) $errors[] = 'Kode shift harus diisi';
    if (empty($startTime)) $errors[] = 'Jam mulai harus diisi';
    if (empty($endTime)) $errors[] = 'Jam selesai harus diisi';
    
    // Check duplicate code
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM work_shifts WHERE shift_code = ?");
        $checkStmt->execute([$shiftCode]);
        if ($checkStmt->fetch()) {
            $errors[] = 'Kode shift sudah digunakan';
        }
    }
    
    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("INSERT INTO work_shifts 
                                        (shift_name, shift_code, start_time, end_time, grace_period_minutes, 
                                         shift_allowance, is_night_shift, description)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([
                $shiftName, $shiftCode, $startTime, $endTime, 
                $gracePeriod, $shiftAllowance, $isNightShift, $description
            ]);
            
            $_SESSION['success'] = 'Shift berhasil ditambahkan';
            header('Location: /hrm/admin/shifts/index.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-plus-circle me-2"></i> Tambah Shift Baru</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/shifts/index.php">Shift Kerja</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
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
                <h5 class="mb-0">Form Shift Baru</h5>
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
                                   value="<?php echo htmlspecialchars($_POST['shift_name'] ?? ''); ?>" 
                                   placeholder="e.g., Shift Pagi" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Shift <span class="text-danger">*</span></label>
                            <input type="text" name="shift_code" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['shift_code'] ?? ''); ?>" 
                                   placeholder="e.g., PAGI" required maxlength="20">
                            <small class="text-muted">Huruf kapital, tanpa spasi</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grace Period (menit)</label>
                            <input type="number" name="grace_period" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['grace_period'] ?? '15'); ?>" 
                                   min="0" max="60">
                            <small class="text-muted">Toleransi keterlambatan</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tunjangan Shift (Rp)</label>
                            <input type="number" name="shift_allowance" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['shift_allowance'] ?? '0'); ?>" 
                                   min="0" step="1000">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_night_shift" class="form-check-input" 
                                   id="isNightShift" <?php echo isset($_POST['is_night_shift']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isNightShift">
                                <i class="fas fa-moon me-1"></i> Shift Malam (Tunjangan Ekstra)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Keterangan tambahan..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/hrm/admin/shifts/index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Panduan</h5>
            </div>
            <div class="card-body">
                <h6>Contoh Shift:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Shift Pagi</strong><br>
                        <small class="text-muted">08:00 - 17:00 (Grace: 15 menit)</small>
                    </li>
                    <li class="mb-2">
                        <strong>Shift Siang</strong><br>
                        <small class="text-muted">14:00 - 22:00 (Tunjangan: Rp 50.000)</small>
                    </li>
                    <li class="mb-2">
                        <strong>Shift Malam</strong><br>
                        <small class="text-muted">22:00 - 06:00 (Tunjangan: Rp 100.000)</small>
                    </li>
                </ul>
                
                <hr>
                
                <h6>Tips:</h6>
                <ul class="small">
                    <li>Kode shift harus unik</li>
                    <li>Grace period untuk toleransi keterlambatan</li>
                    <li>Shift malam otomatis dapat tunjangan ekstra</li>
                    <li>Tunjangan akan masuk ke perhitungan payroll</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
