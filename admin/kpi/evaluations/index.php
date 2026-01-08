<?php
$page_title = 'KPI Evaluations';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$period_filter = $_GET['period'] ?? date('Y-m');
$status_filter = $_GET['status'] ?? '';

try {
    // Build query with filters
    $where = ["1=1"];
    $params = [];
    
    if ($period_filter) {
        $where[] = "ke.period = ?";
        $params[] = $period_filter;
    }
    if ($status_filter) {
        $where[] = "ke.status = ?";
        $params[] = $status_filter;
    }
    
    $whereClause = implode(" AND ", $where);
    
    // Get all evaluations
    $evaluationsStmt = $pdo->prepare("SELECT 
        ke.*,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        e.employee_code,
        d.department_name,
        ki.indicator_name,
        ki.measurement_type,
        ki.target_value,
        kc.category_name
        FROM kpi_evaluations ke
        LEFT JOIN employees e ON ke.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN kpi_indicators ki ON ke.indicator_id = ki.id
        LEFT JOIN kpi_categories kc ON ki.category_id = kc.id
        WHERE $whereClause
        ORDER BY ke.updated_at DESC");
    $evaluationsStmt->execute($params);
    $evaluations = $evaluationsStmt->fetchAll();
    
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total_evaluations,
        SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft_count,
        SUM(CASE WHEN status = 'Self-Assessed' THEN 1 ELSE 0 END) as self_assessed_count,
        SUM(CASE WHEN status = 'Manager-Reviewed' THEN 1 ELSE 0 END) as manager_reviewed_count,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
        AVG(CASE WHEN status = 'Approved' THEN score ELSE NULL END) as avg_score
        FROM kpi_evaluations");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching KPI evaluations: " . $e->getMessage());
    $evaluations = [];
    $stats = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-clipboard-check me-2"></i> KPI Evaluations</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/kpi/dashboard.php">KPI</a></li>
                    <li class="breadcrumb-item active">Evaluations</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card fade-in">
            <div class="card-body text-center">
                <h4 class="mb-0"><?php echo $stats['total_evaluations'] ?? 0; ?></h4>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card fade-in border-secondary">
            <div class="card-body text-center">
                <h4 class="mb-0 text-secondary"><?php echo $stats['draft_count'] ?? 0; ?></h4>
                <small class="text-muted">Draft</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card fade-in border-info">
            <div class="card-body text-center">
                <h4 class="mb-0 text-info"><?php echo $stats['self_assessed_count'] ?? 0; ?></h4>
                <small class="text-muted">Self-Assessed</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card fade-in border-warning">
            <div class="card-body text-center">
                <h4 class="mb-0 text-warning"><?php echo $stats['manager_reviewed_count'] ?? 0; ?></h4>
                <small class="text-muted">Manager-Reviewed</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card fade-in border-success">
            <div class="card-body text-center">
                <h4 class="mb-0 text-success"><?php echo $stats['approved_count'] ?? 0; ?></h4>
                <small class="text-muted">Approved</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="card fade-in border-primary">
            <div class="card-body text-center">
                <h4 class="mb-0 text-primary"><?php echo round($stats['avg_score'] ?? 0, 1); ?></h4>
                <small class="text-muted">Avg Score</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Periode</label>
                        <input type="month" name="period" class="form-control form-control-sm" value="<?php echo $period_filter; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Draft" <?php echo ($status_filter == 'Draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="Self-Assessed" <?php echo ($status_filter == 'Self-Assessed') ? 'selected' : ''; ?>>Self-Assessed</option>
                            <option value="Manager-Reviewed" <?php echo ($status_filter == 'Manager-Reviewed') ? 'selected' : ''; ?>>Manager-Reviewed</option>
                            <option value="Approved" <?php echo ($status_filter == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                            <a href="/hrm/admin/kpi/evaluations/" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Evaluations Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Evaluasi KPI
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="evaluationsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Karyawan</th>
                                <th>Kategori</th>
                                <th>Indikator</th>
                                <th>Target</th>
                                <th>Actual</th>
                                <th>Skor</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluations as $eval): ?>
                                <tr>
                                    <td><?php echo $eval['period']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($eval['employee_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($eval['employee_code']); ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($eval['category_name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($eval['indicator_name']); ?></td>
                                    <td><?php echo $eval['target_value']; ?></td>
                                    <td><?php echo $eval['actual_value']; ?></td>
                                    <td>
                                        <?php
                                        $scoreClass = '';
                                        if ($eval['score'] >= 90) $scoreClass = 'bg-success';
                                        elseif ($eval['score'] >= 75) $scoreClass = 'bg-info';
                                        elseif ($eval['score'] >= 60) $scoreClass = 'bg-warning';
                                        else $scoreClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $scoreClass; ?>"><?php echo round($eval['score'], 1); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($eval['status']) {
                                            case 'Draft': $statusClass = 'bg-secondary'; break;
                                            case 'Self-Assessed': $statusClass = 'bg-info'; break;
                                            case 'Manager-Reviewed': $statusClass = 'bg-warning'; break;
                                            case 'Approved': $statusClass = 'bg-success'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $eval['status']; ?></span>
                                    </td>
                                    <td>
                                        <button onclick='viewEvaluation(<?php echo json_encode($eval); ?>)' 
                                                class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
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

<!-- View Evaluation Modal -->
<div class="modal fade" id="viewEvaluationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Detail Evaluasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="evaluationDetails">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    initDataTable('#evaluationsTable', {
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari evaluasi..."
        }
    });
});

function viewEvaluation(eval) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informasi Karyawan</h6>
                <table class="table table-sm">
                    <tr><td>Nama:</td><td><strong>${eval.employee_name}</strong></td></tr>
                    <tr><td>Kode:</td><td>${eval.employee_code}</td></tr>
                    <tr><td>Departemen:</td><td>${eval.department_name || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Informasi KPI</h6>
                <table class="table table-sm">
                    <tr><td>Kategori:</td><td><strong>${eval.category_name}</strong></td></tr>
                    <tr><td>Indikator:</td><td>${eval.indicator_name}</td></tr>
                    <tr><td>Periode:</td><td>${eval.period}</td></tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <div class="text-center p-3 bg-light rounded">
                    <small class="text-muted">Target</small>
                    <h4 class="mb-0">${eval.target_value}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-light rounded">
                    <small class="text-muted">Actual</small>
                    <h4 class="mb-0">${eval.actual_value}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-light rounded">
                    <small class="text-muted">Score</small>
                    <h4 class="mb-0">${Math.round(eval.score * 10) / 10}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-light rounded">
                    <small class="text-muted">Status</small>
                    <h6 class="mb-0"><span class="badge bg-success">${eval.status}</span></h6>
                </div>
            </div>
        </div>
        ${eval.self_assessment ? `
            <hr>
            <h6>Self Assessment</h6>
            <p class="text-muted">${eval.self_assessment}</p>
        ` : ''}
        ${eval.manager_assessment ? `
            <hr>
            <h6>Manager Assessment</h6>
            <p class="text-muted">${eval.manager_assessment}</p>
        ` : ''}
        ${eval.notes ? `
            <hr>
            <h6>Notes</h6>
            <p class="text-muted">${eval.notes}</p>
        ` : ''}
    `;
    
    $('#evaluationDetails').html(html);
    $('#viewEvaluationModal').modal('show');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
