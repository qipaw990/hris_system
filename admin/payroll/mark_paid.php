<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$period_id = $_GET['id'] ?? 0;

if (empty($period_id)) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'ID periode tidak valid');
}

try {
    // Update period status to Paid
    $sql = "UPDATE payroll_periods SET status = 'Paid' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$period_id]);
    
    // Update all slips in this period to Paid
    $slipSql = "UPDATE payroll_slips SET status = 'Paid', payment_date = NOW() WHERE period_id = ?";
    $slipStmt = $pdo->prepare($slipSql);
    $slipStmt->execute([$period_id]);
    
    redirect('/hrm/admin/payroll/view_period.php?id=' . $period_id, 'success', 'Periode penggajian berhasil ditandai sebagai sudah dibayar');
    
} catch (PDOException $e) {
    error_log("Error marking period as paid: " . $e->getMessage());
    redirect('/hrm/admin/payroll/view_period.php?id=' . $period_id, 'error', 'Gagal menandai periode sebagai sudah dibayar');
}
