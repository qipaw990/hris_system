<?php
$page_title = 'Tambah User';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Admin only access
if ($_SESSION['role'] !== 'Admin') {
    redirect('/hrm/admin/index.php', 'error', 'Akses ditolak');
}

// Get all employees for dropdown
try {
    $stmt = $pdo->query("SELECT id, employee_code, first_name, last_name, email 
                         FROM employees 
                         WHERE id NOT IN (SELECT employee_id FROM users WHERE employee_id IS NOT NULL)
                         ORDER BY first_name ASC");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching employees: " . $e->getMessage());
    $employees = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-user-plus me-2"></i> Tambah User</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/users/index.php">Manajemen User</a></li>
                    <li class="breadcrumb-item active">Tambah User</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/users/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Add User Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i> Form Tambah User</h5>
            </div>
            <div class="card-body">
                <form action="/hrm/admin/users/process_add.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required 
                               placeholder="Username untuk login">
                        <small class="text-muted">Username harus unik</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required 
                               placeholder="email@example.com">
                        <small class="text-muted">Email harus unik</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required 
                               minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirm" class="form-control" required 
                               minlength="6" placeholder="Ketik ulang password">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <option value="Admin">Admin - Full Access</option>
                            <option value="HR">HR - HR Management</option>
                            <option value="Employee">Employee - Limited Access</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Link ke Karyawan (Opsional)</label>
                        <select name="employee_id" class="form-select">
                            <option value="">Tidak ada</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                    (<?php echo htmlspecialchars($emp['employee_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hubungkan user ini dengan data karyawan</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" selected>Aktif</option>
                            <option value="Inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/hrm/admin/users/index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card fade-in">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informasi Role</h6>
            </div>
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-user-shield me-1"></i> Admin</h6>
                <p class="small">Full access ke semua modul termasuk User Management dan System Settings</p>
                
                <h6 class="text-warning"><i class="fas fa-user-tie me-1"></i> HR</h6>
                <p class="small">Access ke modul HR: Recruitment, Payroll, KPI, Reports</p>
                
                <h6 class="text-secondary"><i class="fas fa-user me-1"></i> Employee</h6>
                <p class="small">Limited access: Self-service untuk attendance, leave, profile</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
