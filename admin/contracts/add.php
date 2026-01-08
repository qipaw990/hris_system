<?php
$page_title = 'Add Contract';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get employees, departments, and positions for dropdowns
try {
    $empStmt = $pdo->query("SELECT id, employee_code, CONCAT(first_name, ' ', last_name) as full_name FROM employees WHERE employment_status = 'Active' ORDER BY first_name");
    $employees = $empStmt->fetchAll();
    
    $deptStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $deptStmt->fetchAll();
    
    $posStmt = $pdo->query("SELECT * FROM positions ORDER BY position_name");
    $positions = $posStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    $employees = [];
    $departments = [];
    $positions = [];
}

// Generate contract number
$year = date('Y');
$stmt = $pdo->query("SELECT contract_number FROM contracts WHERE contract_number LIKE 'CTR-$year-%' ORDER BY id DESC LIMIT 1");
$lastContract = $stmt->fetchColumn();
if ($lastContract) {
    $number = intval(substr($lastContract, -3)) + 1;
} else {
    $number = 1;
}
$contractNumber = 'CTR-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-file-contract me-2"></i> Add New Contract</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/contracts/index.php">Contracts</a></li>
                    <li class="breadcrumb-item active">Add Contract</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/contracts/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>
</div>

<!-- Add Contract Form -->
<div class="row">
    <div class="col-12">
        <form action="/hrm/admin/contracts/process_add.php" method="POST" enctype="multipart/form-data" id="addContractForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Contract Information -->
            <div class="card fade-in mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i> Contract Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="contract_number" class="form-label">Contract Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contract_number" name="contract_number" 
                                   value="<?php echo htmlspecialchars($contractNumber); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['employee_code'] . ' - ' . $emp['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_type" name="contract_type" required>
                                <option value="">Select Type</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Contract">Contract</option>
                                <option value="Probation">Probation</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                            <small class="text-muted">Leave empty for permanent contracts</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="contract_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_status" name="contract_status" required>
                                <option value="Active" selected>Active</option>
                                <option value="Expired">Expired</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Renewed">Renewed</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contract_file" class="form-label">Contract File (PDF)</label>
                            <input type="file" class="form-control" id="contract_file" name="contract_file" accept=".pdf">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="salary" class="form-label">Salary (Rp)</label>
                            <input type="number" class="form-control" id="salary" name="salary" 
                                   placeholder="0" min="0" step="1000">
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
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="job_title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="position_id" class="form-label">Position</label>
                            <select class="form-select" id="position_id" name="position_id">
                                <option value="">Select Position</option>
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?php echo $pos['id']; ?>">
                                        <?php echo htmlspecialchars($pos['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="/hrm/admin/contracts/index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Contract
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form validation
    $('#addContractForm').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Auto-disable end date for permanent contracts
    $('#contract_type').on('change', function() {
        if ($(this).val() === 'Permanent') {
            $('#end_date').val('').prop('disabled', true);
        } else {
            $('#end_date').prop('disabled', false);
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
