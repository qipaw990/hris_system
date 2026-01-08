<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

// Get current user data
$userId = $_SESSION['user_id'];
$error = null;
$user = null;
$empDetails = null;

try {
    // First, get basic user data
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = 'User tidak ditemukan';
    } else {
        // Try to find linked employee record by email
        $empStmt = $pdo->prepare("SELECT 
                                  e.id as emp_id,
                                  e.employee_code,
                                  e.first_name,
                                  e.last_name,
                                  e.email,
                                  e.phone,
                                  e.date_of_birth,
                                  e.address,
                                  e.hire_date,
                                  e.employment_status,
                                  e.basic_salary,
                                  e.photo,
                                  e.department_id,
                                  e.position_id,
                                  d.department_name,
                                  p.position_name
                                  FROM employees e
                                  LEFT JOIN departments d ON e.department_id = d.id
                                  LEFT JOIN positions p ON e.position_id = p.id
                                  WHERE e.email = ?");
        $empStmt->execute([$user['email']]);
        $empData = $empStmt->fetch();
        
        // Merge employee data into user array if found
        if ($empData) {
            $user = array_merge($user, $empData);
            $user['is_employee'] = true;
            $empDetails = $empData; // For backward compatibility
        } else {
            $user['is_employee'] = false;
            // Set default values for admin users
            $user['first_name'] = $user['username'];
            $user['last_name'] = '';
            $user['employee_code'] = 'N/A';
        }
    }
    
} catch (PDOException $e) {
    error_log("Error fetching user profile: " . $e->getMessage());
    $error = 'Gagal memuat profil: ' . $e->getMessage();
}

// Now include header after all processing
$page_title = 'My Profile';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

// Show error if any
if ($error) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    echo '<a href="/hrm/admin/index.php" class="btn btn-primary">Kembali ke Dashboard</a>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-user-circle me-2"></i> My Profile</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Profil Saya</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Profile Content -->
<div class="row">
    <!-- Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in">
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="/hrm/assets/uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                             alt="Profile Photo" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                             style="width: 150px; height: 150px;">
                            <i class="fas fa-user fa-4x text-white"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h4 class="mb-1">
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h4>
                <p class="text-muted mb-2">
                    <i class="fas fa-id-badge me-1"></i> 
                    <?php echo htmlspecialchars($user['employee_code'] ?? 'N/A'); ?>
                </p>
                <p class="text-muted mb-3">
                    <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                </p>
                
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPhotoModal">
                    <i class="fas fa-camera me-2"></i> Ubah Foto
                </button>
            </div>
        </div>
        
        <!-- Quick Info -->
        <?php if ($user['is_employee']): ?>
        <div class="card fade-in mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informasi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Departemen</small>
                    <p class="mb-0"><strong><?php echo htmlspecialchars($empDetails['department_name'] ?? '-'); ?></strong></p>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted">Jabatan</small>
                    <p class="mb-0"><strong><?php echo htmlspecialchars($empDetails['position_name'] ?? '-'); ?></strong></p>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted">Status</small>
                    <p class="mb-0">
                        <span class="badge bg-success">
                            <?php echo htmlspecialchars($user['employment_status'] ?? 'Active'); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Profile Details -->
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card fade-in mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> Informasi Pribadi</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-edit me-2"></i> Edit
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Nama Lengkap</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Email</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($user['email'] ?? '-'); ?></strong></p>
                    </div>
                </div>
                
                <?php if ($user['is_employee']): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Nomor Telepon</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Tanggal Lahir</small>
                        <p class="mb-0"><strong><?php echo formatDate($user['date_of_birth'] ?? ''); ?></strong></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 mb-3">
                        <small class="text-muted">Alamat</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($user['address'] ?? '-'); ?></strong></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Tanggal Bergabung</small>
                        <p class="mb-0"><strong><?php echo formatDate($user['hire_date'] ?? ''); ?></strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Gaji Pokok</small>
                        <p class="mb-0"><strong><?php echo formatCurrency($user['basic_salary'] ?? 0); ?></strong></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Account Security -->
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i> Keamanan Akun</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Username</small>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Password</small>
                        <p class="mb-0">
                            <strong>••••••••</strong>
                            <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                Ubah Password
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/hrm/admin/profile/update.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Depan</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Belakang</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                    
                    <?php if ($user['is_employee']): ?>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/hrm/admin/profile/change_password.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i> Ubah Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Photo Modal -->
<div class="modal fade" id="editPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/hrm/admin/profile/update_photo.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-camera me-2"></i> Ubah Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <?php if (!empty($user['photo'])): ?>
                            <img src="/hrm/assets/uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                 alt="Current Photo" class="rounded-circle mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Foto Baru</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
