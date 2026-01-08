-- Work Shift Management System Database Schema

-- Table: work_shifts
-- Stores shift definitions (Pagi, Siang, Malam, etc.)
CREATE TABLE IF NOT EXISTS work_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
    shift_code VARCHAR(20) UNIQUE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    grace_period_minutes INT DEFAULT 15 COMMENT 'Toleransi keterlambatan dalam menit',
    shift_allowance DECIMAL(10,2) DEFAULT 0 COMMENT 'Tunjangan shift',
    is_night_shift BOOLEAN DEFAULT FALSE COMMENT 'Shift malam mendapat tunjangan ekstra',
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_code (shift_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_shifts
-- Assigns shifts to employees
CREATE TABLE IF NOT EXISTS employee_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    shift_id INT NOT NULL,
    effective_date DATE NOT NULL COMMENT 'Tanggal mulai shift',
    end_date DATE NULL COMMENT 'Tanggal akhir (NULL = permanent)',
    is_permanent BOOLEAN DEFAULT TRUE,
    notes TEXT,
    assigned_by INT COMMENT 'User ID yang assign',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_id) REFERENCES work_shifts(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_shift (shift_id),
    INDEX idx_dates (effective_date, end_date),
    INDEX idx_active (employee_id, effective_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add shift_id to attendance table
ALTER TABLE attendance 
ADD COLUMN shift_id INT AFTER employee_id,
ADD FOREIGN KEY (shift_id) REFERENCES work_shifts(id) ON DELETE SET NULL;

-- Sample shift data
INSERT INTO work_shifts (shift_name, shift_code, start_time, end_time, grace_period_minutes, shift_allowance, is_night_shift, description) VALUES
('Shift Pagi', 'PAGI', '08:00:00', '17:00:00', 15, 0, FALSE, 'Shift kerja pagi standar'),
('Shift Siang', 'SIANG', '14:00:00', '22:00:00', 15, 50000, FALSE, 'Shift kerja siang dengan tunjangan'),
('Shift Malam', 'MALAM', '22:00:00', '06:00:00', 15, 100000, TRUE, 'Shift kerja malam dengan tunjangan ekstra');

-- Verify
SELECT * FROM work_shifts;
