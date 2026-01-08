-- Attendance Correction Requests Schema
-- Allows employees to request attendance correction for forgotten check-ins

CREATE TABLE IF NOT EXISTS attendance_correction_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    request_date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    reason TEXT NOT NULL,
    proof_attachment VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_request (employee_id, request_date, status),
    INDEX idx_status (status),
    INDEX idx_employee (employee_id),
    INDEX idx_request_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing (optional)
-- INSERT INTO attendance_correction_requests 
-- (employee_id, request_date, check_in_time, check_out_time, reason, status)
-- VALUES 
-- (1, '2026-01-06', '08:30:00', '17:00:00', 'Lupa check-in karena meeting urgent di luar kantor', 'Pending');
