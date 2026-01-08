-- Update users table structure for user management
ALTER TABLE users 
MODIFY COLUMN role ENUM('Admin', 'HR', 'Employee') DEFAULT 'Employee';

-- Add status column if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status ENUM('Active', 'Inactive') DEFAULT 'Active';

-- Add employee_id column if not exists (for linking user to employee)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS employee_id INT NULL;

-- Add foreign key constraint
ALTER TABLE users 
ADD CONSTRAINT fk_users_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL;

-- Add indexes for performance
ALTER TABLE users 
ADD INDEX idx_role (role),
ADD INDEX idx_status (status),
ADD INDEX idx_employee_id (employee_id);
