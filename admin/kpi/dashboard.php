<?php
$page_title = 'KPI Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

try {
    // Get overall KPI statistics
    $statsStmt = $pdo->query("SELECT 
        COUNT(DISTINCT kc.id) as total_categories,
        COUNT(DISTINCT ki.id) as total_indicators,
        COUNT(DISTINCT eka.id) as total_assignments,
        COUNT(DISTINCT ke.id) as total_evaluations,
        COUNT(DISTINCT CASE WHEN ke.status = 'Approved' THEN ke.id END) as approved_evaluations,
        AVG(ke.score) as avg_score
        FROM kpi_categories kc
        LEFT JOIN kpi_indicators ki ON kc.id = ki.category_id
        LEFT JOIN employee_kpi_assignments eka ON ki.id = eka.indicator_id
        LEFT JOIN kpi_evaluations ke ON eka.id = ke.assignment_id");
    $stats = $statsStmt->fetch();
    
    // Get KPI by category
    $categoryStmt = $pdo->query("SELECT 
        kc.category_name,
        kc.weight,
        COUNT(DISTINCT ki.id) as indicator_count,
        AVG(ke.score) as avg_score,
        COUNT(DISTINCT ke.id) as evaluation_count
        FROM kpi_categories kc
        LEFT JOIN kpi_indicators ki ON kc.id = ki.category_id
        LEFT JOIN kpi_evaluations ke ON ki.id = ke.indicator_id
        WHERE kc.is_active = 1
        GROUP BY kc.id
        ORDER BY kc.weight DESC");
    $byCategory = $categoryStmt->fetchAll();
    
    // Get top performers
    $topPerformersStmt = $pdo->query("SELECT 
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        e.employee_code,
        d.department_name,
        AVG(ke.score) as avg_score,
        COUNT(DISTINCT ke.id) as evaluation_count
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN kpi_evaluations ke ON e.id = ke.employee_id
        WHERE ke.status = 'Approved'
        GROUP BY e.id
        HAVING evaluation_count > 0
        ORDER BY avg_score DESC
        LIMIT 10");
    $topPerformers = $topPerformersStmt->fetchAll();
    
    // Get recent evaluations
    $recentStmt = $pdo->query("SELECT 
        ke.*,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        ki.indicator_name,
        kc.category_name
        FROM kpi_evaluations ke
        LEFT JOIN employees e ON ke.employee_id = e.id
        LEFT JOIN kpi_indicators ki ON ke.indicator_id = ki.id
        LEFT JOIN kpi_categories kc ON ki.category_id = kc.id
        ORDER BY ke.updated_at DESC
        LIMIT 10");
    $recentEvaluations = $recentStmt->fetchAll();
    
    // Get pending evaluations
    $pendingStmt = $pdo->query("SELECT 
        ke.*,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        ki.indicator_name,
        kc.category_name
        FROM kpi_evaluations ke
        LEFT JOIN employees e ON ke.employee_id = e.id
        LEFT JOIN kpi_indicators ki ON ke.indicator_id = ki.id
        LEFT JOIN kpi_categories kc ON ki.category_id = kc.id
        WHERE ke.status IN ('Draft', 'Self-Assessed', 'Manager-Reviewed')
        ORDER BY ke.created_at ASC
        LIMIT 10");
    $pendingEvaluations = $pendingStmt->fetchAll();
    
    // Get score distribution
    $distributionStmt = $pdo->query("SELECT 
        CASE 
            WHEN score >= 90 THEN 'Excellent (90-100)'
            WHEN score >= 75 THEN 'Good (75-89)'
            WHEN score >= 60 THEN 'Average (60-74)'
            WHEN score >= 40 THEN 'Below Average (40-59)'
            ELSE 'Poor (0-39)'
        END as score_range,
        COUNT(*) as count
        FROM kpi_evaluations
        WHERE status = 'Approved'
        GROUP BY score_range
        ORDER BY MIN(score) DESC");
    $scoreDistribution = $distributionStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching KPI dashboard data: " . $e->getMessage());
    $stats = [];
    $byCategory = [];
    $topPerformers = [];
    $recentEvaluations = [];
    $pendingEvaluations = [];
    $scoreDistribution = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-tachometer-alt me-2"></i> KPI Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">KPI Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/kpi/categories/" class="btn btn-primary btn-sm">
                <i class="fas fa-cog me-2"></i> Kelola KPI
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Kategori</p>
                        <h3 class="mb-0"><?php echo $stats['total_categories'] ?? 0; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-layer-group"></i> KPI Categories
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Indikator</p>
                        <h3 class="mb-0"><?php echo $stats['total_indicators'] ?? 0; ?></h3>
                        <small class="text-info">
                            <i class="fas fa-chart-line"></i> KPI Indicators
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Evaluasi</p>
                        <h3 class="mb-0"><?php echo $stats['total_evaluations'] ?? 0; ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> <?php echo $stats['approved_evaluations'] ?? 0; ?> Approved
                        </small>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Rata-rata Skor</p>
                        <h3 class="mb-0"><?php echo round($stats['avg_score'] ?? 0, 1); ?></h3>
                        <small class="text-warning">
                            <i class="fas fa-star"></i> Overall Score
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Category Performance -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i> Performa per Kategori
                </h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Score Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Distribusi Skor
                </h5>
            </div>
            <div class="card-body">
                <canvas id="distributionChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers & Category Details -->
<div class="row mb-4">
    <!-- Top Performers -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i> Top Performers
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; foreach ($topPerformers as $performer): ?>
                                <tr>
                                    <td>
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-medal"></i> <?php echo $rank; ?>
                                            </span>
                                        <?php else: ?>
                                            <?php echo $rank; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($performer['employee_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($performer['employee_code']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($performer['department_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo round($performer['avg_score'], 1); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php $rank++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Details -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Detail Kategori KPI
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($byCategory as $cat): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <div>
                                <strong><?php echo htmlspecialchars($cat['category_name']); ?></strong>
                                <small class="text-muted">(Bobot: <?php echo $cat['weight']; ?>%)</small>
                            </div>
                            <span class="badge bg-info"><?php echo $cat['indicator_count']; ?> Indikator</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <?php 
                            $score = $cat['avg_score'] ?? 0;
                            $scoreClass = '';
                            if ($score >= 90) $scoreClass = 'bg-success';
                            elseif ($score >= 75) $scoreClass = 'bg-info';
                            elseif ($score >= 60) $scoreClass = 'bg-warning';
                            else $scoreClass = 'bg-danger';
                            ?>
                            <div class="progress-bar <?php echo $scoreClass; ?>" style="width: <?php echo $score; ?>%">
                                <?php echo round($score, 1); ?>%
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent & Pending Evaluations -->
<div class="row mb-4">
    <!-- Recent Evaluations -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i> Evaluasi Terbaru
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Indikator</th>
                                <th>Skor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvaluations as $eval): ?>
                                <tr>
                                    <td>
                                        <small><?php echo htmlspecialchars($eval['employee_name']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($eval['indicator_name']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo round($eval['score'], 1); ?></span>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Evaluations -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i> Evaluasi Pending
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Indikator</th>
                                <th>Periode</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingEvaluations as $eval): ?>
                                <tr>
                                    <td>
                                        <small><?php echo htmlspecialchars($eval['employee_name']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($eval['indicator_name']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo $eval['period']; ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($eval['status']) {
                                            case 'Draft': $statusClass = 'bg-secondary'; break;
                                            case 'Self-Assessed': $statusClass = 'bg-info'; break;
                                            case 'Manager-Reviewed': $statusClass = 'bg-warning'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $eval['status']; ?></span>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Category Performance Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($byCategory, 'category_name')); ?>,
            datasets: [{
                label: 'Rata-rata Skor',
                data: <?php echo json_encode(array_map(fn($c) => round($c['avg_score'] ?? 0, 1), $byCategory)); ?>,
                backgroundColor: 'rgba(44, 44, 44, 0.8)',
                borderColor: 'rgba(44, 44, 44, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    // Score Distribution Chart
    new Chart(document.getElementById('distributionChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($scoreDistribution, 'score_range')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($scoreDistribution, 'count')); ?>,
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(255, 133, 27, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
