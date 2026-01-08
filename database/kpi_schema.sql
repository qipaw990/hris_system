-- KPI Management System Schema
-- HRIS Management System

USE hris_db;

-- Table: kpi_categories
CREATE TABLE IF NOT EXISTS kpi_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    weight DECIMAL(5,2) DEFAULT 0 COMMENT 'Weight in percentage (0-100)',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: kpi_indicators
CREATE TABLE IF NOT EXISTS kpi_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    indicator_name VARCHAR(200) NOT NULL,
    description TEXT,
    measurement_type ENUM('Numeric', 'Percentage', 'Rating', 'Boolean') DEFAULT 'Numeric',
    target_value DECIMAL(10,2) DEFAULT 0,
    weight DECIMAL(5,2) DEFAULT 0 COMMENT 'Weight within category (0-100)',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES kpi_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_kpi_assignments
CREATE TABLE IF NOT EXISTS employee_kpi_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    indicator_id INT NOT NULL,
    target_value DECIMAL(10,2) NULL COMMENT 'Custom target for this employee (overrides default)',
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    assigned_by INT NOT NULL COMMENT 'User ID who assigned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (indicator_id) REFERENCES kpi_indicators(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_indicator (indicator_id),
    INDEX idx_period (period_start, period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: kpi_evaluations
CREATE TABLE IF NOT EXISTS kpi_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    employee_id INT NOT NULL,
    indicator_id INT NOT NULL,
    period VARCHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
    actual_value DECIMAL(10,2) DEFAULT 0,
    score DECIMAL(5,2) DEFAULT 0 COMMENT 'Calculated score (0-100)',
    self_assessment TEXT,
    manager_assessment TEXT,
    status ENUM('Draft', 'Self-Assessed', 'Manager-Reviewed', 'Approved') DEFAULT 'Draft',
    evaluated_by INT NULL COMMENT 'User ID who evaluated',
    evaluated_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES employee_kpi_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (indicator_id) REFERENCES kpi_indicators(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_period (period),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample KPI categories
INSERT INTO kpi_categories (category_name, description, weight) VALUES
('Sales Performance', 'Indikator kinerja penjualan dan revenue', 30.00),
('Customer Service', 'Indikator kepuasan dan layanan pelanggan', 25.00),
('Quality & Productivity', 'Indikator kualitas kerja dan produktivitas', 25.00),
('Teamwork & Collaboration', 'Indikator kerjasama tim dan kolaborasi', 10.00),
('Professional Development', 'Indikator pengembangan diri dan kompetensi', 10.00);

-- Insert sample KPI indicators for Sales Performance
INSERT INTO kpi_indicators (category_id, indicator_name, description, measurement_type, target_value, weight) VALUES
(1, 'Monthly Sales Target', 'Pencapaian target penjualan bulanan', 'Percentage', 100.00, 40.00),
(1, 'New Customer Acquisition', 'Jumlah pelanggan baru per bulan', 'Numeric', 10.00, 30.00),
(1, 'Revenue Growth', 'Pertumbuhan revenue dibanding bulan sebelumnya', 'Percentage', 10.00, 30.00);

-- Insert sample KPI indicators for Customer Service
INSERT INTO kpi_indicators (category_id, indicator_name, description, measurement_type, target_value, weight) VALUES
(2, 'Customer Satisfaction Score', 'Skor kepuasan pelanggan (CSAT)', 'Rating', 4.50, 40.00),
(2, 'Response Time', 'Rata-rata waktu respon (dalam menit)', 'Numeric', 15.00, 30.00),
(2, 'Issue Resolution Rate', 'Persentase masalah yang terselesaikan', 'Percentage', 95.00, 30.00);

-- Insert sample KPI indicators for Quality & Productivity
INSERT INTO kpi_indicators (category_id, indicator_name, description, measurement_type, target_value, weight) VALUES
(3, 'Task Completion Rate', 'Persentase tugas yang diselesaikan tepat waktu', 'Percentage', 95.00, 35.00),
(3, 'Error Rate', 'Persentase kesalahan dalam pekerjaan', 'Percentage', 5.00, 30.00),
(3, 'Output Quality Score', 'Skor kualitas hasil kerja', 'Rating', 4.00, 35.00);

-- Insert sample KPI indicators for Teamwork
INSERT INTO kpi_indicators (category_id, indicator_name, description, measurement_type, target_value, weight) VALUES
(4, 'Team Collaboration Score', 'Skor kolaborasi dengan tim', 'Rating', 4.00, 50.00),
(4, 'Meeting Attendance', 'Persentase kehadiran dalam meeting tim', 'Percentage', 95.00, 50.00);

-- Insert sample KPI indicators for Professional Development
INSERT INTO kpi_indicators (category_id, indicator_name, description, measurement_type, target_value, weight) VALUES
(5, 'Training Completion', 'Jumlah training yang diselesaikan', 'Numeric', 4.00, 50.00),
(5, 'Skill Improvement', 'Skor peningkatan kompetensi', 'Rating', 4.00, 50.00);

-- Sample KPI assignments (assign to first 3 employees for demonstration)
INSERT INTO employee_kpi_assignments (employee_id, indicator_id, target_value, period_start, period_end, assigned_by) 
SELECT 
    e.id,
    ki.id,
    ki.target_value,
    '2026-01-01',
    '2026-12-31',
    1
FROM employees e
CROSS JOIN kpi_indicators ki
WHERE e.id IN (1, 2, 3)
AND ki.is_active = 1
LIMIT 45;

-- Sample evaluations for current month
INSERT INTO kpi_evaluations (assignment_id, employee_id, indicator_id, period, actual_value, score, status)
SELECT 
    eka.id,
    eka.employee_id,
    eka.indicator_id,
    DATE_FORMAT(CURDATE(), '%Y-%m'),
    CASE 
        WHEN ki.measurement_type = 'Percentage' THEN ROUND(RAND() * 100, 2)
        WHEN ki.measurement_type = 'Rating' THEN ROUND(RAND() * 5, 2)
        WHEN ki.measurement_type = 'Numeric' THEN ROUND(RAND() * ki.target_value * 1.2, 2)
        ELSE 1
    END as actual_value,
    ROUND(RAND() * 100, 2) as score,
    'Draft'
FROM employee_kpi_assignments eka
JOIN kpi_indicators ki ON eka.indicator_id = ki.id
WHERE eka.employee_id IN (1, 2, 3)
LIMIT 30;
