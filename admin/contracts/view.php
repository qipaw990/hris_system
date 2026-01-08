<?php
$page_title = 'Lihat Kontrak';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get contract ID
$id = $_GET['id'] ?? 0;

// Get contract details
try {
    $stmt = $pdo->prepare("SELECT c.*, 
                           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                           e.employee_code, e.email as employee_email, e.phone as employee_phone,
                           d.department_name,
                           p.position_name,
                           CONCAT(u.username) as created_by_name
                           FROM contracts c
                           LEFT JOIN employees e ON c.employee_id = e.id
                           LEFT JOIN departments d ON c.department_id = d.id
                           LEFT JOIN positions p ON c.position_id = p.id
                           LEFT JOIN users u ON c.created_by = u.id
                           WHERE c.id = ?");
    $stmt->execute([$id]);
    $contract = $stmt->fetch();
    
    if (!$contract) {
        redirect('/hrm/admin/contracts/index.php', 'error', 'Contract not found');
    }
} catch (PDOException $e) {
    error_log("Error fetching contract: " . $e->getMessage());
    redirect('/hrm/admin/contracts/index.php', 'error', 'Error fetching contract details');
}

// Calculate contract duration
$startDate = new DateTime($contract['start_date']);
$endDate = $contract['end_date'] ? new DateTime($contract['end_date']) : null;
$today = new DateTime();

if ($endDate) {
    $duration = $startDate->diff($endDate);
    $daysRemaining = $today->diff($endDate);
    $isExpired = $today > $endDate;
} else {
    $duration = null;
    $daysRemaining = null;
    $isExpired = false;
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-file-contract me-2"></i> Detail Kontrak</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/contracts/index.php">Kontrak</a></li>
                    <li class="breadcrumb-item active">Lihat Kontrak</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/contracts/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
            <a href="/hrm/admin/contracts/edit.php?id=<?php echo $contract['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i> Ubah
            </a>
            <button onclick="confirmDelete('/hrm/admin/contracts/delete.php?id=<?php echo $contract['id']; ?>', 'Hapus Kontrak?', 'Ini akan menghapus permanen kontrak ini')" 
                    class="btn btn-danger">
                <i class="fas fa-trash me-2"></i> Hapus
            </button>
        </div>
    </div>
</div>

<!-- Contract Details -->
<div class="row">
    <!-- Contract Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-file-contract fa-4x text-primary"></i>
                </div>
                
                <h4 class="mb-1"><?php echo htmlspecialchars($contract['contract_number']); ?></h4>
                <p class="text-muted mb-3">Contract Number</p>
                
                <span class="badge bg-info mb-3" style="font-size: 0.9rem;">
                    <?php echo htmlspecialchars($contract['contract_type']); ?>
                </span>
                
                <?php
                $statusClass = '';
                switch ($contract['contract_status']) {
                    case 'Active': $statusClass = 'bg-success'; break;
                    case 'Expired': $statusClass = 'bg-danger'; break;
                    case 'Terminated': $statusClass = 'bg-dark'; break;
                    case 'Renewed': $statusClass = 'bg-primary'; break;
                    default: $statusClass = 'bg-secondary';
                }
                ?>
                <br>
                <span class="badge <?php echo $statusClass; ?>" style="font-size: 0.9rem;">
                    <?php echo htmlspecialchars($contract['contract_status']); ?>
                </span>
                
                <?php if ($contract['contract_file']): ?>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/hrm/uploads/contracts/<?php echo htmlspecialchars($contract['contract_file']); ?>" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Lihat PDF Kontrak
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Duration Info -->
        <?php if ($duration || $daysRemaining): ?>
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i> Duration Info
                </h5>
            </div>
            <div class="card-body">
                <?php if ($duration): ?>
                    <div class="mb-3">
                        <strong>Contract Duration:</strong><br>
                        <span class="text-muted">
                            <?php echo $duration->y; ?> years, <?php echo $duration->m; ?> months, <?php echo $duration->d; ?> days
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if ($daysRemaining && !$isExpired): ?>
                    <div class="mb-3">
                        <strong>Days Remaining:</strong><br>
                        <span class="badge bg-warning">
                            <?php echo $daysRemaining->days; ?> days
                        </span>
                    </div>
                <?php elseif ($isExpired): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Contract Expired!</strong><br>
                        Expired <?php echo $daysRemaining->days; ?> days ago
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Details Cards -->
    <div class="col-lg-8 mb-4">
        <!-- Employee Information -->
        <div class="card fade-in mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i> Employee Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-id-card me-2 text-primary"></i> Employee Code:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['employee_code']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-user me-2 text-primary"></i> Full Name:</strong>
                    </div>
                    <div class="col-md-8">
                        <a href="/hrm/admin/employees/view.php?id=<?php echo $contract['employee_id']; ?>">
                            <?php echo htmlspecialchars($contract['employee_name']); ?>
                        </a>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-envelope me-2 text-primary"></i> Email:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['employee_email'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-phone me-2 text-primary"></i> Phone:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['employee_phone'] ?? '-'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contract Details -->
        <div class="card fade-in mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i> Contract Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-calendar-alt me-2 text-success"></i> Start Date:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($contract['start_date']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-calendar-times me-2 text-success"></i> End Date:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo $contract['end_date'] ? formatDate($contract['end_date']) : '<span class="text-muted">Permanent</span>'; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-money-bill-wave me-2 text-success"></i> Salary:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo $contract['salary'] ? formatCurrency($contract['salary']) : '-'; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Job Details -->
        <div class="card fade-in mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-briefcase me-2"></i> Job Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-user-tie me-2 text-info"></i> Job Title:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['job_title'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-building me-2 text-info"></i> Department:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['department_name'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-briefcase me-2 text-info"></i> Position:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['position_name'] ?? '-'); ?>
                    </div>
                </div>
                
                <?php if ($contract['notes']): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-sticky-note me-2 text-info"></i> Notes:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo nl2br(htmlspecialchars($contract['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- System Info -->
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i> System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-user me-2 text-secondary"></i> Created By:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($contract['created_by_name'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-clock me-2 text-secondary"></i> Created At:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($contract['created_at'], 'd M Y H:i'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-sync me-2 text-secondary"></i> Last Updated:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($contract['updated_at'], 'd M Y H:i'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
