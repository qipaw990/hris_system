<?php
$page_title = 'Shuffle Shift - Auto Assignment';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Get all active shifts
$shiftsStmt = $pdo->query("SELECT * FROM work_shifts WHERE is_active = 1 ORDER BY shift_code");
$shifts = $shiftsStmt->fetchAll();

if (empty($shifts)) {
    $_SESSION['error'] = 'Tidak ada shift aktif';
    header('Location: /hrm/admin/shifts/index.php');
    exit();
}

// Handle shuffle process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shuffle'])) {
    try {
        // Get all active employees
        $employeesStmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE employment_status = 'Active'");
        $employees = $employeesStmt->fetchAll();
        
        // Calculate shift history for each employee (last 30 days)
        $shiftHistory = [];
        foreach ($employees as $emp) {
            $historyStmt = $pdo->prepare("SELECT ws.id as shift_id, ws.shift_name, COUNT(*) as count
                                          FROM attendance a
                                          JOIN work_shifts ws ON a.shift_id = ws.id
                                          WHERE a.employee_id = ?
                                          AND a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                          GROUP BY ws.id");
            $historyStmt->execute([$emp['id']]);
            $history = $historyStmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
            
            $shiftHistory[$emp['id']] = [
                'name' => $emp['name'],
                'history' => $history
            ];
        }
        
        // Calculate fairness score for each employee-shift combination
        $assignments = [];
        foreach ($employees as $emp) {
            $scores = [];
            foreach ($shifts as $shift) {
                // Count how many times employee worked this shift
                $count = 0;
                if (isset($shiftHistory[$emp['id']]['history'][$shift['id']])) {
                    $count = $shiftHistory[$emp['id']]['history'][$shift['id']][0]['count'];
                }
                
                // Lower count = higher priority (more fair to assign)
                // Night shift gets bonus (harder shift)
                $score = $count;
                if ($shift['is_night_shift']) {
                    $score -= 2; // Prioritize night shift assignment
                }
                
                $scores[$shift['id']] = $score;
            }
            
            // Assign to shift with lowest score (most fair)
            asort($scores);
            $assignedShiftId = key($scores);
            
            $assignments[] = [
                'employee_id' => $emp['id'],
                'shift_id' => $assignedShiftId,
                'score' => $scores[$assignedShiftId]
            ];
        }
        
        // Balance assignments (ensure each shift has similar number of employees)
        $shiftCounts = array_fill_keys(array_column($shifts, 'id'), 0);
        foreach ($assignments as $assignment) {
            $shiftCounts[$assignment['shift_id']]++;
        }
        
        $avgPerShift = count($employees) / count($shifts);
        
        // Rebalance if needed
        foreach ($assignments as &$assignment) {
            $currentShift = $assignment['shift_id'];
            
            // If current shift is overloaded
            if ($shiftCounts[$currentShift] > $avgPerShift + 2) {
                // Find underloaded shift
                foreach ($shiftCounts as $shiftId => $count) {
                    if ($count < $avgPerShift - 1) {
                        $assignment['shift_id'] = $shiftId;
                        $shiftCounts[$currentShift]--;
                        $shiftCounts[$shiftId]++;
                        break;
                    }
                }
            }
        }
        
        // Apply assignments
        $pdo->beginTransaction();
        
        // End all current assignments
        $pdo->exec("UPDATE employee_shifts SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
                   WHERE end_date IS NULL OR end_date >= CURDATE()");
        
        // Create new assignments
        $insertStmt = $pdo->prepare("INSERT INTO employee_shifts 
                                     (employee_id, shift_id, effective_date, is_permanent, assigned_by, notes)
                                     VALUES (?, ?, CURDATE(), 1, ?, 'Auto-assigned via shuffle')");
        
        foreach ($assignments as $assignment) {
            $insertStmt->execute([
                $assignment['employee_id'],
                $assignment['shift_id'],
                $_SESSION['user_id']
            ]);
        }
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Shuffle berhasil! ' . count($assignments) . ' karyawan telah di-assign secara otomatis';
        header('Location: /hrm/admin/shifts/shuffle.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

// Get current distribution
$distributionStmt = $pdo->query("SELECT ws.shift_name, ws.shift_code, COUNT(es.employee_id) as employee_count
                                FROM work_shifts ws
                                LEFT JOIN employee_shifts es ON ws.id = es.shift_id 
                                AND (es.end_date IS NULL OR es.end_date >= CURDATE())
                                WHERE ws.is_active = 1
                                GROUP BY ws.id
                                ORDER BY ws.shift_code");
$distribution = $distributionStmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-random me-2"></i> Shuffle Shift Assignment</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/shifts/index.php">Shift Kerja</a></li>
                    <li class="breadcrumb-item active">Shuffle</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/shifts/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i> Tentang Shuffle Shift</h5>
            <p class="mb-0">
                Fitur ini akan secara otomatis mendistribusikan karyawan ke shift yang berbeda berdasarkan:
            </p>
            <ul class="mb-0 mt-2">
                <li><strong>History Attendance:</strong> Karyawan yang jarang dapat shift tertentu akan diprioritaskan</li>
                <li><strong>Night Shift Priority:</strong> Shift malam mendapat prioritas lebih tinggi</li>
                <li><strong>Balance Distribution:</strong> Setiap shift akan mendapat jumlah karyawan yang seimbang</li>
                <li><strong>Fair Rotation:</strong> Sistem memastikan rotasi yang adil untuk semua karyawan</li>
            </ul>
        </div>
    </div>
</div>

<!-- Current Distribution -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i> Distribusi Shift Saat Ini
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($distribution as $dist): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="text-primary"><?php echo $dist['employee_count']; ?></h3>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($dist['shift_name']); ?></p>
                                    <small class="text-muted">(<?php echo htmlspecialchars($dist['shift_code']); ?>)</small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shuffle Action -->
<div class="row">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i> Peringatan
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    <strong>Proses shuffle akan:</strong>
                </p>
                <ul>
                    <li>Mengakhiri semua assignment shift yang ada saat ini</li>
                    <li>Membuat assignment baru berdasarkan algoritma fairness</li>
                    <li>Mempertimbangkan history 30 hari terakhir</li>
                    <li>Mendistribusikan karyawan secara merata ke semua shift</li>
                </ul>
                
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> Proses ini tidak dapat di-undo. Pastikan Anda sudah backup data jika diperlukan.
                </div>
                
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melakukan shuffle? Semua assignment saat ini akan diganti dengan assignment baru yang lebih adil.');">
                    <input type="hidden" name="shuffle" value="1">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-random me-2"></i> Mulai Shuffle Shift
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i> Cara Kerja Algoritma
                </h5>
            </div>
            <div class="card-body">
                <ol>
                    <li><strong>Analisis History:</strong> Sistem menganalisis attendance 30 hari terakhir untuk setiap karyawan</li>
                    <li><strong>Hitung Score:</strong> Setiap kombinasi karyawan-shift diberi score berdasarkan frekuensi kerja di shift tersebut</li>
                    <li><strong>Prioritas Night Shift:</strong> Shift malam mendapat bonus prioritas (score -2)</li>
                    <li><strong>Assignment Awal:</strong> Setiap karyawan di-assign ke shift dengan score terendah (paling jarang dikerjakan)</li>
                    <li><strong>Balancing:</strong> Sistem menyeimbangkan jumlah karyawan per shift agar merata</li>
                    <li><strong>Apply:</strong> Assignment baru diterapkan dengan effective date hari ini</li>
                </ol>
                
                <div class="mt-3">
                    <h6>Contoh Perhitungan:</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Shift Pagi (30d)</th>
                                <th>Shift Siang (30d)</th>
                                <th>Shift Malam (30d)</th>
                                <th>Assigned To</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John</td>
                                <td>15x (score: 15)</td>
                                <td>10x (score: 10)</td>
                                <td>5x (score: 3*)</td>
                                <td><span class="badge bg-dark">Shift Malam</span></td>
                            </tr>
                            <tr>
                                <td>Jane</td>
                                <td>8x (score: 8)</td>
                                <td>12x (score: 12)</td>
                                <td>10x (score: 8*)</td>
                                <td><span class="badge bg-primary">Shift Pagi</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <small class="text-muted">* Night shift bonus: score - 2</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
