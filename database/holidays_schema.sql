-- Holidays Table for Auto-Absent System
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_name VARCHAR(100) NOT NULL,
    holiday_date DATE NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_holiday_date (holiday_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample holidays for 2026
INSERT INTO holidays (holiday_name, holiday_date, description) VALUES
('Tahun Baru 2026', '2026-01-01', 'Tahun Baru Masehi'),
('Tahun Baru Imlek', '2026-02-17', 'Tahun Baru Imlek 2577 Kongzili'),
('Isra Miraj', '2026-03-11', 'Isra Miraj Nabi Muhammad SAW'),
('Hari Raya Nyepi', '2026-03-22', 'Tahun Baru Saka 1948'),
('Wafat Yesus Kristus', '2026-04-03', 'Wafat Yesus Kristus'),
('Idul Fitri', '2026-04-20', 'Hari Raya Idul Fitri 1447 H'),
('Idul Fitri (Hari Kedua)', '2026-04-21', 'Hari Raya Idul Fitri 1447 H'),
('Hari Buruh', '2026-05-01', 'Hari Buruh Internasional'),
('Kenaikan Yesus Kristus', '2026-05-14', 'Kenaikan Yesus Kristus'),
('Hari Raya Waisak', '2026-06-01', 'Hari Raya Waisak 2570'),
('Hari Lahir Pancasila', '2026-06-01', 'Hari Lahir Pancasila'),
('Idul Adha', '2026-06-27', 'Hari Raya Idul Adha 1447 H'),
('Tahun Baru Islam', '2026-07-18', 'Tahun Baru Islam 1448 H'),
('Hari Kemerdekaan RI', '2026-08-17', 'Hari Kemerdekaan Republik Indonesia'),
('Maulid Nabi Muhammad SAW', '2026-09-26', 'Maulid Nabi Muhammad SAW'),
('Hari Natal', '2026-12-25', 'Hari Natal');

-- Activity Logs Table (if not exists)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for better performance
CREATE INDEX idx_holiday_date ON holidays(holiday_date);
CREATE INDEX idx_activity_user ON activity_logs(user_id);
CREATE INDEX idx_activity_created ON activity_logs(created_at);
