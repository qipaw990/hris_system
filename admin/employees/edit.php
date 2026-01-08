<?php
$page_title = 'Edit Employee';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get employee ID
$id = $_GET['id'] ?? 0;

// Get employee details
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect('/hrm/admin/employees/index.php', 'error', 'Employee not found');
    }
    
    // Get departments and positions
    $deptStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $deptStmt->fetchAll();
    
    $posStmt = $pdo->query("SELECT * FROM positions ORDER BY position_name");
    $positions = $posStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    redirect('/hrm/admin/employees/index.php', 'error', 'Error fetching employee details');
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-edit me-2"></i> Edit Employee</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/employees/index.php">Employees</a></li>
                    <li class="breadcrumb-item active">Edit Employee</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/employees/view.php?id=<?php echo $employee['id']; ?>" class="btn btn-info">
                <i class="fas fa-eye me-2"></i> View Details
            </a>
            <a href="/hrm/admin/employees/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>
</div>

<!-- Edit Employee Form -->
<div class="row">
    <div class="col-12">
        <form action="/hrm/admin/employees/process_edit.php" method="POST" enctype="multipart/form-data" id="editEmployeeForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
            <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($employee['photo']); ?>">
            
            <!-- Personal Information -->
            <div class="card fade-in mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i> Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($employee['phone']); ?>" 
                                   placeholder="+62-xxx-xxxx-xxxx">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?php echo htmlspecialchars($employee['date_of_birth']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo $employee['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $employee['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $employee['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" 
                                   onchange="previewImage(this, '#photoPreview')">
                            <small class="text-muted">Leave empty to keep current photo</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($employee['address']); ?></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Photo</label>
                            <div class="image-preview" id="photoPreview">
                                <?php if ($employee['photo']): ?>
                                    <img src="/hrm/uploads/employees/<?php echo htmlspecialchars($employee['photo']); ?>" alt="Photo">
                                <?php else: ?>
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employment Information -->
            <div class="card fade-in mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i> Employment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="employee_code" class="form-label">Employee Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="employee_code" name="employee_code" 
                                   value="<?php echo htmlspecialchars($employee['employee_code']); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" 
                                            <?php echo $employee['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $pos['id']; ?>" 
                                            <?php echo $employee['position_id'] == $pos['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pos['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                   value="<?php echo htmlspecialchars($employee['hire_date']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="employment_status" class="form-label">Employment Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="employment_status" name="employment_status" required>
                                <option value="Active" <?php echo $employee['employment_status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $employee['employment_status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="On Leave" <?php echo $employee['employment_status'] === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                                <option value="Terminated" <?php echo $employee['employment_status'] === 'Terminated' ? 'selected' : ''; ?>>Terminated</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="salary" class="form-label">Salary (Rp)</label>
                            <input type="number" class="form-control" id="salary" name="salary" 
                                   value="<?php echo htmlspecialchars($employee['salary']); ?>" 
                                   placeholder="0" min="0" step="1000">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="/hrm/admin/employees/index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Employee
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
    $('#editEmployeeForm').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
