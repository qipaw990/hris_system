-- Attendance Management Module
-- Add attendance table to HRIS database

USE hris_db;

-- Table: attendance
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    status ENUM('Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Cuti') DEFAULT 'Hadir',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (employee_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample attendance data for current month
INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, status) VALUES
-- Employee 1 - Full attendance
(1, '2026-01-02', '08:00:00', '17:00:00', 'Hadir'),
(1, '2026-01-03', '08:05:00', '17:05:00', 'Hadir'),
(1, '2026-01-06', '08:00:00', '17:00:00', 'Hadir'),
(1, '2026-01-07', '08:00:00', NULL, 'Hadir'),

-- Employee 2 - Some late
(2, '2026-01-02', '08:30:00', '17:00:00', 'Terlambat'),
(2, '2026-01-03', '08:00:00', '17:00:00', 'Hadir'),
(2, '2026-01-06', '08:45:00', '17:00:00', 'Terlambat'),
(2, '2026-01-07', '08:00:00', NULL, 'Hadir'),

-- Employee 3 - Mixed
(3, '2026-01-02', '08:00:00', '17:00:00', 'Hadir'),
(3, '2026-01-03', NULL, NULL, 'Sakit'),
(3, '2026-01-06', '08:00:00', '17:00:00', 'Hadir'),
(3, '2026-01-07', '08:00:00', NULL, 'Hadir'),

-- Employee 4
(4, '2026-01-02', '08:00:00', '17:00:00', 'Hadir'),
(4, '2026-01-03', '08:00:00', '17:00:00', 'Hadir'),
(4, '2026-01-06', NULL, NULL, 'Izin'),
(4, '2026-01-07', '08:00:00', NULL, 'Hadir'),

-- Employee 5
(5, '2026-01-02', '08:15:00', '17:00:00', 'Terlambat'),
(5, '2026-01-03', NULL, NULL, 'Alpha'),
(5, '2026-01-06', '08:00:00', '17:00:00', 'Hadir'),
(5, '2026-01-07', '08:00:00', NULL, 'Hadir'),

-- Employee 6
(6, '2026-01-02', '08:00:00', '17:00:00', 'Hadir'),
(6, '2026-01-03', '08:00:00', '17:00:00', 'Hadir'),
(6, '2026-01-06', '08:00:00', '17:00:00', 'Hadir'),
(6, '2026-01-07', '08:00:00', NULL, 'Hadir');

-- Create indexes for better performance
CREATE INDEX idx_attendance_date ON attendance(attendance_date);
CREATE INDEX idx_attendance_employee ON attendance(employee_id);
CREATE INDEX idx_attendance_status ON attendance(status);
CREATE INDEX idx_attendance_month ON attendance(attendance_date, employee_id);
