<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$slip_id = $_GET['id'] ?? 0;

if (empty($slip_id)) {
    echo '<div class="alert alert-danger">ID slip tidak valid</div>';
    exit;
}

try {
    // Get slip details
    $slipStmt = $pdo->prepare("SELECT ps.*, 
                               CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                               e.employee_code,
                               d.department_name,
                               p.position_name,
                               pp.period_name,
                               pp.payment_date
                               FROM payroll_slips ps
                               LEFT JOIN employees e ON ps.employee_id = e.id
                               LEFT JOIN departments d ON e.department_id = d.id
                               LEFT JOIN positions p ON e.position_id = p.id
                               LEFT JOIN payroll_periods pp ON ps.period_id = pp.id
                               WHERE ps.id = ?");
    $slipStmt->execute([$slip_id]);
    $slip = $slipStmt->fetch();
    
    if (!$slip) {
        echo '<div class="alert alert-danger">Slip gaji tidak ditemukan</div>';
        exit;
    }
    
    // Get slip details (components)
    $detailsStmt = $pdo->prepare("SELECT * FROM payroll_slip_details WHERE slip_id = ? ORDER BY component_type, component_name");
    $detailsStmt->execute([$slip_id]);
    $details = $detailsStmt->fetchAll();
    
    // Separate earnings and deductions
    $earnings = array_filter($details, fn($d) => $d['component_type'] == 'Earning');
    $deductions = array_filter($details, fn($d) => $d['component_type'] == 'Deduction');
    
} catch (PDOException $e) {
    error_log("Error fetching slip details: " . $e->getMessage());
    echo '<div class="alert alert-danger">Gagal memuat detail slip gaji</div>';
    exit;
}
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold">Informasi Karyawan</h6>
        <table class="table table-sm">
            <tr>
                <td width="40%">Nama</td>
                <td><strong><?php echo htmlspecialchars($slip['employee_name']); ?></strong></td>
            </tr>
            <tr>
                <td>Kode Karyawan</td>
                <td><?php echo htmlspecialchars($slip['employee_code']); ?></td>
            </tr>
            <tr>
                <td>Departemen</td>
                <td><?php echo htmlspecialchars($slip['department_name'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td><?php echo htmlspecialchars($slip['position_name'] ?? '-'); ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold">Informasi Periode</h6>
        <table class="table table-sm">
            <tr>
                <td width="40%">Periode</td>
                <td><strong><?php echo htmlspecialchars($slip['period_name']); ?></strong></td>
            </tr>
            <tr>
                <td>Tanggal Bayar</td>
                <td><?php echo formatDate($slip['payment_date'], 'd M Y'); ?></td>
            </tr>
            <tr>
                <td>Kehadiran</td>
                <td><?php echo $slip['attendance_days']; ?> dari <?php echo $slip['working_days']; ?> hari</td>
            </tr>
            <tr>
                <td>Keterlambatan</td>
                <td><?php echo $slip['late_count']; ?>x</td>
            </tr>
        </table>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold text-success"><i class="fas fa-plus-circle me-2"></i>Pendapatan</h6>
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Komponen</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Gaji Pokok</strong></td>
                    <td class="text-end"><strong><?php echo formatCurrency($slip['basic_salary']); ?></strong></td>
                </tr>
                <?php foreach ($earnings as $earning): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($earning['component_name']); ?></td>
                        <td class="text-end"><?php echo formatCurrency($earning['amount']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-success">
                    <td><strong>Total Pendapatan</strong></td>
                    <td class="text-end"><strong><?php echo formatCurrency($slip['total_earnings']); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="fw-bold text-danger"><i class="fas fa-minus-circle me-2"></i>Potongan</h6>
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Komponen</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($deductions) > 0): ?>
                    <?php foreach ($deductions as $deduction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($deduction['component_name']); ?></td>
                            <td class="text-end"><?php echo formatCurrency($deduction['amount']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted">Tidak ada potongan</td>
                    </tr>
                <?php endif; ?>
                <tr class="table-danger">
                    <td><strong>Total Potongan</strong></td>
                    <td class="text-end"><strong><?php echo formatCurrency($slip['total_deductions']); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Gaji Bersih (Take Home Pay)</h5>
                </div>
                <div>
                    <h3 class="mb-0 text-primary"><?php echo formatCurrency($slip['net_salary']); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($slip['notes']): ?>
    <div class="row">
        <div class="col-12">
            <h6 class="fw-bold">Catatan</h6>
            <p><?php echo nl2br(htmlspecialchars($slip['notes'])); ?></p>
        </div>
    </div>
<?php endif; ?>
