<?php
$page_title = 'Lihat Karyawan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get employee ID
$id = $_GET['id'] ?? 0;

// Get employee details
try {
    $stmt = $pdo->prepare("SELECT e.*, d.department_name, p.position_name 
                           FROM employees e
                           LEFT JOIN departments d ON e.department_id = d.id
                           LEFT JOIN positions p ON e.position_id = p.id
                           WHERE e.id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect('/hrm/admin/employees/index.php', 'error', 'Employee not found');
    }
} catch (PDOException $e) {
    error_log("Error fetching employee: " . $e->getMessage());
    redirect('/hrm/admin/employees/index.php', 'error', 'Error fetching employee details');
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-user me-2"></i> Employee Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/employees/index.php">Employees</a></li>
                    <li class="breadcrumb-item active">Lihat Karyawan</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/employees/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
            <a href="/hrm/admin/employees/edit.php?id=<?php echo $employee['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i> Ubah
            </a>
            <button onclick="confirmDelete('/hrm/admin/employees/delete.php?id=<?php echo $employee['id']; ?>', 'Hapus Karyawan?', 'Ini akan menghapus permanen karyawan ini')" 
                    class="btn btn-danger">
                <i class="fas fa-trash me-2"></i> Hapus
            </button>
        </div>
    </div>
</div>

<!-- Employee Details -->
<div class="row">
    <!-- Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="card fade-in">
            <div class="card-body text-center">
                <?php if ($employee['photo']): ?>
                    <img src="/hrm/uploads/employees/<?php echo htmlspecialchars($employee['photo']); ?>" 
                         alt="Photo" class="employee-photo-large mb-3">
                <?php else: ?>
                    <img src="<?php echo getDefaultAvatar($employee['gender']); ?>" 
                         alt="Avatar" class="employee-photo-large mb-3">
                <?php endif; ?>
                
                <h3 class="mb-1"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h3>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($employee['employee_code']); ?></p>
                
                <span class="badge <?php echo getStatusBadgeClass($employee['employment_status']); ?> mb-3">
                    <?php echo htmlspecialchars($employee['employment_status']); ?>
                </span>
                
                <div class="d-grid gap-2 mt-3">
                    <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i> Send Email
                    </a>
                    <?php if ($employee['phone']): ?>
                        <a href="tel:<?php echo htmlspecialchars($employee['phone']); ?>" class="btn btn-success">
                            <i class="fas fa-phone me-2"></i> Call
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Details Card -->
    <div class="col-lg-8 mb-4">
        <!-- Personal Information -->
        <div class="card fade-in mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i> Personal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-id-card me-2 text-primary"></i> Employee Code:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($employee['employee_code']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-user me-2 text-primary"></i> Full Name:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-envelope me-2 text-primary"></i> Email:</strong>
                    </div>
                    <div class="col-md-8">
                        <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>">
                            <?php echo htmlspecialchars($employee['email']); ?>
                        </a>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-phone me-2 text-primary"></i> Phone:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($employee['phone'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-birthday-cake me-2 text-primary"></i> Date of Birth:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($employee['date_of_birth']); ?>
                        <?php if ($employee['date_of_birth']): ?>
                            <span class="badge bg-info"><?php echo calculateAge($employee['date_of_birth']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-venus-mars me-2 text-primary"></i> Gender:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo getGenderIcon($employee['gender']); ?>
                        <?php echo htmlspecialchars($employee['gender']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo nl2br(htmlspecialchars($employee['address'] ?? '-')); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employment Information -->
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-briefcase me-2"></i> Employment Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-building me-2 text-success"></i> Department:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($employee['department_name'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-briefcase me-2 text-success"></i> Position:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo htmlspecialchars($employee['position_name'] ?? '-'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-calendar me-2 text-success"></i> Hire Date:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($employee['hire_date']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-info-circle me-2 text-success"></i> Employment Status:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge <?php echo getStatusBadgeClass($employee['employment_status']); ?>">
                            <?php echo htmlspecialchars($employee['employment_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-money-bill-wave me-2 text-success"></i> Salary:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo $employee['salary'] ? formatCurrency($employee['salary']) : '-'; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-clock me-2 text-success"></i> Created At:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($employee['created_at'], 'd M Y H:i'); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-sync me-2 text-success"></i> Last Updated:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo formatDate($employee['updated_at'], 'd M Y H:i'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Contracts -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-contract me-2"></i> Contract History
                </h5>
                <a href="/hrm/admin/contracts/add.php?employee_id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i> Tambah Kontrak Baru
                </a>
            </div>
            <div class="card-body">
                <?php
                // Get employee contracts
                try {
                    $contractStmt = $pdo->prepare("SELECT c.*, 
                                                   d.department_name,
                                                   p.position_name
                                                   FROM contracts c
                                                   LEFT JOIN departments d ON c.department_id = d.id
                                                   LEFT JOIN positions p ON c.position_id = p.id
                                                   WHERE c.employee_id = ?
                                                   ORDER BY c.start_date DESC");
                    $contractStmt->execute([$id]);
                    $contracts = $contractStmt->fetchAll();
                } catch (PDOException $e) {
                    error_log("Error fetching contracts: " . $e->getMessage());
                    $contracts = [];
                }
                
                if (count($contracts) > 0):
                ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Contract #</th>
                                    <th>Type</th>
                                    <th>Job Title</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract): 
                                    // Calculate duration
                                    $startDate = new DateTime($contract['start_date']);
                                    $endDate = $contract['end_date'] ? new DateTime($contract['end_date']) : null;
                                    $today = new DateTime();
                                    
                                    if ($endDate) {
                                        $duration = $startDate->diff($endDate);
                                        $isExpired = $today > $endDate;
                                        $daysRemaining = $today->diff($endDate)->days;
                                    } else {
                                        $duration = $startDate->diff($today);
                                        $isExpired = false;
                                        $daysRemaining = null;
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($contract['contract_number']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($contract['contract_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($contract['job_title'] ?? '-'); ?></td>
                                        <td><?php echo formatDate($contract['start_date']); ?></td>
                                        <td>
                                            <?php 
                                            if ($contract['end_date']) {
                                                echo formatDate($contract['end_date']);
                                                if ($isExpired) {
                                                    echo '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Expired</small>';
                                                } elseif ($daysRemaining <= 30) {
                                                    echo '<br><small class="text-warning"><i class="fas fa-clock"></i> ' . $daysRemaining . ' days left</small>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Permanent</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($duration) {
                                                $years = $duration->y;
                                                $months = $duration->m;
                                                $days = $duration->d;
                                                
                                                $durationText = '';
                                                if ($years > 0) $durationText .= $years . 'y ';
                                                if ($months > 0) $durationText .= $months . 'm ';
                                                if ($days > 0) $durationText .= $days . 'd';
                                                
                                                echo '<small>' . trim($durationText) . '</small>';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
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
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($contract['contract_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="/hrm/admin/contracts/view.php?id=<?php echo $contract['id']; ?>" 
                                                   class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="/hrm/admin/contracts/edit.php?id=<?php echo $contract['id']; ?>" 
                                                   class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Ubah">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($contract['contract_file']): ?>
                                                    <a href="/hrm/uploads/contracts/<?php echo htmlspecialchars($contract['contract_file']); ?>" 
                                                       class="btn btn-sm btn-primary" target="_blank" data-bs-toggle="tooltip" title="Lihat PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No contracts found for this employee. 
                        <a href="/hrm/admin/contracts/add.php?employee_id=<?php echo $employee['id']; ?>" class="alert-link">
                            Click here to add a contract
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
