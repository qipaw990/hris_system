-- HRIS Management System Database Schema
-- Created: 2026-01-07

-- Create Database
CREATE DATABASE IF NOT EXISTS hris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hris_db;

-- Table: departments
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: positions
CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL,
    position_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr', 'employee') DEFAULT 'employee',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employees
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address TEXT,
    department_id INT,
    position_id INT,
    hire_date DATE NOT NULL,
    employment_status ENUM('Active', 'Inactive', 'On Leave', 'Terminated') DEFAULT 'Active',
    salary DECIMAL(12, 2),
    photo VARCHAR(255),
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Departments
INSERT INTO departments (department_name, department_code, description) VALUES
('Human Resources', 'HR', 'Manages employee relations, recruitment, and benefits'),
('Information Technology', 'IT', 'Manages technology infrastructure and software development'),
('Finance', 'FIN', 'Handles financial planning, accounting, and reporting'),
('Marketing', 'MKT', 'Responsible for marketing strategies and brand management'),
('Sales', 'SLS', 'Manages sales operations and customer relationships'),
('Operations', 'OPS', 'Oversees daily business operations and logistics');

-- Insert Sample Positions
INSERT INTO positions (position_name, position_code, description) VALUES
('Chief Executive Officer', 'CEO', 'Top executive responsible for overall company operations'),
('Chief Technology Officer', 'CTO', 'Oversees technology strategy and development'),
('HR Manager', 'HRM', 'Manages human resources department'),
('Senior Developer', 'SDEV', 'Experienced software developer'),
('Junior Developer', 'JDEV', 'Entry-level software developer'),
('Marketing Manager', 'MKTM', 'Leads marketing initiatives'),
('Sales Executive', 'SLSE', 'Handles sales and client relations'),
('Accountant', 'ACC', 'Manages financial records and reporting'),
('Operations Manager', 'OPM', 'Oversees operational activities'),
('Administrative Assistant', 'ADMIN', 'Provides administrative support');

-- Insert Default Admin User
-- Password: admin123 (hashed with PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, role, is_active) VALUES
('admin', 'admin@hris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('hr_manager', 'hr@hris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', 1);

-- Insert Sample Employees
INSERT INTO employees (employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, department_id, position_id, hire_date, employment_status, salary) VALUES
('EMP001', 'John', 'Doe', 'john.doe@company.com', '+62-812-3456-7890', '1985-03-15', 'Male', 'Jl. Sudirman No. 123, Jakarta', 2, 2, '2020-01-15', 'Active', 25000000.00),
('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+62-813-4567-8901', '1990-07-22', 'Female', 'Jl. Thamrin No. 45, Jakarta', 1, 3, '2019-05-10', 'Active', 18000000.00),
('EMP003', 'Michael', 'Johnson', 'michael.j@company.com', '+62-814-5678-9012', '1988-11-30', 'Male', 'Jl. Gatot Subroto No. 67, Jakarta', 2, 4, '2021-03-20', 'Active', 15000000.00),
('EMP004', 'Sarah', 'Williams', 'sarah.w@company.com', '+62-815-6789-0123', '1992-05-18', 'Female', 'Jl. Rasuna Said No. 89, Jakarta', 4, 6, '2022-02-14', 'Active', 16000000.00),
('EMP005', 'David', 'Brown', 'david.b@company.com', '+62-816-7890-1234', '1987-09-25', 'Male', 'Jl. Kuningan No. 12, Jakarta', 5, 7, '2020-08-05', 'Active', 14000000.00),
('EMP006', 'Emily', 'Davis', 'emily.d@company.com', '+62-817-8901-2345', '1991-12-08', 'Female', 'Jl. Senopati No. 34, Jakarta', 3, 8, '2021-06-18', 'Active', 13000000.00),
('EMP007', 'Robert', 'Miller', 'robert.m@company.com', '+62-818-9012-3456', '1989-04-12', 'Male', 'Jl. Menteng No. 56, Jakarta', 2, 5, '2023-01-10', 'Active', 10000000.00),
('EMP008', 'Lisa', 'Anderson', 'lisa.a@company.com', '+62-819-0123-4567', '1993-08-20', 'Female', 'Jl. Kemang No. 78, Jakarta', 6, 9, '2022-09-22', 'Active', 17000000.00);

-- Create indexes for better performance
CREATE INDEX idx_employee_code ON employees(employee_code);
CREATE INDEX idx_employee_status ON employees(employment_status);
CREATE INDEX idx_employee_department ON employees(department_id);
CREATE INDEX idx_employee_position ON employees(position_id);
CREATE INDEX idx_user_username ON users(username);
CREATE INDEX idx_user_email ON users(email);
