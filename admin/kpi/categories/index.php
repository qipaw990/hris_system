<?php
$page_title = 'KPI Categories';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

try {
    // Get all KPI categories
    $categoriesStmt = $pdo->query("SELECT 
        kc.*,
        COUNT(DISTINCT ki.id) as indicator_count,
        SUM(CASE WHEN ki.is_active = 1 THEN 1 ELSE 0 END) as active_indicators
        FROM kpi_categories kc
        LEFT JOIN kpi_indicators ki ON kc.id = ki.category_id
        GROUP BY kc.id
        ORDER BY kc.weight DESC");
    $categories = $categoriesStmt->fetchAll();
    
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total_categories,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_categories,
        SUM(weight) as total_weight
        FROM kpi_categories");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching KPI categories: " . $e->getMessage());
    $categories = [];
    $stats = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-layer-group me-2"></i> KPI Categories</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/kpi/dashboard.php">KPI</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-2"></i> Tambah Kategori
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Kategori</p>
                        <h3 class="mb-0"><?php echo $stats['total_categories'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> <?php echo $stats['active_categories'] ?? 0; ?> Aktif
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Bobot</p>
                        <h3 class="mb-0"><?php echo round($stats['total_weight'] ?? 0, 1); ?>%</h3>
                        <small class="<?php echo ($stats['total_weight'] == 100) ? 'text-success' : 'text-warning'; ?>">
                            <i class="fas fa-balance-scale"></i> 
                            <?php echo ($stats['total_weight'] == 100) ? 'Balanced' : 'Not Balanced'; ?>
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Indikator</p>
                        <h3 class="mb-0"><?php echo array_sum(array_column($categories, 'indicator_count')); ?></h3>
                        <small class="text-info">
                            <i class="fas fa-chart-line"></i> Across All Categories
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categories Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Kategori KPI
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categoriesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Bobot (%)</th>
                                <th>Indikator</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(substr($cat['description'] ?? '-', 0, 50)); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $cat['weight']; ?>%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $cat['indicator_count']; ?> Total
                                        </span>
                                        <span class="badge bg-success">
                                            <?php echo $cat['active_indicators']; ?> Aktif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $cat['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $cat['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick='editCategory(<?php echo json_encode($cat); ?>)' 
                                                class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="/hrm/admin/kpi/indicators/?category=<?php echo $cat['id']; ?>" 
                                           class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat Indikator">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/hrm/admin/kpi/categories/process_add.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Tambah Kategori KPI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                        <input type="number" name="weight" class="form-control" step="0.01" min="0" max="100" required>
                        <small class="text-muted">Total bobot semua kategori harus 100%</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/hrm/admin/kpi/categories/process_edit.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Kategori KPI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="edit_category_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                        <input type="number" name="weight" id="edit_weight" class="form-control" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable('#categoriesTable', {
        order: [[2, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari kategori..."
        }
    });
});

function editCategory(category) {
    $('#edit_id').val(category.id);
    $('#edit_category_name').val(category.category_name);
    $('#edit_description').val(category.description);
    $('#edit_weight').val(category.weight);
    $('#edit_is_active').prop('checked', category.is_active == 1);
    $('#editCategoryModal').modal('show');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
