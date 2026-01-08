-- Contract Management Module
-- Add contracts table to HRIS database

USE hris_db;

-- Table: contracts
CREATE TABLE IF NOT EXISTS contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    contract_number VARCHAR(50) NOT NULL UNIQUE,
    contract_type ENUM('Permanent', 'Contract', 'Probation', 'Internship', 'Freelance') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    salary DECIMAL(12, 2),
    job_title VARCHAR(100),
    department_id INT,
    position_id INT,
    contract_status ENUM('Active', 'Expired', 'Terminated', 'Renewed') DEFAULT 'Active',
    contract_file VARCHAR(255),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample contracts
INSERT INTO contracts (employee_id, contract_number, contract_type, start_date, end_date, salary, job_title, department_id, position_id, contract_status) VALUES
(1, 'CTR-2020-001', 'Permanent', '2020-01-15', NULL, 25000000.00, 'Chief Technology Officer', 2, 2, 'Active'),
(2, 'CTR-2019-002', 'Permanent', '2019-05-10', NULL, 18000000.00, 'HR Manager', 1, 3, 'Active'),
(3, 'CTR-2021-003', 'Contract', '2021-03-20', '2024-03-20', 15000000.00, 'Senior Developer', 2, 4, 'Active'),
(4, 'CTR-2022-004', 'Permanent', '2022-02-14', NULL, 16000000.00, 'Marketing Manager', 4, 6, 'Active'),
(5, 'CTR-2020-005', 'Contract', '2020-08-05', '2023-08-05', 14000000.00, 'Sales Executive', 5, 7, 'Expired'),
(6, 'CTR-2021-006', 'Permanent', '2021-06-18', NULL, 13000000.00, 'Accountant', 3, 8, 'Active'),
(7, 'CTR-2023-007', 'Probation', '2023-01-10', '2023-04-10', 10000000.00, 'Junior Developer', 2, 5, 'Active'),
(8, 'CTR-2022-008', 'Contract', '2022-09-22', '2024-09-22', 17000000.00, 'Operations Manager', 6, 9, 'Active');

-- Create indexes for better performance
CREATE INDEX idx_contract_number ON contracts(contract_number);
CREATE INDEX idx_contract_employee ON contracts(employee_id);
CREATE INDEX idx_contract_status ON contracts(contract_status);
CREATE INDEX idx_contract_type ON contracts(contract_type);
CREATE INDEX idx_contract_dates ON contracts(start_date, end_date);
