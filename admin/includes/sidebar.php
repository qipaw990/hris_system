    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">
                <i class="fas fa-bars me-2"></i> Menu
            </h5>
        </div>
        
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="/hrm/admin/index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Beranda</span>
                </a>
            </li>
            
            <!-- Employee Management -->
            <li class="menu-item has-submenu <?php echo (strpos($current_page, 'employee') !== false) ? 'active' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="fas fa-users"></i>
                    <span>Manajemen Karyawan</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="/hrm/admin/employees/index.php">
                            <i class="fas fa-list"></i> Semua Karyawan
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/employees/add.php">
                            <i class="fas fa-user-plus"></i> Tambah Karyawan
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Department Management -->
            <li class="menu-item <?php echo ($current_page == 'departments.php') ? 'active' : ''; ?>">
                <a href="/hrm/admin/departments/index.php">
                    <i class="fas fa-building"></i>
                    <span>Departemen</span>
                </a>
            </li>
            
            <!-- Position Management -->
            <li class="menu-item <?php echo ($current_page == 'positions.php') ? 'active' : ''; ?>">
                <a href="/hrm/admin/positions/index.php">
                    <i class="fas fa-briefcase"></i>
                    <span>Jabatan</span>
                </a>
            </li>
            
            <!-- Contract Management -->
            <li class="menu-item <?php echo (strpos($current_page, 'contract') !== false) ? 'active' : ''; ?>">
                <a href="/hrm/admin/contracts/index.php">
                    <i class="fas fa-file-contract"></i>
                    <span>Kontrak</span>
                </a>
            </li>
            
            <!-- Office Locations -->
            <li class="menu-item <?php echo (strpos($current_page, 'location') !== false) ? 'active' : ''; ?>">
                <a href="/hrm/admin/locations/index.php">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Lokasi Kantor</span>
                </a>
            </li>
            
            <!-- Work Shifts -->
            <li class="menu-item <?php echo (strpos($current_page, 'shifts') !== false) ? 'active' : ''; ?>">
                <a href="/hrm/admin/shifts/index.php">
                    <i class="fas fa-clock"></i>
                    <span>Shift Kerja</span>
                </a>
            </li>
            
            <!-- Attendance -->
            <li class="menu-item has-submenu <?php echo (strpos($current_page, 'attendance') !== false) ? 'active' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="fas fa-calendar-check"></i>
                    <span>Kehadiran</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="/hrm/admin/attendance/index.php">
                            <i class="fas fa-list"></i> Data Kehadiran
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/attendance/corrections/index.php">
                            <i class="fas fa-user-edit"></i> Koreksi Kehadiran
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/attendance/auto_absent.php">
                            <i class="fas fa-user-clock"></i> Auto-Absent
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Leave Management -->
            <li class="menu-item has-submenu <?php echo (strpos($current_page, 'leave') !== false) ? 'active' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <i class="fas fa-calendar-times"></i>
                    <span>Cuti & Izin</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="/hrm/admin/leave/index.php">
                            <i class="fas fa-umbrella-beach"></i> Cuti
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/leave/sick-permission/index.php">
                            <i class="fas fa-notes-medical"></i> Sakit & Izin
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Payroll -->
            <li class="menu-item">
                <a href="/hrm/admin/payroll/index.php">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Penggajian</span>
                </a>
            </li>
            
            <!-- Recruitment -->
            <li class="menu-item has-submenu">
                <a href="#" class="submenu-toggle">
                    <i class="fas fa-user-tie"></i>
                    <span>Recruitment</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="/hrm/admin/recruitment/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/recruitment/jobs/">
                            <i class="fas fa-briefcase"></i> Job Postings
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/recruitment/applicants/">
                            <i class="fas fa-users"></i> Applicants
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/recruitment/interviews/">
                            <i class="fas fa-calendar-alt"></i> Interviews
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- KPI Management -->
            <li class="menu-item has-submenu">
                <a href="#" class="submenu-toggle">
                    <i class="fas fa-chart-line"></i>
                    <span>KPI Management</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="/hrm/admin/kpi/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> KPI Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/kpi/categories/">
                            <i class="fas fa-layer-group"></i> Categories
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/kpi/indicators/">
                            <i class="fas fa-chart-line"></i> Indicators
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/kpi/evaluations/">
                            <i class="fas fa-clipboard-check"></i> Evaluations
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports -->
            <li class="menu-item has-submenu">
                <a href="#" class="submenu-toggle">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="/hrm/admin/reports/employee.php">
                            <i class="fas fa-users"></i> Laporan Karyawan
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/reports/attendance.php">
                            <i class="fas fa-calendar"></i> Laporan Kehadiran
                        </a>
                    </li>
                    <li>
                        <a href="/hrm/admin/reports/payroll.php">
                            <i class="fas fa-dollar-sign"></i> Laporan Penggajian
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- User Management (Admin Only) -->
            <?php if ($_SESSION['role'] == 'Admin'): ?>
            <li class="menu-item <?php echo (strpos($current_page, 'users') !== false) ? 'active' : ''; ?>">
                <a href="/hrm/admin/users/index.php">
                    <i class="fas fa-users-cog"></i>
                    <span>Manajemen User</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Settings (Admin Only) -->
            <?php if ($_SESSION['role'] == 'Admin'): ?>
            <li class="menu-item">
                <a href="/hrm/admin/settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
