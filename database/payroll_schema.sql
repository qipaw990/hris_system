-- Payroll Management Module
-- Add payroll tables to HRIS database

USE hris_db;

-- Table: payroll_components (Komponen Gaji)
CREATE TABLE IF NOT EXISTS payroll_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    component_name VARCHAR(100) NOT NULL,
    component_type ENUM('Earning', 'Deduction') NOT NULL,
    calculation_type ENUM('Fixed', 'Percentage', 'Formula') NOT NULL,
    default_amount DECIMAL(15,2) DEFAULT 0,
    is_taxable BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_payroll_config (Konfigurasi Gaji per Karyawan)
CREATE TABLE IF NOT EXISTS employee_payroll_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    component_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES payroll_components(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payroll_periods (Periode Penggajian)
CREATE TABLE IF NOT EXISTS payroll_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_name VARCHAR(100) NOT NULL,
    period_month INT NOT NULL,
    period_year INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    payment_date DATE NOT NULL,
    status ENUM('Draft', 'Processed', 'Paid', 'Closed') DEFAULT 'Draft',
    total_employees INT DEFAULT 0,
    total_gross DECIMAL(15,2) DEFAULT 0,
    total_deductions DECIMAL(15,2) DEFAULT 0,
    total_net DECIMAL(15,2) DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_period (period_month, period_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payroll_slips (Slip Gaji)
CREATE TABLE IF NOT EXISTS payroll_slips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_id INT NOT NULL,
    employee_id INT NOT NULL,
    basic_salary DECIMAL(15,2) NOT NULL,
    total_earnings DECIMAL(15,2) NOT NULL,
    total_deductions DECIMAL(15,2) NOT NULL,
    net_salary DECIMAL(15,2) NOT NULL,
    attendance_days INT DEFAULT 0,
    working_days INT DEFAULT 0,
    overtime_hours DECIMAL(5,2) DEFAULT 0,
    late_count INT DEFAULT 0,
    status ENUM('Draft', 'Approved', 'Paid') DEFAULT 'Draft',
    payment_date DATE NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_period (employee_id, period_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payroll_slip_details (Detail Komponen Slip Gaji)
CREATE TABLE IF NOT EXISTS payroll_slip_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slip_id INT NOT NULL,
    component_id INT NOT NULL,
    component_name VARCHAR(100) NOT NULL,
    component_type ENUM('Earning', 'Deduction') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (slip_id) REFERENCES payroll_slips(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES payroll_components(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default payroll components
INSERT INTO payroll_components (component_name, component_type, calculation_type, default_amount, is_taxable, description) VALUES
-- Earnings
('Gaji Pokok', 'Earning', 'Fixed', 0, TRUE, 'Gaji pokok karyawan'),
('Tunjangan Transportasi', 'Earning', 'Fixed', 500000, TRUE, 'Tunjangan transportasi bulanan'),
('Tunjangan Makan', 'Earning', 'Fixed', 750000, TRUE, 'Tunjangan makan bulanan'),
('Tunjangan Kesehatan', 'Earning', 'Fixed', 300000, TRUE, 'Tunjangan kesehatan'),
('Tunjangan Keluarga', 'Earning', 'Percentage', 10, TRUE, 'Tunjangan keluarga 10% dari gaji pokok'),
('Bonus Kinerja', 'Earning', 'Fixed', 0, TRUE, 'Bonus berdasarkan kinerja'),
('Lembur', 'Earning', 'Formula', 0, TRUE, 'Pembayaran lembur'),

-- Deductions
('BPJS Kesehatan', 'Deduction', 'Percentage', 1, FALSE, 'Potongan BPJS Kesehatan 1%'),
('BPJS Ketenagakerjaan', 'Deduction', 'Percentage', 2, FALSE, 'Potongan BPJS Ketenagakerjaan 2%'),
('PPh 21', 'Deduction', 'Percentage', 5, FALSE, 'Pajak Penghasilan Pasal 21'),
('Potongan Keterlambatan', 'Deduction', 'Formula', 0, FALSE, 'Potongan karena terlambat'),
('Pinjaman Karyawan', 'Deduction', 'Fixed', 0, FALSE, 'Cicilan pinjaman karyawan'),
('Potongan Alpha', 'Deduction', 'Formula', 0, FALSE, 'Potongan karena tidak hadir tanpa keterangan');

-- Create indexes
CREATE INDEX idx_payroll_config_employee ON employee_payroll_config(employee_id);
CREATE INDEX idx_payroll_config_component ON employee_payroll_config(component_id);
CREATE INDEX idx_payroll_period_status ON payroll_periods(status);
CREATE INDEX idx_payroll_slip_period ON payroll_slips(period_id);
CREATE INDEX idx_payroll_slip_employee ON payroll_slips(employee_id);
CREATE INDEX idx_payroll_slip_status ON payroll_slips(status);
