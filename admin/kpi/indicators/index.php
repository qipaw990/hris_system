<?php
$page_title = 'KPI Indicators';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$category_filter = $_GET['category'] ?? '';

try {
    // Get all categories for filter
    $categoriesStmt = $pdo->query("SELECT * FROM kpi_categories WHERE is_active = 1 ORDER BY category_name");
    $categories = $categoriesStmt->fetchAll();
    
    // Build query with filter
    $where = "1=1";
    $params = [];
    if ($category_filter) {
        $where .= " AND ki.category_id = ?";
        $params[] = $category_filter;
    }
    
    // Get all KPI indicators
    $indicatorsStmt = $pdo->prepare("SELECT 
        ki.*,
        kc.category_name,
        kc.weight as category_weight,
        COUNT(DISTINCT eka.id) as assignment_count
        FROM kpi_indicators ki
        LEFT JOIN kpi_categories kc ON ki.category_id = kc.id
        LEFT JOIN employee_kpi_assignments eka ON ki.id = eka.indicator_id
        WHERE $where
        GROUP BY ki.id
        ORDER BY kc.category_name, ki.indicator_name");
    $indicatorsStmt->execute($params);
    $indicators = $indicatorsStmt->fetchAll();
    
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total_indicators,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_indicators,
        COUNT(DISTINCT category_id) as categories_used
        FROM kpi_indicators");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching KPI indicators: " . $e->getMessage());
    $indicators = [];
    $categories = [];
    $stats = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-chart-line me-2"></i> KPI Indicators</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/kpi/dashboard.php">KPI</a></li>
                    <li class="breadcrumb-item active">Indicators</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addIndicatorModal">
                <i class="fas fa-plus me-2"></i> Tambah Indikator
            </button>
        </div>
    </div>
</div>

<!-- Statistics & Filter -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Indikator</p>
                        <h3 class="mb-0"><?php echo $stats['total_indicators'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> <?php echo $stats['active_indicators'] ?? 0; ?> Aktif
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-9 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Filter Kategori</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                            <a href="/hrm/admin/kpi/indicators/" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Indicators Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Indikator KPI
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="indicatorsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Nama Indikator</th>
                                <th>Tipe</th>
                                <th>Target</th>
                                <th>Bobot (%)</th>
                                <th>Assignments</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indicators as $ind): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($ind['category_name']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ind['indicator_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($ind['description'] ?? '', 0, 50)); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $ind['measurement_type']; ?></span>
                                    </td>
                                    <td><?php echo $ind['target_value']; ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $ind['weight']; ?>%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $ind['assignment_count']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $ind['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $ind['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick='editIndicator(<?php echo json_encode($ind); ?>)' 
                                                class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
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

<!-- Add Indicator Modal -->
<div class="modal fade" id="addIndicatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/hrm/admin/kpi/indicators/process_add.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Tambah Indikator KPI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe Pengukuran <span class="text-danger">*</span></label>
                            <select name="measurement_type" class="form-select" required>
                                <option value="Numeric">Numeric</option>
                                <option value="Percentage">Percentage</option>
                                <option value="Rating">Rating (1-5)</option>
                                <option value="Boolean">Boolean (Yes/No)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="indicator_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Value <span class="text-danger">*</span></label>
                            <input type="number" name="target_value" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bobot dalam Kategori (%) <span class="text-danger">*</span></label>
                            <input type="number" name="weight" class="form-control" step="0.01" min="0" max="100" required>
                        </div>
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

<!-- Edit Indicator Modal -->
<div class="modal fade" id="editIndicatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/hrm/admin/kpi/indicators/process_edit.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Indikator KPI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" id="edit_category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe Pengukuran <span class="text-danger">*</span></label>
                            <select name="measurement_type" id="edit_measurement_type" class="form-select" required>
                                <option value="Numeric">Numeric</option>
                                <option value="Percentage">Percentage</option>
                                <option value="Rating">Rating (1-5)</option>
                                <option value="Boolean">Boolean (Yes/No)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="indicator_name" id="edit_indicator_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Value <span class="text-danger">*</span></label>
                            <input type="number" name="target_value" id="edit_target_value" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bobot dalam Kategori (%) <span class="text-danger">*</span></label>
                            <input type="number" name="weight" id="edit_weight" class="form-control" step="0.01" min="0" max="100" required>
                        </div>
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
    initDataTable('#indicatorsTable', {
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari indikator..."
        }
    });
});

function editIndicator(indicator) {
    $('#edit_id').val(indicator.id);
    $('#edit_category_id').val(indicator.category_id);
    $('#edit_indicator_name').val(indicator.indicator_name);
    $('#edit_description').val(indicator.description);
    $('#edit_measurement_type').val(indicator.measurement_type);
    $('#edit_target_value').val(indicator.target_value);
    $('#edit_weight').val(indicator.weight);
    $('#edit_is_active').prop('checked', indicator.is_active == 1);
    $('#editIndicatorModal').modal('show');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
