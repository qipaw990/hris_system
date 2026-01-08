<?php
$page_title = 'Manajemen Kontrak';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get all contracts with employee info
try {
    $stmt = $pdo->query("SELECT c.*, 
                         CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                         e.employee_code,
                         d.department_name,
                         p.position_name
                         FROM contracts c
                         LEFT JOIN employees e ON c.employee_id = e.id
                         LEFT JOIN departments d ON c.department_id = d.id
                         LEFT JOIN positions p ON c.position_id = p.id
                         ORDER BY c.created_at DESC");
    $contracts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching contracts: " . $e->getMessage());
    $contracts = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-file-contract me-2"></i> Manajemen Kontrak</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Kontrak</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/contracts/add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Tambah Kontrak Baru
            </a>
        </div>
    </div>
</div>

<!-- Contracts Table -->
<div class="row">
    <div class="col-12">
        <!-- Search & Filter Card -->
        <div class="card fade-in mb-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i> Cari & Filter
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="filterType" class="form-label">Contract Type</label>
                        <select class="form-select" id="filterType">
                            <option value="">All Types</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                            <option value="Probation">Probation</option>
                            <option value="Internship">Internship</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="Active">Aktif</option>
                            <option value="Expired">Expired</option>
                            <option value="Terminated">Terminated</option>
                            <option value="Renewed">Renewed</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterStartDate" class="form-label">Start Date From</label>
                        <input type="date" class="form-control" id="filterStartDate">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterEndDate" class="form-label">Start Date To</label>
                        <input type="date" class="form-control" id="filterEndDate">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" id="btnApplyFilter">
                            <i class="fas fa-filter me-2"></i> Terapkan Filter
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnResetFilter">
                            <i class="fas fa-redo me-2"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Semua Kontrak
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="contractsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Contract #</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Job Title</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($contract['contract_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($contract['employee_name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($contract['employee_code']); ?></small>
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
                                            // Check if expired
                                            if (strtotime($contract['end_date']) < time()) {
                                                echo '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Expired</small>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">Permanent</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $contract['salary'] ? formatCurrency($contract['salary']) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($contract['contract_status']) {
                                            case 'Active':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'Expired':
                                                $statusClass = 'bg-danger';
                                                break;
                                            case 'Terminated':
                                                $statusClass = 'bg-dark';
                                                break;
                                            case 'Renewed':
                                                $statusClass = 'bg-primary';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($contract['contract_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/hrm/admin/contracts/view.php?id=<?php echo $contract['id']; ?>" 
                                               class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/hrm/admin/contracts/edit.php?id=<?php echo $contract['id']; ?>" 
                                               class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete('/hrm/admin/contracts/delete.php?id=<?php echo $contract['id']; ?>', 'Delete Contract?', 'This will permanently delete contract <?php echo htmlspecialchars($contract['contract_number']); ?>')" 
                                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#contractsTable').DataTable({
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ],
        pageLength: 25,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search contracts..."
        }
    });
    
    // Custom filtering function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var filterType = $('#filterType').val();
            var filterStatus = $('#filterStatus').val();
            var filterStartDate = $('#filterStartDate').val();
            var filterEndDate = $('#filterEndDate').val();
            
            var contractType = data[2] || ''; // Type column
            var contractStatus = data[7] || ''; // Status column
            var startDate = data[4] || ''; // Start Date column
            
            // Filter by type
            if (filterType && !contractType.includes(filterType)) {
                return false;
            }
            
            // Filter by status
            if (filterStatus && !contractStatus.includes(filterStatus)) {
                return false;
            }
            
            // Filter by date range
            if (filterStartDate || filterEndDate) {
                var dateValue = new Date(startDate);
                var minDate = filterStartDate ? new Date(filterStartDate) : null;
                var maxDate = filterEndDate ? new Date(filterEndDate) : null;
                
                if (minDate && dateValue < minDate) {
                    return false;
                }
                if (maxDate && dateValue > maxDate) {
                    return false;
                }
            }
            
            return true;
        }
    );
    
    // Apply filter button
    $('#btnApplyFilter').on('click', function() {
        table.draw();
    });
    
    // Reset filter button
    $('#btnResetFilter').on('click', function() {
        $('#filterType').val('');
        $('#filterStatus').val('');
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        table.draw();
    });
    
    // Auto-apply on Enter key
    $('#filterType, #filterStatus, #filterStartDate, #filterEndDate').on('keypress', function(e) {
        if (e.which === 13) {
            table.draw();
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
