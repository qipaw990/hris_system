-- Leave Management Module
-- Add leave tables to HRIS database

USE hris_db;

-- Table: leave_types
CREATE TABLE IF NOT EXISTS leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leave_name VARCHAR(100) NOT NULL,
    max_days INT NOT NULL DEFAULT 12,
    description TEXT,
    is_paid BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: leave_requests
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert leave types
INSERT INTO leave_types (leave_name, max_days, description, is_paid) VALUES
('Cuti Tahunan', 12, 'Cuti tahunan yang dibayar', TRUE),
('Cuti Sakit', 14, 'Cuti karena sakit dengan surat dokter', TRUE),
('Cuti Menikah', 3, 'Cuti untuk pernikahan karyawan', TRUE),
('Cuti Melahirkan', 90, 'Cuti melahirkan untuk karyawan wanita', TRUE),
('Cuti Besar', 7, 'Cuti besar setelah 6 tahun bekerja', TRUE),
('Izin Tidak Dibayar', 30, 'Izin tanpa gaji', FALSE);

-- Insert sample leave requests
INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status, approved_by, approved_at) VALUES
-- Approved leaves
(1, 1, '2026-01-15', '2026-01-17', 3, 'Liburan keluarga', 'Approved', 1, '2026-01-05 10:00:00'),
(2, 2, '2026-01-10', '2026-01-12', 3, 'Sakit demam', 'Approved', 1, '2026-01-09 14:30:00'),
(3, 1, '2026-01-20', '2026-01-22', 3, 'Acara keluarga', 'Approved', 1, '2026-01-06 09:15:00'),

-- Pending leaves
(4, 1, '2026-01-25', '2026-01-27', 3, 'Keperluan pribadi', 'Pending', NULL, NULL),
(5, 2, '2026-01-18', '2026-01-19', 2, 'Kontrol kesehatan', 'Pending', NULL, NULL),

-- Rejected leave
(6, 1, '2026-01-08', '2026-01-10', 3, 'Liburan', 'Rejected', 1, '2026-01-07 11:00:00');

-- Create indexes for better performance
CREATE INDEX idx_leave_employee ON leave_requests(employee_id);
CREATE INDEX idx_leave_status ON leave_requests(status);
CREATE INDEX idx_leave_dates ON leave_requests(start_date, end_date);
CREATE INDEX idx_leave_type ON leave_requests(leave_type_id);
