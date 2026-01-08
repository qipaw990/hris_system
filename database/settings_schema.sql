-- System Settings Schema
-- HRIS Management System

USE hris_db;

-- Table: system_settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_category VARCHAR(50) DEFAULT 'general',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (setting_category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings

-- Work Hours & Attendance Settings
INSERT INTO system_settings (setting_key, setting_value, setting_category, description) VALUES
('work_start_time', '08:00', 'attendance', 'Jam masuk kerja (format HH:MM)'),
('work_end_time', '17:00', 'attendance', 'Jam pulang kerja (format HH:MM)'),
('late_tolerance_minutes', '15', 'attendance', 'Toleransi keterlambatan dalam menit'),
('early_leave_tolerance_minutes', '15', 'attendance', 'Toleransi pulang cepat dalam menit'),
('working_days_per_week', '5', 'attendance', 'Jumlah hari kerja per minggu'),
('break_start_time', '12:00', 'attendance', 'Jam mulai istirahat'),
('break_end_time', '13:00', 'attendance', 'Jam selesai istirahat'),
('overtime_multiplier', '1.5', 'attendance', 'Multiplier untuk perhitungan lembur'),
('weekend_days', 'Saturday,Sunday', 'attendance', 'Hari libur akhir pekan (comma separated)');

-- Leave Policy Settings
INSERT INTO system_settings (setting_key, setting_value, setting_category, description) VALUES
('annual_leave_days', '12', 'leave', 'Jumlah cuti tahunan per tahun'),
('sick_leave_days', '12', 'leave', 'Jumlah cuti sakit per tahun'),
('min_days_before_leave', '3', 'leave', 'Minimal hari sebelum mengajukan cuti'),
('max_consecutive_leave_days', '14', 'leave', 'Maksimal hari cuti berturut-turut'),
('carry_forward_leave', '1', 'leave', 'Izinkan carry forward cuti (1=yes, 0=no)'),
('max_carry_forward_days', '5', 'leave', 'Maksimal hari cuti yang bisa di-carry forward');

-- Payroll Settings
INSERT INTO system_settings (setting_key, setting_value, setting_category, description) VALUES
('payroll_period', 'monthly', 'payroll', 'Periode penggajian (monthly/biweekly/weekly)'),
('payroll_day', '25', 'payroll', 'Tanggal pembayaran gaji'),
('tax_percentage', '5', 'payroll', 'Persentase pajak penghasilan'),
('insurance_percentage', '2', 'payroll', 'Persentase potongan asuransi'),
('late_deduction_amount', '50000', 'payroll', 'Potongan per keterlambatan (Rp)'),
('absence_deduction_type', 'daily_salary', 'payroll', 'Tipe potongan alpha (daily_salary/fixed_amount)');

-- General System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_category, description) VALUES
('company_name', 'PT. HRIS Indonesia', 'general', 'Nama perusahaan'),
('company_address', 'Jakarta, Indonesia', 'general', 'Alamat perusahaan'),
('company_phone', '+62 21 1234567', 'general', 'Nomor telepon perusahaan'),
('company_email', 'info@hris.com', 'general', 'Email perusahaan'),
('timezone', 'Asia/Jakarta', 'general', 'Timezone sistem'),
('date_format', 'd/m/Y', 'general', 'Format tanggal'),
('currency', 'IDR', 'general', 'Mata uang'),
('language', 'id', 'general', 'Bahasa sistem (id/en)');

-- Notification Settings
INSERT INTO system_settings (setting_key, setting_value, setting_category, description) VALUES
('email_notifications', '1', 'notification', 'Aktifkan notifikasi email (1=yes, 0=no)'),
('leave_approval_notification', '1', 'notification', 'Notifikasi persetujuan cuti'),
('payroll_notification', '1', 'notification', 'Notifikasi slip gaji'),
('birthday_notification', '1', 'notification', 'Notifikasi ulang tahun karyawan');
