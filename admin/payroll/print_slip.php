<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$slip_id = $_GET['id'] ?? 0;

if (empty($slip_id)) {
    die('ID slip tidak valid');
}

try {
    // Get slip details
    $slipStmt = $pdo->prepare("SELECT ps.*, 
                               CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                               e.employee_code,
                               e.address,
                               d.department_name,
                               p.position_name,
                               pp.period_name,
                               pp.payment_date,
                               pp.start_date,
                               pp.end_date
                               FROM payroll_slips ps
                               LEFT JOIN employees e ON ps.employee_id = e.id
                               LEFT JOIN departments d ON e.department_id = d.id
                               LEFT JOIN positions p ON e.position_id = p.id
                               LEFT JOIN payroll_periods pp ON ps.period_id = pp.id
                               WHERE ps.id = ?");
    $slipStmt->execute([$slip_id]);
    $slip = $slipStmt->fetch();
    
    if (!$slip) {
        die('Slip gaji tidak ditemukan');
    }
    
    // Get slip details (components)
    $detailsStmt = $pdo->prepare("SELECT * FROM payroll_slip_details WHERE slip_id = ? ORDER BY component_type, component_name");
    $detailsStmt->execute([$slip_id]);
    $details = $detailsStmt->fetchAll();
    
    // Separate earnings and deductions
    $earnings = array_filter($details, fn($d) => $d['component_type'] == 'Earning');
    $deductions = array_filter($details, fn($d) => $d['component_type'] == 'Deduction');
    
} catch (PDOException $e) {
    error_log("Error fetching slip for print: " . $e->getMessage());
    die('Gagal memuat slip gaji');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - <?php echo htmlspecialchars($slip['employee_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .slip-header {
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .slip-footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Tutup
            </button>
        </div>
        
        <div class="slip-header">
            <div class="row">
                <div class="col-8">
                    <h3 class="mb-0">SLIP GAJI KARYAWAN</h3>
                    <p class="mb-0">HRIS Management System</p>
                </div>
                <div class="col-4 text-end">
                    <p class="mb-0"><strong>Periode:</strong> <?php echo htmlspecialchars($slip['period_name']); ?></p>
                    <p class="mb-0"><strong>Tanggal Bayar:</strong> <?php echo formatDate($slip['payment_date'], 'd M Y'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%"><strong>Nama</strong></td>
                        <td>: <?php echo htmlspecialchars($slip['employee_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Kode Karyawan</strong></td>
                        <td>: <?php echo htmlspecialchars($slip['employee_code']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Departemen</strong></td>
                        <td>: <?php echo htmlspecialchars($slip['department_name'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Jabatan</strong></td>
                        <td>: <?php echo htmlspecialchars($slip['position_name'] ?? '-'); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%"><strong>Periode</strong></td>
                        <td>: <?php echo formatDate($slip['start_date'], 'd M'); ?> - <?php echo formatDate($slip['end_date'], 'd M Y'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Hari Kerja</strong></td>
                        <td>: <?php echo $slip['working_days']; ?> hari</td>
                    </tr>
                    <tr>
                        <td><strong>Kehadiran</strong></td>
                        <td>: <?php echo $slip['attendance_days']; ?> hari</td>
                    </tr>
                    <tr>
                        <td><strong>Keterlambatan</strong></td>
                        <td>: <?php echo $slip['late_count']; ?>x</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-6">
                <h6 class="fw-bold">PENDAPATAN</h6>
                <table class="table table-sm table-bordered">
                    <tbody>
                        <tr>
                            <td>Gaji Pokok</td>
                            <td class="text-end"><?php echo formatCurrency($slip['basic_salary']); ?></td>
                        </tr>
                        <?php foreach ($earnings as $earning): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($earning['component_name']); ?></td>
                                <td class="text-end"><?php echo formatCurrency($earning['amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="fw-bold">
                            <td>TOTAL PENDAPATAN</td>
                            <td class="text-end"><?php echo formatCurrency($slip['total_earnings']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="col-6">
                <h6 class="fw-bold">POTONGAN</h6>
                <table class="table table-sm table-bordered">
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
                        <tr class="fw-bold">
                            <td>TOTAL POTONGAN</td>
                            <td class="text-end"><?php echo formatCurrency($slip['total_deductions']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-dark">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mb-0">GAJI BERSIH (TAKE HOME PAY)</h5>
                        </div>
                        <div class="col-6 text-end">
                            <h4 class="mb-0"><?php echo formatCurrency($slip['net_salary']); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="slip-footer">
            <div class="row">
                <div class="col-6">
                    <p class="mb-0 text-muted"><small>Dicetak pada: <?php echo date('d M Y H:i'); ?></small></p>
                </div>
                <div class="col-6 text-end">
                    <p class="mb-5">Menyetujui,</p>
                    <p class="mb-0">_____________________</p>
                    <p class="mb-0"><small>HRD Manager</small></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto print on load
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
