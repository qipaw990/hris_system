<?php
$page_title = 'Edit Contract';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get contract ID
$id = $_GET['id'] ?? 0;

// Get contract details
try {
    $stmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ?");
    $stmt->execute([$id]);
    $contract = $stmt->fetch();
    
    if (!$contract) {
        redirect('/hrm/admin/contracts/index.php', 'error', 'Contract not found');
    }
    
    // Get employees, departments, and positions for dropdowns
    $empStmt = $pdo->query("SELECT id, employee_code, CONCAT(first_name, ' ', last_name) as full_name FROM employees WHERE employment_status = 'Active' ORDER BY first_name");
    $employees = $empStmt->fetchAll();
    
    $deptStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $deptStmt->fetchAll();
    
    $posStmt = $pdo->query("SELECT * FROM positions ORDER BY position_name");
    $positions = $posStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    redirect('/hrm/admin/contracts/index.php', 'error', 'Error fetching contract details');
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-edit me-2"></i> Edit Contract</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/contracts/index.php">Contracts</a></li>
                    <li class="breadcrumb-item active">Edit Contract</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/contracts/view.php?id=<?php echo $contract['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Details
            </a>
        </div>
    </div>
</div>

<!-- Edit Contract Form -->
<div class="row">
    <div class="col-12">
        <form action="/hrm/admin/contracts/process_edit.php" method="POST" enctype="multipart/form-data" id="editContractForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $contract['id']; ?>">
            <input type="hidden" name="old_file" value="<?php echo htmlspecialchars($contract['contract_file'] ?? ''); ?>">
            
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
                                   value="<?php echo htmlspecialchars($contract['contract_number']); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>" <?php echo ($emp['id'] == $contract['employee_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['employee_code'] . ' - ' . $emp['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_type" name="contract_type" required>
                                <option value="">Select Type</option>
                                <option value="Permanent" <?php echo ($contract['contract_type'] == 'Permanent') ? 'selected' : ''; ?>>Permanent</option>
                                <option value="Contract" <?php echo ($contract['contract_type'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                                <option value="Probation" <?php echo ($contract['contract_type'] == 'Probation') ? 'selected' : ''; ?>>Probation</option>
                                <option value="Internship" <?php echo ($contract['contract_type'] == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                                <option value="Freelance" <?php echo ($contract['contract_type'] == 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo $contract['start_date']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?php echo $contract['end_date'] ?? ''; ?>">
                            <small class="text-muted">Leave empty for permanent contracts</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="contract_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_status" name="contract_status" required>
                                <option value="Active" <?php echo ($contract['contract_status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Expired" <?php echo ($contract['contract_status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                                <option value="Terminated" <?php echo ($contract['contract_status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                                <option value="Renewed" <?php echo ($contract['contract_status'] == 'Renewed') ? 'selected' : ''; ?>>Renewed</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contract_file" class="form-label">Contract File (PDF)</label>
                            <input type="file" class="form-control" id="contract_file" name="contract_file" accept=".pdf">
                            <?php if ($contract['contract_file']): ?>
                                <small class="text-muted">
                                    Current: <a href="/hrm/uploads/contracts/<?php echo htmlspecialchars($contract['contract_file']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($contract['contract_file']); ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="salary" class="form-label">Salary (Rp)</label>
                            <input type="number" class="form-control" id="salary" name="salary" 
                                   value="<?php echo $contract['salary'] ?? ''; ?>" 
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
                            <input type="text" class="form-control" id="job_title" name="job_title"
                                   value="<?php echo htmlspecialchars($contract['job_title'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($dept['id'] == $contract['department_id']) ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $pos['id']; ?>" <?php echo ($pos['id'] == $contract['position_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pos['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($contract['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="/hrm/admin/contracts/view.php?id=<?php echo $contract['id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Contract
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
    $('#editContractForm').on('submit', function(e) {
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
    
    // Trigger on load
    if ($('#contract_type').val() === 'Permanent') {
        $('#end_date').prop('disabled', true);
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
