<?php
$page_title = 'Manajemen User';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Admin only access
if ($_SESSION['role'] !== 'Admin') {
    redirect('/hrm/admin/index.php', 'error', 'Akses ditolak. Hanya Admin yang dapat mengakses halaman ini.');
}

// Get all users with employee info
try {
    $stmt = $pdo->query("SELECT u.*, 
                         CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                         e.employee_code
                         FROM users u
                         LEFT JOIN employees e ON u.employee_id = e.id
                         ORDER BY u.role ASC, u.username ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-users-cog me-2"></i> Manajemen User</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Manajemen User</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/users/add.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i> Tambah User
            </a>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Semua User
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Karyawan</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge bg-info ms-1">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php
                                        $roleClass = [
                                            'Admin' => 'bg-danger',
                                            'HR' => 'bg-warning',
                                            'Employee' => 'bg-secondary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $roleClass[$user['role']] ?? 'bg-secondary'; ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['employee_id']): ?>
                                            <a href="/hrm/admin/employees/view.php?id=<?php echo $user['employee_id']; ?>">
                                                <?php echo htmlspecialchars($user['employee_name']); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($user['employee_code']); ?>)</small>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] == 'Active'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/hrm/admin/users/edit.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Ubah">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button onclick="confirmDelete('/hrm/admin/users/delete.php?id=<?php echo $user['id']; ?>', 'Hapus User?', 'Ini akan menghapus permanen user <?php echo htmlspecialchars($user['username']); ?>')" 
                                                        class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        order: [[2, 'asc'], [0, 'asc']], // Sort by role then username
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
