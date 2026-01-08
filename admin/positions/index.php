<?php
$page_title = 'Daftar Jabatan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get all positions with employee count
try {
    $stmt = $pdo->query("SELECT p.*, 
                         (SELECT COUNT(*) FROM employees e WHERE e.position_id = p.id) as employee_count
                         FROM positions p
                         ORDER BY p.position_name ASC");
    $positions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching positions: " . $e->getMessage());
    $positions = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-briefcase me-2"></i> Manajemen Jabatan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Jabatan</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                <i class="fas fa-plus me-2"></i> Tambah Jabatan
            </button>
        </div>
    </div>
</div>

<!-- Positions Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Jabatan
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="positionsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Jabatan</th>
                                <th>Deskripsi</th>
                                <th>Level</th>
                                <th>Jumlah Karyawan</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($positions as $pos): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pos['position_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($pos['description'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($pos['level']): ?>
                                            <span class="badge bg-info">Level <?php echo $pos['level']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $pos['employee_count']; ?> Karyawan
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($pos['created_at'], 'd M Y'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="editPosition(<?php echo $pos['id']; ?>, '<?php echo htmlspecialchars($pos['position_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($pos['description'] ?? '', ENT_QUOTES); ?>', '<?php echo $pos['level'] ?? ''; ?>')" 
                                                    class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmDelete('/hrm/admin/positions/delete.php?id=<?php echo $pos['id']; ?>', 'Hapus Jabatan?', 'Jabatan <?php echo htmlspecialchars($pos['position_name']); ?> akan dihapus. Karyawan dengan jabatan ini akan menjadi tanpa jabatan.')" 
                                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus"
                                                    <?php echo ($pos['employee_count'] > 0) ? 'disabled' : ''; ?>>
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

<!-- Add Position Modal -->
<div class="modal fade" id="addPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Tambah Jabatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/positions/process_add.php" method="POST" id="addPositionForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="position_name" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="position_name" name="position_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <select class="form-select" id="level" name="level">
                            <option value="">Pilih Level</option>
                            <option value="1">Level 1 - Entry</option>
                            <option value="2">Level 2 - Junior</option>
                            <option value="3">Level 3 - Senior</option>
                            <option value="4">Level 4 - Lead</option>
                            <option value="5">Level 5 - Manager</option>
                            <option value="6">Level 6 - Director</option>
                            <option value="7">Level 7 - Executive</option>
                        </select>
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

<!-- Edit Position Modal -->
<div class="modal fade" id="editPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/positions/process_edit.php" method="POST" id="editPositionForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_position_name" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_position_name" name="position_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_level" class="form-label">Level</label>
                        <select class="form-select" id="edit_level" name="level">
                            <option value="">Pilih Level</option>
                            <option value="1">Level 1 - Entry</option>
                            <option value="2">Level 2 - Junior</option>
                            <option value="3">Level 3 - Senior</option>
                            <option value="4">Level 4 - Lead</option>
                            <option value="5">Level 5 - Manager</option>
                            <option value="6">Level 6 - Director</option>
                            <option value="7">Level 7 - Executive</option>
                        </select>
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
    initDataTable('#positionsTable', {
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari jabatan..."
        }
    });
});

// Edit position function
function editPosition(id, name, description, level) {
    $('#edit_id').val(id);
    $('#edit_position_name').val(name);
    $('#edit_description').val(description);
    $('#edit_level').val(level);
    $('#editPositionModal').modal('show');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
