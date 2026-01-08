<?php
$page_title = 'System Settings';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

// Get all settings grouped by category
try {
    $settingsStmt = $pdo->query("SELECT * FROM system_settings ORDER BY setting_category, setting_key");
    $allSettings = $settingsStmt->fetchAll();
    
    // Group by category
    $settings = [];
    foreach ($allSettings as $setting) {
        $settings[$setting['setting_category']][$setting['setting_key']] = $setting['setting_value'];
    }
    
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    $settings = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-cog me-2"></i> System Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Pengaturan</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Settings Tabs -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#attendance">
                            <i class="fas fa-clock me-2"></i> Jam Kerja & Kehadiran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#leave">
                            <i class="fas fa-calendar-times me-2"></i> Kebijakan Cuti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#payroll">
                            <i class="fas fa-money-bill-wave me-2"></i> Penggajian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#general">
                            <i class="fas fa-building me-2"></i> Umum
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#notification">
                            <i class="fas fa-bell me-2"></i> Notifikasi
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Attendance Settings -->
                    <div class="tab-pane fade show active" id="attendance">
                        <form action="/hrm/admin/settings/update.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="category" value="attendance">
                            
                            <h5 class="mb-3"><i class="fas fa-clock me-2"></i> Pengaturan Jam Kerja</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Masuk Kerja</label>
                                    <input type="time" name="work_start_time" class="form-control" 
                                           value="<?php echo $settings['attendance']['work_start_time'] ?? '08:00'; ?>">
                                    <small class="text-muted">Jam mulai kerja standar</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Pulang Kerja</label>
                                    <input type="time" name="work_end_time" class="form-control" 
                                           value="<?php echo $settings['attendance']['work_end_time'] ?? '17:00'; ?>">
                                    <small class="text-muted">Jam selesai kerja standar</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Mulai Istirahat</label>
                                    <input type="time" name="break_start_time" class="form-control" 
                                           value="<?php echo $settings['attendance']['break_start_time'] ?? '12:00'; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Selesai Istirahat</label>
                                    <input type="time" name="break_end_time" class="form-control" 
                                           value="<?php echo $settings['attendance']['break_end_time'] ?? '13:00'; ?>">
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4"><i class="fas fa-user-clock me-2"></i> Toleransi & Aturan</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Toleransi Keterlambatan (Menit)</label>
                                    <input type="number" name="late_tolerance_minutes" class="form-control" 
                                           value="<?php echo $settings['attendance']['late_tolerance_minutes'] ?? '15'; ?>">
                                    <small class="text-muted">Menit toleransi sebelum dianggap terlambat</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Toleransi Pulang Cepat (Menit)</label>
                                    <input type="number" name="early_leave_tolerance_minutes" class="form-control" 
                                           value="<?php echo $settings['attendance']['early_leave_tolerance_minutes'] ?? '15'; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hari Kerja Per Minggu</label>
                                    <input type="number" name="working_days_per_week" class="form-control" 
                                           value="<?php echo $settings['attendance']['working_days_per_week'] ?? '5'; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hari Libur Akhir Pekan</label>
                                    <input type="text" name="weekend_days" class="form-control" 
                                           value="<?php echo $settings['attendance']['weekend_days'] ?? 'Saturday,Sunday'; ?>">
                                    <small class="text-muted">Pisahkan dengan koma (Saturday,Sunday)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Multiplier Lembur</label>
                                    <input type="number" name="overtime_multiplier" class="form-control" step="0.1"
                                           value="<?php echo $settings['attendance']['overtime_multiplier'] ?? '1.5'; ?>">
                                    <small class="text-muted">Pengali untuk perhitungan lembur</small>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Leave Settings -->
                    <div class="tab-pane fade" id="leave">
                        <form action="/hrm/admin/settings/update.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="category" value="leave">
                            
                            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Jatah Cuti</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cuti Tahunan (Hari)</label>
                                    <input type="number" name="annual_leave_days" class="form-control" 
                                           value="<?php echo $settings['leave']['annual_leave_days'] ?? '12'; ?>">
                                    <small class="text-muted">Jumlah cuti tahunan per tahun</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cuti Sakit (Hari)</label>
                                    <input type="number" name="sick_leave_days" class="form-control" 
                                           value="<?php echo $settings['leave']['sick_leave_days'] ?? '12'; ?>">
                                    <small class="text-muted">Jumlah cuti sakit per tahun</small>
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4"><i class="fas fa-rules me-2"></i> Aturan Pengajuan</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimal Hari Sebelum Cuti</label>
                                    <input type="number" name="min_days_before_leave" class="form-control" 
                                           value="<?php echo $settings['leave']['min_days_before_leave'] ?? '3'; ?>">
                                    <small class="text-muted">Minimal hari sebelum mengajukan cuti</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maksimal Cuti Berturut-turut (Hari)</label>
                                    <input type="number" name="max_consecutive_leave_days" class="form-control" 
                                           value="<?php echo $settings['leave']['max_consecutive_leave_days'] ?? '14'; ?>">
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4"><i class="fas fa-forward me-2"></i> Carry Forward</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Izinkan Carry Forward Cuti</label>
                                    <select name="carry_forward_leave" class="form-select">
                                        <option value="1" <?php echo ($settings['leave']['carry_forward_leave'] ?? '1') == '1' ? 'selected' : ''; ?>>Ya</option>
                                        <option value="0" <?php echo ($settings['leave']['carry_forward_leave'] ?? '1') == '0' ? 'selected' : ''; ?>>Tidak</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maksimal Carry Forward (Hari)</label>
                                    <input type="number" name="max_carry_forward_days" class="form-control" 
                                           value="<?php echo $settings['leave']['max_carry_forward_days'] ?? '5'; ?>">
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Payroll Settings -->
                    <div class="tab-pane fade" id="payroll">
                        <form action="/hrm/admin/settings/update.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="category" value="payroll">
                            
                            <h5 class="mb-3"><i class="fas fa-calendar-check me-2"></i> Periode Penggajian</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Periode Penggajian</label>
                                    <select name="payroll_period" class="form-select">
                                        <option value="monthly" <?php echo ($settings['payroll']['payroll_period'] ?? 'monthly') == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                                        <option value="biweekly" <?php echo ($settings['payroll']['payroll_period'] ?? 'monthly') == 'biweekly' ? 'selected' : ''; ?>>Dua Minggu Sekali</option>
                                        <option value="weekly" <?php echo ($settings['payroll']['payroll_period'] ?? 'monthly') == 'weekly' ? 'selected' : ''; ?>>Mingguan</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Pembayaran Gaji</label>
                                    <input type="number" name="payroll_day" class="form-control" min="1" max="31"
                                           value="<?php echo $settings['payroll']['payroll_day'] ?? '25'; ?>">
                                    <small class="text-muted">Tanggal pembayaran setiap bulan</small>
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4"><i class="fas fa-percent me-2"></i> Pajak & Potongan</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Persentase Pajak (%)</label>
                                    <input type="number" name="tax_percentage" class="form-control" step="0.1"
                                           value="<?php echo $settings['payroll']['tax_percentage'] ?? '5'; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Persentase Asuransi (%)</label>
                                    <input type="number" name="insurance_percentage" class="form-control" step="0.1"
                                           value="<?php echo $settings['payroll']['insurance_percentage'] ?? '2'; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Potongan Keterlambatan (Rp)</label>
                                    <input type="number" name="late_deduction_amount" class="form-control" 
                                           value="<?php echo $settings['payroll']['late_deduction_amount'] ?? '50000'; ?>">
                                    <small class="text-muted">Potongan per keterlambatan</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipe Potongan Alpha</label>
                                    <select name="absence_deduction_type" class="form-select">
                                        <option value="daily_salary" <?php echo ($settings['payroll']['absence_deduction_type'] ?? 'daily_salary') == 'daily_salary' ? 'selected' : ''; ?>>Gaji Harian</option>
                                        <option value="fixed_amount" <?php echo ($settings['payroll']['absence_deduction_type'] ?? 'daily_salary') == 'fixed_amount' ? 'selected' : ''; ?>>Jumlah Tetap</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- General Settings -->
                    <div class="tab-pane fade" id="general">
                        <form action="/hrm/admin/settings/update.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="category" value="general">
                            
                            <h5 class="mb-3"><i class="fas fa-building me-2"></i> Informasi Perusahaan</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Perusahaan</label>
                                    <input type="text" name="company_name" class="form-control" 
                                           value="<?php echo $settings['general']['company_name'] ?? 'PT. HRIS Indonesia'; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Perusahaan</label>
                                    <input type="email" name="company_email" class="form-control" 
                                           value="<?php echo $settings['general']['company_email'] ?? 'info@hris.com'; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" name="company_phone" class="form-control" 
                                           value="<?php echo $settings['general']['company_phone'] ?? '+62 21 1234567'; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" name="company_address" class="form-control" 
                                           value="<?php echo $settings['general']['company_address'] ?? 'Jakarta, Indonesia'; ?>">
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4"><i class="fas fa-globe me-2"></i> Preferensi Sistem</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Timezone</label>
                                    <select name="timezone" class="form-select">
                                        <option value="Asia/Jakarta" <?php echo ($settings['general']['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : ''; ?>>Asia/Jakarta (WIB)</option>
                                        <option value="Asia/Makassar" <?php echo ($settings['general']['timezone'] ?? 'Asia/Jakarta') == 'Asia/Makassar' ? 'selected' : ''; ?>>Asia/Makassar (WITA)</option>
                                        <option value="Asia/Jayapura" <?php echo ($settings['general']['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jayapura' ? 'selected' : ''; ?>>Asia/Jayapura (WIT)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Format Tanggal</label>
                                    <select name="date_format" class="form-select">
                                        <option value="d/m/Y" <?php echo ($settings['general']['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                        <option value="m/d/Y" <?php echo ($settings['general']['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                        <option value="Y-m-d" <?php echo ($settings['general']['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mata Uang</label>
                                    <select name="currency" class="form-select">
                                        <option value="IDR" <?php echo ($settings['general']['currency'] ?? 'IDR') == 'IDR' ? 'selected' : ''; ?>>IDR (Rupiah)</option>
                                        <option value="USD" <?php echo ($settings['general']['currency'] ?? 'IDR') == 'USD' ? 'selected' : ''; ?>>USD (Dollar)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bahasa</label>
                                    <select name="language" class="form-select">
                                        <option value="id" <?php echo ($settings['general']['language'] ?? 'id') == 'id' ? 'selected' : ''; ?>>Indonesia</option>
                                        <option value="en" <?php echo ($settings['general']['language'] ?? 'id') == 'en' ? 'selected' : ''; ?>>English</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="tab-pane fade" id="notification">
                        <form action="/hrm/admin/settings/update.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="category" value="notification">
                            
                            <h5 class="mb-3"><i class="fas fa-bell me-2"></i> Pengaturan Notifikasi</h5>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" value="1" 
                                           <?php echo ($settings['notification']['email_notifications'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <strong>Aktifkan Notifikasi Email</strong><br>
                                        <small class="text-muted">Master switch untuk semua notifikasi email</small>
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="leave_approval_notification" value="1" 
                                           <?php echo ($settings['notification']['leave_approval_notification'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <strong>Notifikasi Persetujuan Cuti</strong><br>
                                        <small class="text-muted">Kirim email saat cuti disetujui/ditolak</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="payroll_notification" value="1" 
                                           <?php echo ($settings['notification']['payroll_notification'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <strong>Notifikasi Slip Gaji</strong><br>
                                        <small class="text-muted">Kirim email saat slip gaji tersedia</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="birthday_notification" value="1" 
                                           <?php echo ($settings['notification']['birthday_notification'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <strong>Notifikasi Ulang Tahun</strong><br>
                                        <small class="text-muted">Kirim ucapan ulang tahun ke karyawan</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
