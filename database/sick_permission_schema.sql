-- Sick and Permission Requests Table (Separate from Leave Requests)
-- This table is specifically for short-term sick leave and permission requests

CREATE TABLE IF NOT EXISTS sick_permission_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    request_type ENUM('Sakit', 'Izin') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT NOT NULL,
    attachment VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_request_type (request_type),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing (optional)
-- INSERT INTO sick_permission_requests 
-- (employee_id, request_type, start_date, end_date, total_days, reason, status)
-- VALUES 
-- (1, 'Sakit', '2026-01-06', '2026-01-08', 3, 'Demam dan flu', 'Pending'),
-- (1, 'Izin', '2026-01-05', '2026-01-05', 1, 'Keperluan keluarga', 'Approved');
