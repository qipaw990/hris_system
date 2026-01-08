<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require login
requireLogin();

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - HRIS Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/hrm/assets/css/style.css">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary fixed-top">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="/hrm/admin/index.php">
                <i class="fas fa-building me-2"></i>
                <span class="fw-bold">HRIS System</span>
            </a>
            
            <!-- Sidebar Toggle -->
            <button class="btn btn-link text-white d-lg-none" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Right Side Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Notifications -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notificationDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" 
                            aria-labelledby="notificationDropdown">
                            <li class="dropdown-header">
                                <i class="fas fa-bell me-2"></i> Notifications
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user-plus text-success me-2"></i>
                                    <div class="d-inline-block">
                                        <strong>New Employee</strong>
                                        <small class="d-block text-muted">John Doe joined today</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-calendar text-warning me-2"></i>
                                    <div class="d-inline-block">
                                        <strong>Leave Request</strong>
                                        <small class="d-block text-muted">3 pending requests</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-birthday-cake text-info me-2"></i>
                                    <div class="d-inline-block">
                                        <strong>Birthday</strong>
                                        <small class="d-block text-muted">2 birthdays this week</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center text-primary" href="#">
                                    View All Notifications
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- User Profile -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                           id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                            <div class="d-none d-md-block">
                                <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                <small class="text-white-50"><?php echo ucfirst($_SESSION['role']); ?></small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li class="dropdown-header">
                                <i class="fas fa-user me-2"></i> 
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/hrm/admin/profile.php">
                                    <i class="fas fa-user-edit me-2"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/hrm/admin/settings.php">
                                    <i class="fas fa-cog me-2"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="/hrm/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
