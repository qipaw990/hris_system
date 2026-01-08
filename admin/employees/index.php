<?php
$page_title = 'All Employees';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get all employees with department, position, and contract info
try {
    $stmt = $pdo->query("SELECT e.*, 
                         d.department_name, 
                         p.position_name,
                         (SELECT COUNT(*) FROM contracts c 
                          WHERE c.employee_id = e.id 
                          AND c.contract_status = 'Active' 
                          AND (c.end_date IS NULL OR c.end_date >= CURDATE())) as has_active_contract,
                         (SELECT c.contract_number FROM contracts c 
                          WHERE c.employee_id = e.id 
                          AND c.contract_status = 'Active' 
                          ORDER BY c.start_date DESC LIMIT 1) as active_contract_number,
                         (SELECT c.end_date FROM contracts c 
                          WHERE c.employee_id = e.id 
                          AND c.contract_status = 'Active' 
                          ORDER BY c.start_date DESC LIMIT 1) as contract_end_date
                         FROM employees e
                         LEFT JOIN departments d ON e.department_id = d.id
                         LEFT JOIN positions p ON e.position_id = p.id
                         ORDER BY e.created_at DESC");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching employees: " . $e->getMessage());
    $employees = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-users me-2"></i> All Employees</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Employees</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/employees/add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Tambah Karyawan Baru
            </a>
        </div>
    </div>
</div>

<!-- Employees Table -->
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
                        <label for="filterDepartment" class="form-label">Department</label>
                        <select class="form-select" id="filterDepartment">
                            <option value="">All Departments</option>
                            <?php
                            try {
                                $deptStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
                                while ($dept = $deptStmt->fetch()) {
                                    echo '<option value="' . htmlspecialchars($dept['department_name']) . '">' . htmlspecialchars($dept['department_name']) . '</option>';
                                }
                            } catch (PDOException $e) {
                                error_log("Error fetching departments: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterPosition" class="form-label">Position</label>
                        <select class="form-select" id="filterPosition">
                            <option value="">All Positions</option>
                            <?php
                            try {
                                $posStmt = $pdo->query("SELECT * FROM positions ORDER BY position_name");
                                while ($pos = $posStmt->fetch()) {
                                    echo '<option value="' . htmlspecialchars($pos['position_name']) . '">' . htmlspecialchars($pos['position_name']) . '</option>';
                                }
                            } catch (PDOException $e) {
                                error_log("Error fetching positions: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="Active">Aktif</option>
                            <option value="Inactive">Tidak Aktif</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Terminated">Terminated</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="filterGender" class="form-label">Gender</label>
                        <select class="form-select" id="filterGender">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="filterContract" class="form-label">Contract</label>
                        <select class="form-select" id="filterContract">
                            <option value="">All</option>
                            <option value="Active">Punya Kontrak Aktif</option>
                            <option value="NoContract">No Contract</option>
                        </select>
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
                    <i class="fas fa-list me-2"></i> Employee List
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employeesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Employee Code</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Contract</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td>
                                        <?php if ($emp['photo']): ?>
                                            <img src="/hrm/uploads/employees/<?php echo htmlspecialchars($emp['photo']); ?>" 
                                                 alt="Photo" class="employee-photo" style="width: 50px; height: 50px;">
                                        <?php else: ?>
                                            <img src="<?php echo getDefaultAvatar($emp['gender']); ?>" 
                                                 alt="Avatar" class="employee-photo" style="width: 50px; height: 50px;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($emp['employee_code']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo getGenderIcon($emp['gender']); ?>
                                            <?php echo htmlspecialchars($emp['gender']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($emp['position_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($emp['employment_status']); ?>">
                                            <?php echo htmlspecialchars($emp['employment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($emp['has_active_contract'] > 0) {
                                            // Check if contract is expiring soon
                                            $daysToExpire = null;
                                            $isExpiring = false; // Initialize variable
                                            
                                            if ($emp['contract_end_date']) {
                                                $endDate = new DateTime($emp['contract_end_date']);
                                                $today = new DateTime();
                                                $daysToExpire = $today->diff($endDate)->days;
                                                $isExpiring = ($daysToExpire <= 30 && $endDate > $today);
                                            }
                                            
                                            echo '<span class="badge bg-success" data-bs-toggle="tooltip" title="Contract: ' . htmlspecialchars($emp['active_contract_number']) . '">';
                                            echo '<i class="fas fa-file-contract me-1"></i> Aktif';
                                            echo '</span>';
                                            
                                            if ($isExpiring) {
                                                echo '<br><small class="text-warning"><i class="fas fa-clock"></i> ' . $daysToExpire . ' days</small>';
                                            }
                                        } else {
                                            echo '<span class="badge bg-secondary" data-bs-toggle="tooltip" title="No active contract">';
                                            echo '<i class="fas fa-times me-1"></i> No Contract';
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/hrm/admin/employees/view.php?id=<?php echo $emp['id']; ?>" 
                                               class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/hrm/admin/employees/edit.php?id=<?php echo $emp['id']; ?>" 
                                               class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Ubah">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete('/hrm/admin/employees/delete.php?id=<?php echo $emp['id']; ?>', 'Hapus Karyawan?', 'Ini akan menghapus permanen <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>')" 
                                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
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
    var table = $('#employeesTable').DataTable({
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Photo and Actions columns
        ],
        pageLength: 25,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search employees..."
        }
    });
    
    // Custom filtering function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var filterDepartment = $('#filterDepartment').val();
            var filterPosition = $('#filterPosition').val();
            var filterStatus = $('#filterStatus').val();
            var filterGender = $('#filterGender').val();
            var filterContract = $('#filterContract').val();
            
            var department = data[5] || ''; // Department column
            var position = data[6] || ''; // Position column
            var status = data[7] || ''; // Status column
            var gender = data[2] || ''; // Name column (contains gender info)
            var contract = data[8] || ''; // Contract column
            
            // Filter by department
            if (filterDepartment && department !== filterDepartment) {
                return false;
            }
            
            // Filter by position
            if (filterPosition && position !== filterPosition) {
                return false;
            }
            
            // Filter by status
            if (filterStatus && !status.includes(filterStatus)) {
                return false;
            }
            
            // Filter by gender
            if (filterGender && !gender.includes(filterGender)) {
                return false;
            }
            
            // Filter by contract
            if (filterContract) {
                if (filterContract === 'Active' && !contract.includes('Active')) {
                    return false;
                }
                if (filterContract === 'NoContract' && !contract.includes('No Contract')) {
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
        $('#filterDepartment').val('');
        $('#filterPosition').val('');
        $('#filterStatus').val('');
        $('#filterGender').val('');
        $('#filterContract').val('');
        table.draw();
    });
    
    // Auto-apply on Enter key
    $('#filterDepartment, #filterPosition, #filterStatus, #filterGender, #filterContract').on('keypress', function(e) {
        if (e.which === 13) {
            table.draw();
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
