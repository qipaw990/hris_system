<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HRIS Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .register-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .register-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 28px;
        }
        
        .register-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .register-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #667eea;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 15px;
            font-size: 14px;
        }
        
        .password-toggle {
            cursor: pointer;
            color: #667eea;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #764ba2;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2>Create Account</h2>
                <p>HRIS Management System</p>
            </div>
            
            <div class="register-body">
                <?php
                session_start();
                
                // Display flash message
                if (isset($_SESSION['flash_message'])) {
                    $flash = $_SESSION['flash_message'];
                    $alertClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
                    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                            <i class="fas fa-' . ($flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle') . ' me-2"></i>
                            ' . htmlspecialchars($flash['message']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
                    unset($_SESSION['flash_message']);
                }
                ?>
                
                <form action="/hrm/auth/process_register.php" method="POST" id="registerForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i> Username
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Enter username" required minlength="3">
                        </div>
                        <small class="text-muted">Minimum 3 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i> Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter email address" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i> Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter password" required minlength="6">
                            <span class="input-group-text password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </span>
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i> Confirm Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm password" required minlength="6">
                            <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">
                            <i class="fas fa-user-tag me-1"></i> Role
                        </label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="hr">HR Manager</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i> Create Account
                    </button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="/hrm/login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>
