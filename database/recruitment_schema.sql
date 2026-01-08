-- Recruitment Module Schema
-- HRIS Management System

USE hris_db;

-- Table: job_postings
CREATE TABLE IF NOT EXISTS job_postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(200) NOT NULL,
    department_id INT,
    position_id INT,
    job_description TEXT,
    requirements TEXT,
    responsibilities TEXT,
    salary_range VARCHAR(100),
    employment_type ENUM('Full-Time', 'Part-Time', 'Contract', 'Internship') DEFAULT 'Full-Time',
    location VARCHAR(200),
    vacancies INT DEFAULT 1,
    status ENUM('Open', 'Closed', 'On Hold') DEFAULT 'Open',
    posted_date DATE,
    closing_date DATE,
    posted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_posted_date (posted_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: applicants
CREATE TABLE IF NOT EXISTS applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    education_level VARCHAR(100),
    years_of_experience INT DEFAULT 0,
    current_company VARCHAR(200),
    current_position VARCHAR(200),
    expected_salary DECIMAL(15,2),
    resume_file VARCHAR(255),
    cover_letter TEXT,
    linkedin_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: job_applications
CREATE TABLE IF NOT EXISTS job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    applicant_id INT NOT NULL,
    application_date DATE NOT NULL,
    status ENUM('Applied', 'Screening', 'Interview', 'Offered', 'Hired', 'Rejected') DEFAULT 'Applied',
    notes TEXT,
    rating INT DEFAULT 0 COMMENT 'Rating 1-5',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_postings(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_job (job_id),
    INDEX idx_applicant (applicant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: interviews
CREATE TABLE IF NOT EXISTS interviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    interview_type ENUM('Phone', 'Video', 'In-Person', 'Technical', 'HR') DEFAULT 'In-Person',
    interview_date DATE NOT NULL,
    interview_time TIME NOT NULL,
    location VARCHAR(255),
    meeting_link VARCHAR(255),
    interviewer_name VARCHAR(200),
    interviewer_id INT,
    status ENUM('Scheduled', 'Completed', 'Cancelled', 'Rescheduled') DEFAULT 'Scheduled',
    feedback TEXT,
    rating INT DEFAULT 0 COMMENT 'Rating 1-5',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
    INDEX idx_date (interview_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample job postings
INSERT INTO job_postings (job_title, department_id, position_id, job_description, requirements, responsibilities, salary_range, employment_type, location, vacancies, status, posted_date, closing_date, posted_by) VALUES
('Senior Software Engineer', 1, 1, 'We are looking for an experienced Senior Software Engineer to join our development team.', 
'- Bachelor degree in Computer Science\n- 5+ years experience in software development\n- Proficient in PHP, JavaScript, MySQL\n- Experience with Laravel/React', 
'- Design and develop web applications\n- Code review and mentoring\n- Collaborate with cross-functional teams', 
'Rp 15,000,000 - Rp 20,000,000', 'Full-Time', 'Jakarta', 2, 'Open', '2026-01-01', '2026-02-01', 1),

('HR Manager', 2, 2, 'Seeking an experienced HR Manager to lead our human resources department.', 
'- Bachelor degree in HR Management\n- 7+ years experience in HR\n- Strong leadership skills\n- Knowledge of labor laws', 
'- Develop HR strategies\n- Manage recruitment process\n- Handle employee relations', 
'Rp 12,000,000 - Rp 18,000,000', 'Full-Time', 'Jakarta', 1, 'Open', '2026-01-05', '2026-02-05', 1),

('Marketing Specialist', 3, 3, 'Looking for a creative Marketing Specialist to drive our marketing campaigns.', 
'- Bachelor degree in Marketing\n- 3+ years experience\n- Digital marketing expertise\n- Strong communication skills', 
'- Plan and execute marketing campaigns\n- Manage social media\n- Analyze marketing metrics', 
'Rp 8,000,000 - Rp 12,000,000', 'Full-Time', 'Jakarta', 1, 'Open', '2026-01-03', '2026-01-31', 1);

-- Insert sample applicants
INSERT INTO applicants (first_name, last_name, email, phone, education_level, years_of_experience, current_company, current_position, expected_salary) VALUES
('Ahmad', 'Wijaya', 'ahmad.wijaya@email.com', '081234567890', 'Bachelor', 6, 'Tech Corp', 'Software Engineer', 18000000),
('Siti', 'Nurhaliza', 'siti.nur@email.com', '081234567891', 'Master', 8, 'HR Solutions', 'HR Supervisor', 15000000),
('Budi', 'Santoso', 'budi.santoso@email.com', '081234567892', 'Bachelor', 4, 'Digital Agency', 'Marketing Executive', 10000000),
('Dewi', 'Lestari', 'dewi.lestari@email.com', '081234567893', 'Bachelor', 5, 'StartUp Inc', 'Full Stack Developer', 16000000);

-- Insert sample applications
INSERT INTO job_applications (job_id, applicant_id, application_date, status, rating) VALUES
(1, 1, '2026-01-06', 'Interview', 4),
(1, 4, '2026-01-07', 'Screening', 3),
(2, 2, '2026-01-08', 'Interview', 5),
(3, 3, '2026-01-05', 'Offered', 4);

-- Insert sample interviews
INSERT INTO interviews (application_id, interview_type, interview_date, interview_time, location, interviewer_name, status) VALUES
(1, 'Technical', '2026-01-15', '10:00:00', 'Office - Meeting Room A', 'John Doe', 'Scheduled'),
(3, 'HR', '2026-01-16', '14:00:00', 'Office - HR Department', 'Jane Smith', 'Scheduled');
