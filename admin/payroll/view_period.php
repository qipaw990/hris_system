<?php
$page_title = 'Detail Periode Penggajian';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$period_id = $_GET['id'] ?? 0;

if (empty($period_id)) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'ID periode tidak valid');
}

try {
    // Get period details
    $periodStmt = $pdo->prepare("SELECT * FROM payroll_periods WHERE id = ?");
    $periodStmt->execute([$period_id]);
    $period = $periodStmt->fetch();
    
    if (!$period) {
        redirect('/hrm/admin/payroll/index.php', 'error', 'Periode tidak ditemukan');
    }
    
    // Get all payroll slips for this period
    $slipsStmt = $pdo->prepare("SELECT ps.*, 
                                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                                e.employee_code,
                                d.department_name,
                                p.position_name
                                FROM payroll_slips ps
                                LEFT JOIN employees e ON ps.employee_id = e.id
                                LEFT JOIN departments d ON e.department_id = d.id
                                LEFT JOIN positions p ON e.position_id = p.id
                                WHERE ps.period_id = ?
                                ORDER BY e.first_name");
    $slipsStmt->execute([$period_id]);
    $slips = $slipsStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching period details: " . $e->getMessage());
    redirect('/hrm/admin/payroll/index.php', 'error', 'Gagal mengambil data periode');
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-file-invoice-dollar me-2"></i> <?php echo htmlspecialchars($period['period_name']); ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/payroll/index.php">Penggajian</a></li>
                    <li class="breadcrumb-item active">Detail Periode</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <?php if ($period['status'] == 'Processed'): ?>
                <button onclick="markAsPaid(<?php echo $period_id; ?>)" class="btn btn-success btn-sm">
                    <i class="fas fa-check me-2"></i> Tandai Sudah Dibayar
                </button>
            <?php endif; ?>
            <a href="/hrm/admin/payroll/index.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Period Summary -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Karyawan</p>
                        <h3 class="mb-0"><?php echo $period['total_employees']; ?></h3>
                        <small class="text-muted">
                            <i class="fas fa-users"></i> Karyawan
                        </small>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-users"></i>
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
                        <p class="text-muted mb-1">Total Gross</p>
                        <h4 class="mb-0"><?php echo formatCurrency($period['total_gross']); ?></h4>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> Pendapatan
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
                        <p class="text-muted mb-1">Total Potongan</p>
                        <h4 class="mb-0"><?php echo formatCurrency($period['total_deductions']); ?></h4>
                        <small class="text-danger">
                            <i class="fas fa-arrow-down"></i> Potongan
                        </small>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-minus-circle"></i>
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
                        <p class="text-muted mb-1">Total Net</p>
                        <h4 class="mb-0"><?php echo formatCurrency($period['total_net']); ?></h4>
                        <small class="text-info">
                            <i class="fas fa-wallet"></i> Dibayarkan
                        </small>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Periode</p>
                        <p class="fw-bold"><?php echo formatDate($period['start_date'], 'd M Y'); ?> - <?php echo formatDate($period['end_date'], 'd M Y'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Tanggal Pembayaran</p>
                        <p class="fw-bold"><?php echo formatDate($period['payment_date'], 'd M Y'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Status</p>
                        <p>
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
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Dibuat</p>
                        <p class="fw-bold"><?php echo formatDate($period['created_at'], 'd M Y H:i'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payroll Slips Table -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Daftar Slip Gaji
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="slipsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Gaji Pokok</th>
                                <th>Total Pendapatan</th>
                                <th>Total Potongan</th>
                                <th>Gaji Bersih</th>
                                <th>Kehadiran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slips as $slip): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($slip['employee_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($slip['employee_code']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($slip['department_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($slip['position_name'] ?? '-'); ?></td>
                                    <td><?php echo formatCurrency($slip['basic_salary']); ?></td>
                                    <td class="text-success fw-bold"><?php echo formatCurrency($slip['total_earnings']); ?></td>
                                    <td class="text-danger"><?php echo formatCurrency($slip['total_deductions']); ?></td>
                                    <td class="fw-bold"><?php echo formatCurrency($slip['net_salary']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $slip['attendance_days']; ?>/<?php echo $slip['working_days']; ?> hari
                                        </span>
                                        <?php if ($slip['late_count'] > 0): ?>
                                            <br><small class="text-warning">
                                                <i class="fas fa-clock"></i> <?php echo $slip['late_count']; ?>x terlambat
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick="viewSlipDetail(<?php echo $slip['id']; ?>)" 
                                                class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="/hrm/admin/payroll/print_slip.php?id=<?php echo $slip['id']; ?>" 
                                           target="_blank" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Cetak Slip">
                                            <i class="fas fa-print"></i>
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

<!-- Slip Detail Modal -->
<div class="modal fade" id="slipDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> Detail Slip Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="slipDetailContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable('#slipsTable', {
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari slip gaji..."
        }
    });
});

function viewSlipDetail(slipId) {
    $('#slipDetailModal').modal('show');
    
    // Load slip details via AJAX
    $.ajax({
        url: '/hrm/admin/payroll/get_slip_details.php',
        method: 'GET',
        data: { id: slipId },
        success: function(response) {
            $('#slipDetailContent').html(response);
        },
        error: function() {
            $('#slipDetailContent').html('<div class="alert alert-danger">Gagal memuat detail slip gaji</div>');
        }
    });
}

function markAsPaid(periodId) {
    Swal.fire({
        title: 'Tandai Sudah Dibayar?',
        text: 'Periode penggajian akan ditandai sebagai sudah dibayar',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tandai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/hrm/admin/payroll/mark_paid.php?id=' + periodId;
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
