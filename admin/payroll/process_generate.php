<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/payroll/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'Token keamanan tidak valid');
}

// Get form data
$period_month = $_POST['period_month'] ?? 0;
$period_year = $_POST['period_year'] ?? 0;
$payment_date = $_POST['payment_date'] ?? '';

// Validate
if (empty($period_month) || empty($period_year) || empty($payment_date)) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'Data tidak lengkap');
}

try {
    // Check if period already exists
    $checkStmt = $pdo->prepare("SELECT id FROM payroll_periods WHERE period_month = ? AND period_year = ?");
    $checkStmt->execute([$period_month, $period_year]);
    if ($checkStmt->fetch()) {
        redirect('/hrm/admin/payroll/index.php', 'error', 'Periode penggajian untuk bulan ini sudah ada');
    }
    
    // Calculate period dates
    $period_name = date('F Y', mktime(0, 0, 0, $period_month, 1, $period_year));
    $start_date = "$period_year-$period_month-01";
    $end_date = date('Y-m-t', strtotime($start_date));
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Create payroll period
    $periodSql = "INSERT INTO payroll_periods (period_name, period_month, period_year, start_date, end_date, payment_date, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $periodStmt = $pdo->prepare($periodSql);
    $periodStmt->execute([$period_name, $period_month, $period_year, $start_date, $end_date, $payment_date, $_SESSION['user_id']]);
    $period_id = $pdo->lastInsertId();
    
    // Get all active employees
    $empStmt = $pdo->query("SELECT id, basic_salary FROM employees WHERE employment_status = 'Active'");
    $employees = $empStmt->fetchAll();
    
    // Get active payroll components
    $componentsStmt = $pdo->query("SELECT * FROM payroll_components WHERE is_active = 1 ORDER BY component_type, component_name");
    $components = $componentsStmt->fetchAll();
    
    $total_employees = 0;
    $total_gross = 0;
    $total_deductions = 0;
    $total_net = 0;
    
    // Generate payroll for each employee
    foreach ($employees as $emp) {
        $basic_salary = $emp['basic_salary'] ?? 0;
        $earnings = 0;
        $deductions = 0;
        
        // Get attendance data for this period
        $attStmt = $pdo->prepare("SELECT 
                                  COUNT(*) as attendance_days,
                                  SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as present_days,
                                  SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as late_count,
                                  SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha_count
                                  FROM attendance 
                                  WHERE employee_id = ? 
                                  AND attendance_date BETWEEN ? AND ?");
        $attStmt->execute([$emp['id'], $start_date, $end_date]);
        $attendance = $attStmt->fetch();
        
        $working_days = cal_days_in_month(CAL_GREGORIAN, $period_month, $period_year);
        $attendance_days = $attendance['present_days'] ?? 0;
        $late_count = $attendance['late_count'] ?? 0;
        $alpha_count = $attendance['alpha_count'] ?? 0;
        
        // Create payroll slip
        $slipSql = "INSERT INTO payroll_slips (period_id, employee_id, basic_salary, total_earnings, total_deductions, net_salary, attendance_days, working_days, late_count) 
                    VALUES (?, ?, ?, 0, 0, 0, ?, ?, ?)";
        $slipStmt = $pdo->prepare($slipSql);
        $slipStmt->execute([$period_id, $emp['id'], $basic_salary, $attendance_days, $working_days, $late_count]);
        $slip_id = $pdo->lastInsertId();
        
        // Calculate earnings and deductions
        $slip_details = [];
        
        foreach ($components as $comp) {
            $amount = 0;
            
            // Calculate amount based on calculation type
            switch ($comp['calculation_type']) {
                case 'Fixed':
                    $amount = $comp['default_amount'];
                    break;
                    
                case 'Percentage':
                    $amount = ($basic_salary * $comp['default_amount']) / 100;
                    break;
                    
                case 'Formula':
                    // Special formulas
                    if ($comp['component_name'] == 'Potongan Keterlambatan') {
                        // Rp 50,000 per keterlambatan
                        $amount = $late_count * 50000;
                    } elseif ($comp['component_name'] == 'Potongan Alpha') {
                        // Potong 1 hari gaji per alpha
                        $daily_salary = $basic_salary / $working_days;
                        $amount = $alpha_count * $daily_salary;
                    }
                    break;
            }
            
            // Only add if amount > 0
            if ($amount > 0) {
                // Insert slip detail
                $detailSql = "INSERT INTO payroll_slip_details (slip_id, component_id, component_name, component_type, amount) 
                             VALUES (?, ?, ?, ?, ?)";
                $detailStmt = $pdo->prepare($detailSql);
                $detailStmt->execute([$slip_id, $comp['id'], $comp['component_name'], $comp['component_type'], $amount]);
                
                if ($comp['component_type'] == 'Earning') {
                    $earnings += $amount;
                } else {
                    $deductions += $amount;
                }
            }
        }
        
        // Add basic salary to earnings
        $earnings += $basic_salary;
        
        // Calculate net salary
        $net_salary = $earnings - $deductions;
        
        // Update slip totals
        $updateSlipSql = "UPDATE payroll_slips 
                         SET total_earnings = ?, total_deductions = ?, net_salary = ? 
                         WHERE id = ?";
        $updateSlipStmt = $pdo->prepare($updateSlipSql);
        $updateSlipStmt->execute([$earnings, $deductions, $net_salary, $slip_id]);
        
        // Add to period totals
        $total_employees++;
        $total_gross += $earnings;
        $total_deductions += $deductions;
        $total_net += $net_salary;
    }
    
    // Update period totals
    $updatePeriodSql = "UPDATE payroll_periods 
                       SET total_employees = ?, total_gross = ?, total_deductions = ?, total_net = ?, status = 'Processed' 
                       WHERE id = ?";
    $updatePeriodStmt = $pdo->prepare($updatePeriodSql);
    $updatePeriodStmt->execute([$total_employees, $total_gross, $total_deductions, $total_net, $period_id]);
    
    // Commit transaction
    $pdo->commit();
    
    redirect('/hrm/admin/payroll/view_period.php?id=' . $period_id, 'success', 'Penggajian berhasil di-generate untuk ' . $total_employees . ' karyawan');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error generating payroll: " . $e->getMessage());
    redirect('/hrm/admin/payroll/index.php', 'error', 'Gagal generate penggajian: ' . $e->getMessage());
}
