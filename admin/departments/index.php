<?php
$page_title = 'Daftar Departemen';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get all departments with employee count
try {
    $stmt = $pdo->query("SELECT d.*, 
                         (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.id) as employee_count
                         FROM departments d
                         ORDER BY d.department_name ASC");
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching departments: " . $e->getMessage());
    $departments = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-building me-2"></i> Manajemen Departemen</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Departemen</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class="fas fa-plus me-2"></i> Tambah Departemen
            </button>
        </div>
    </div>
</div>

<!-- Departments Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Departemen
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="departmentsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Departemen</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Karyawan</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($dept['department_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($dept['description'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $dept['employee_count']; ?> Karyawan
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($dept['created_at'], 'd M Y'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="editDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['department_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($dept['description'] ?? '', ENT_QUOTES); ?>')" 
                                                    class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmDelete('/hrm/admin/departments/delete.php?id=<?php echo $dept['id']; ?>', 'Hapus Departemen?', 'Departemen <?php echo htmlspecialchars($dept['department_name']); ?> akan dihapus. Karyawan di departemen ini akan menjadi tanpa departemen.')" 
                                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus"
                                                    <?php echo ($dept['employee_count'] > 0) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Tambah Departemen Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/departments/process_add.php" method="POST" id="addDepartmentForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="department_name" class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="department_name" name="department_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Departemen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/departments/process_edit.php" method="POST" id="editDepartmentForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_department_name" class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_department_name" name="department_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable('#departmentsTable', {
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [4] }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari departemen..."
        }
    });
});

// Edit department function
function editDepartment(id, name, description) {
    $('#edit_id').val(id);
    $('#edit_department_name').val(name);
    $('#edit_description').val(description);
    $('#editDepartmentModal').modal('show');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
