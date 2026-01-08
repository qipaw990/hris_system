<?php
$page_title = 'Manajemen Penggajian';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

try {
    // Get payroll components
    $componentsStmt = $pdo->query("SELECT * FROM payroll_components ORDER BY component_type, component_name");
    $components = $componentsStmt->fetchAll();
    
    // Separate by type
    $earnings = array_filter($components, fn($c) => $c['component_type'] == 'Earning');
    $deductions = array_filter($components, fn($c) => $c['component_type'] == 'Deduction');
    
    // Get recent payroll periods
    $periodsStmt = $pdo->query("SELECT * FROM payroll_periods ORDER BY period_year DESC, period_month DESC LIMIT 6");
    $periods = $periodsStmt->fetchAll();
    
    // Get statistics
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total_periods,
        SUM(CASE WHEN status = 'Paid' THEN total_net ELSE 0 END) as total_paid,
        SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft_count,
        SUM(CASE WHEN status = 'Processed' THEN 1 ELSE 0 END) as processed_count
        FROM payroll_periods");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching payroll data: " . $e->getMessage());
    $components = [];
    $earnings = [];
    $deductions = [];
    $periods = [];
    $stats = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-money-bill-wave me-2"></i> Manajemen Penggajian</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Penggajian</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
                <i class="fas fa-calculator me-2"></i> Generate Gaji
            </button>
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
                        <p class="text-muted mb-1">Total Periode</p>
                        <h3 class="mb-0"><?php echo $stats['total_periods'] ?? 0; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> Periode Gaji
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-calendar-alt"></i>
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
                        <p class="text-muted mb-1">Total Dibayar</p>
                        <h3 class="mb-0"><?php echo formatCurrency($stats['total_paid'] ?? 0); ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> Terbayar
                        </small>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="fas fa-money-bill-wave"></i>
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
                        <p class="text-muted mb-1">Draft</p>
                        <h3 class="mb-0"><?php echo $stats['draft_count'] ?? 0; ?></h3>
                        <small class="text-warning">
                            <i class="fas fa-edit"></i> Belum Diproses
                        </small>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-file-alt"></i>
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
                        <p class="text-muted mb-1">Diproses</p>
                        <h3 class="mb-0"><?php echo $stats['processed_count'] ?? 0; ?></h3>
                        <small class="text-info">
                            <i class="fas fa-cog"></i> Siap Dibayar
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payroll Components Configuration -->
<div class="row mb-4">
    <!-- Earnings -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2 text-success"></i> Komponen Pendapatan
                </h5>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addComponentModal" onclick="setComponentType('Earning')">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nama Komponen</th>
                                <th>Tipe Kalkulasi</th>
                                <th>Nilai Default</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($earnings as $comp): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($comp['component_name']); ?></strong>
                                        <?php if ($comp['is_taxable']): ?>
                                            <br><small class="text-muted"><i class="fas fa-receipt"></i> Kena Pajak</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $calcBadge = '';
                                        switch ($comp['calculation_type']) {
                                            case 'Fixed': $calcBadge = 'bg-primary'; break;
                                            case 'Percentage': $calcBadge = 'bg-info'; break;
                                            case 'Formula': $calcBadge = 'bg-warning'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $calcBadge; ?>">
                                            <?php echo $comp['calculation_type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($comp['calculation_type'] == 'Percentage') {
                                            echo $comp['default_amount'] . '%';
                                        } else {
                                            echo formatCurrency($comp['default_amount']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($comp['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick="editComponent(<?php echo htmlspecialchars(json_encode($comp)); ?>)" 
                                                class="btn btn-sm btn-warning">
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
    
    <!-- Deductions -->
    <div class="col-lg-6 mb-4">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-minus-circle me-2 text-danger"></i> Komponen Potongan
                </h5>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#addComponentModal" onclick="setComponentType('Deduction')">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nama Komponen</th>
                                <th>Tipe Kalkulasi</th>
                                <th>Nilai Default</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deductions as $comp): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($comp['component_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $calcBadge = '';
                                        switch ($comp['calculation_type']) {
                                            case 'Fixed': $calcBadge = 'bg-primary'; break;
                                            case 'Percentage': $calcBadge = 'bg-info'; break;
                                            case 'Formula': $calcBadge = 'bg-warning'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $calcBadge; ?>">
                                            <?php echo $comp['calculation_type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($comp['calculation_type'] == 'Percentage') {
                                            echo $comp['default_amount'] . '%';
                                        } else {
                                            echo formatCurrency($comp['default_amount']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($comp['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick="editComponent(<?php echo htmlspecialchars(json_encode($comp)); ?>)" 
                                                class="btn btn-sm btn-warning">
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

<!-- Recent Payroll Periods -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i> Riwayat Periode Penggajian
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($periods) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Tanggal Pembayaran</th>
                                    <th>Jumlah Karyawan</th>
                                    <th>Total Gross</th>
                                    <th>Total Potongan</th>
                                    <th>Total Net</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($periods as $period): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($period['period_name']); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo formatDate($period['start_date'], 'd M'); ?> - 
                                                <?php echo formatDate($period['end_date'], 'd M Y'); ?>
                                            </small>
                                        </td>
                                        <td><?php echo formatDate($period['payment_date'], 'd M Y'); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $period['total_employees']; ?> Karyawan
                                            </span>
                                        </td>
                                        <td><?php echo formatCurrency($period['total_gross']); ?></td>
                                        <td><?php echo formatCurrency($period['total_deductions']); ?></td>
                                        <td><strong><?php echo formatCurrency($period['total_net']); ?></strong></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($period['status']) {
                                                case 'Draft': $statusClass = 'bg-secondary'; break;
                                                case 'Processed': $statusClass = 'bg-info'; break;
                                                case 'Paid': $statusClass = 'bg-success'; break;
                                                case 'Closed': $statusClass = 'bg-dark'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo $period['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/hrm/admin/payroll/view_period.php?id=<?php echo $period['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada periode penggajian. Klik "Generate Gaji" untuk membuat periode baru.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Tambah Komponen Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/payroll/process_add_component.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="component_type" id="component_type">
                    
                    <div class="mb-3">
                        <label for="component_name" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="component_name" name="component_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="calculation_type" class="form-label">Tipe Kalkulasi <span class="text-danger">*</span></label>
                        <select class="form-select" id="calculation_type" name="calculation_type" required>
                            <option value="Fixed">Fixed (Nilai Tetap)</option>
                            <option value="Percentage">Percentage (Persentase dari Gaji Pokok)</option>
                            <option value="Formula">Formula (Dihitung dengan Rumus)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="default_amount" class="form-label">Nilai Default</label>
                        <input type="number" class="form-control" id="default_amount" name="default_amount" step="0.01" value="0">
                        <small class="text-muted">Untuk Fixed: masukkan nominal. Untuk Percentage: masukkan angka persen (contoh: 10 untuk 10%)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_taxable" name="is_taxable" value="1" checked>
                            <label class="form-check-label" for="is_taxable">
                                Kena Pajak
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
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

<!-- Edit Component Modal -->
<div class="modal fade" id="editComponentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Komponen Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/payroll/process_edit_component.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_component_name" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_component_name" name="component_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_calculation_type" class="form-label">Tipe Kalkulasi <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_calculation_type" name="calculation_type" required>
                            <option value="Fixed">Fixed (Nilai Tetap)</option>
                            <option value="Percentage">Percentage (Persentase dari Gaji Pokok)</option>
                            <option value="Formula">Formula (Dihitung dengan Rumus)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_default_amount" class="form-label">Nilai Default</label>
                        <input type="number" class="form-control" id="edit_default_amount" name="default_amount" step="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_taxable" name="is_taxable" value="1">
                            <label class="form-check-label" for="edit_is_taxable">
                                Kena Pajak
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                Aktif
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
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

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calculator me-2"></i> Generate Penggajian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/hrm/admin/payroll/process_generate.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Generate gaji akan membuat slip gaji untuk semua karyawan aktif berdasarkan konfigurasi komponen gaji.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="period_month" class="form-label">Bulan <span class="text-danger">*</span></label>
                            <select class="form-select" id="period_month" name="period_month" required>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m == date('n')) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="period_year" class="form-label">Tahun <span class="text-danger">*</span></label>
                            <select class="form-select" id="period_year" name="period_year" required>
                                <?php for($y = 2020; $y <= 2030; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == date('Y')) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator me-2"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentComponentType = 'Earning';

function setComponentType(type) {
    currentComponentType = type;
    $('#component_type').val(type);
}

function editComponent(component) {
    $('#edit_id').val(component.id);
    $('#edit_component_name').val(component.component_name);
    $('#edit_calculation_type').val(component.calculation_type);
    $('#edit_default_amount').val(component.default_amount);
    $('#edit_is_taxable').prop('checked', component.is_taxable == 1);
    $('#edit_is_active').prop('checked', component.is_active == 1);
    $('#edit_description').val(component.description);
    $('#editComponentModal').modal('show');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
