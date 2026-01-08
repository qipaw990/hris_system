-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Jan 2026 pada 14.27
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hris_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `applicants`
--

CREATE TABLE `applicants` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT 0,
  `current_company` varchar(200) DEFAULT NULL,
  `current_position` varchar(200) DEFAULT NULL,
  `expected_salary` decimal(15,2) DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `applicants`
--

INSERT INTO `applicants` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `date_of_birth`, `education_level`, `years_of_experience`, `current_company`, `current_position`, `expected_salary`, `resume_file`, `cover_letter`, `linkedin_url`, `portfolio_url`, `created_at`, `updated_at`) VALUES
(1, 'Ahmad', 'Wijaya', 'ahmad.wijaya@email.com', '081234567890', NULL, NULL, 'Bachelor', 6, 'Tech Corp', 'Software Engineer', 18000000.00, NULL, NULL, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(2, 'Siti', 'Nurhaliza', 'siti.nur@email.com', '081234567891', NULL, NULL, 'Master', 8, 'HR Solutions', 'HR Supervisor', 15000000.00, NULL, NULL, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(3, 'Budi', 'Santoso', 'budi.santoso@email.com', '081234567892', NULL, NULL, 'Bachelor', 4, 'Digital Agency', 'Marketing Executive', 10000000.00, NULL, NULL, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(4, 'Dewi', 'Lestari', 'dewi.lestari@email.com', '081234567893', NULL, NULL, 'Bachelor', 5, 'StartUp Inc', 'Full Stack Developer', 16000000.00, NULL, NULL, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `applicant_documents`
--

CREATE TABLE `applicant_documents` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `document_type` enum('Resume','Cover Letter','Certificate','Portfolio','ID Card','Other') NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `applicant_documents`
--

INSERT INTO `applicant_documents` (`id`, `applicant_id`, `document_type`, `document_name`, `file_path`, `file_size`, `uploaded_at`) VALUES
(1, 1, 'Resume', 'Ahmad_Wijaya_CV.pdf', 'uploads/resumes/ahmad_wijaya_cv.pdf', 245678, '2026-01-07 10:46:44'),
(2, 1, 'Cover Letter', 'Cover_Letter_Ahmad.pdf', 'uploads/cover_letters/cover_letter_ahmad.pdf', 89456, '2026-01-07 10:46:44'),
(3, 2, 'Resume', 'Siti_Nurhaliza_Resume.pdf', 'uploads/resumes/siti_nurhaliza_resume.pdf', 312456, '2026-01-07 10:46:44'),
(4, 3, 'Resume', 'Budi_Santoso_CV.pdf', 'uploads/resumes/budi_santoso_cv.pdf', 198765, '2026-01-07 10:46:44'),
(5, 3, 'Portfolio', 'Marketing_Portfolio.pdf', 'uploads/portfolios/marketing_portfolio.pdf', 567890, '2026-01-07 10:46:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('Hadir','Terlambat','Izin','Sakit','Alpha','Cuti') DEFAULT 'Hadir',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `check_in_latitude` decimal(10,8) DEFAULT NULL,
  `check_in_longitude` decimal(11,8) DEFAULT NULL,
  `check_out_latitude` decimal(10,8) DEFAULT NULL,
  `check_out_longitude` decimal(11,8) DEFAULT NULL,
  `office_location_id` int(11) DEFAULT NULL,
  `distance_meters` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `attendance_date`, `check_in`, `check_out`, `status`, `notes`, `created_at`, `updated_at`, `check_in_latitude`, `check_in_longitude`, `check_out_latitude`, `check_out_longitude`, `office_location_id`, `distance_meters`) VALUES
(1, 1, '2026-01-02', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, '2026-01-03', '08:05:00', '17:05:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, '2026-01-06', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, '2026-01-07', '08:00:00', NULL, 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 2, '2026-01-02', '08:30:00', '17:00:00', 'Terlambat', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 2, '2026-01-03', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 2, '2026-01-06', '08:45:00', '17:00:00', 'Terlambat', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 2, '2026-01-07', '08:00:00', NULL, 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 3, '2026-01-02', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 3, '2026-01-03', NULL, NULL, 'Sakit', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 3, '2026-01-06', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 3, '2026-01-07', '08:00:00', NULL, 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 4, '2026-01-02', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 4, '2026-01-03', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 4, '2026-01-06', NULL, NULL, 'Izin', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 4, '2026-01-07', '08:00:00', NULL, 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 5, '2026-01-02', '08:15:00', '17:00:00', 'Terlambat', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(18, 5, '2026-01-03', NULL, NULL, 'Alpha', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(19, 5, '2026-01-06', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(20, 5, '2026-01-07', '08:00:00', NULL, 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(21, 6, '2026-01-02', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(22, 6, '2026-01-03', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(23, 6, '2026-01-06', '08:00:00', '17:00:00', 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL),
(24, 6, '2026-01-07', '08:00:00', NULL, 'Hadir', NULL, '2026-01-07 09:19:06', '2026-01-07 09:19:06', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `contract_number` varchar(50) NOT NULL,
  `contract_type` enum('Permanent','Contract','Probation','Internship','Freelance') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `contract_status` enum('Active','Expired','Terminated','Renewed') DEFAULT 'Active',
  `contract_file` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `contracts`
--

INSERT INTO `contracts` (`id`, `employee_id`, `contract_number`, `contract_type`, `start_date`, `end_date`, `salary`, `job_title`, `department_id`, `position_id`, `contract_status`, `contract_file`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'CTR-2020-001', 'Permanent', '2020-01-15', NULL, 25000000.00, 'Chief Technology Officer', 2, 2, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(2, 2, 'CTR-2019-002', 'Permanent', '2019-05-10', NULL, 18000000.00, 'HR Manager', 1, 3, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(3, 3, 'CTR-2021-003', 'Contract', '2021-03-20', '2024-03-20', 15000000.00, 'Senior Developer', 2, 4, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(4, 4, 'CTR-2022-004', 'Permanent', '2022-02-14', NULL, 16000000.00, 'Marketing Manager', 4, 6, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(5, 5, 'CTR-2020-005', 'Contract', '2020-08-05', '2023-08-05', 14000000.00, 'Sales Executive', 5, 7, 'Expired', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(6, 6, 'CTR-2021-006', 'Permanent', '2021-06-18', NULL, 13000000.00, 'Accountant', 3, 8, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(7, 7, 'CTR-2023-007', 'Probation', '2023-01-10', '2023-04-10', 10000000.00, 'Junior Developer', 2, 5, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53'),
(8, 8, 'CTR-2022-008', 'Contract', '2022-09-22', '2024-09-22', 17000000.00, 'Operations Manager', 6, 9, 'Active', NULL, NULL, NULL, '2026-01-07 08:49:53', '2026-01-07 08:49:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Human Resources', 'HR', 'Manages employee relations, recruitment, and benefits', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(2, 'Information Technology', 'IT', 'Manages technology infrastructure and software development', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(3, 'Finance', 'FIN', 'Handles financial planning, accounting, and reporting', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(4, 'Marketing', 'MKT', 'Responsible for marketing strategies and brand management', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(5, 'Sales', 'SLS', 'Manages sales operations and customer relationships', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(6, 'Operations', 'OPS', 'Oversees daily business operations and logistics', '2026-01-07 08:22:28', '2026-01-07 08:22:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_code` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `employment_status` enum('Active','Inactive','On Leave','Terminated') DEFAULT 'Active',
  `basic_salary` decimal(15,2) DEFAULT 0.00,
  `salary` decimal(12,2) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`, `address`, `department_id`, `position_id`, `hire_date`, `employment_status`, `basic_salary`, `salary`, `photo`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'EMP001', 'John', 'Doe', 'john.doe@company.com', '+62-812-3456-7890', '1985-03-15', 'Male', 'Jl. Sudirman No. 123, Jakarta', 2, 2, '2020-01-15', 'Active', 7000000.00, 25000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(2, 'EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+62-813-4567-8901', '1990-07-22', 'Female', 'Jl. Thamrin No. 45, Jakarta', 1, 3, '2019-05-10', 'Active', 10000000.00, 18000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(3, 'EMP003', 'Michael', 'Johnson', 'michael.j@company.com', '+62-814-5678-9012', '1988-11-30', 'Male', 'Jl. Gatot Subroto No. 67, Jakarta', 2, 4, '2021-03-20', 'Active', 12000000.00, 15000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(4, 'EMP004', 'Sarah', 'Williams', 'sarah.w@company.com', '+62-815-6789-0123', '1992-05-18', 'Female', 'Jl. Rasuna Said No. 89, Jakarta', 4, 6, '2022-02-14', 'Active', 20000000.00, 16000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(5, 'EMP005', 'David', 'Brown', 'david.b@company.com', '+62-816-7890-1234', '1987-09-25', 'Male', 'Jl. Kuningan No. 12, Jakarta', 5, 7, '2020-08-05', 'Active', 5000000.00, 14000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(6, 'EMP006', 'Emily', 'Davis', 'emily.d@company.com', '+62-817-8901-2345', '1991-12-08', 'Female', 'Jl. Senopati No. 34, Jakarta', 3, 8, '2021-06-18', 'Active', 5000000.00, 13000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(7, 'EMP007', 'Robert', 'Miller', 'robert.m@company.com', '+62-818-9012-3456', '1989-04-12', 'Male', 'Jl. Menteng No. 56, Jakarta', 2, 5, '2023-01-10', 'Active', 15000000.00, 10000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39'),
(8, 'EMP008', 'Lisa', 'Anderson', 'lisa.a@company.com', '+62-819-0123-4567', '1993-08-20', 'Female', 'Jl. Kemang No. 78, Jakarta', 6, 9, '2022-09-22', 'Active', 5000000.00, 17000000.00, NULL, NULL, '2026-01-07 08:22:28', '2026-01-07 09:38:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `employee_kpi_assignments`
--

CREATE TABLE `employee_kpi_assignments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `indicator_id` int(11) NOT NULL,
  `target_value` decimal(10,2) DEFAULT NULL COMMENT 'Custom target for this employee (overrides default)',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `assigned_by` int(11) NOT NULL COMMENT 'User ID who assigned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `employee_kpi_assignments`
--

INSERT INTO `employee_kpi_assignments` (`id`, `employee_id`, `indicator_id`, `target_value`, `period_start`, `period_end`, `assigned_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 100.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(2, 2, 1, 100.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(3, 3, 1, 100.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(4, 1, 2, 10.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(5, 2, 2, 10.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(6, 3, 2, 10.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(7, 1, 3, 10.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(8, 2, 3, 10.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(9, 3, 3, 10.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(10, 1, 4, 4.50, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(11, 2, 4, 4.50, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(12, 3, 4, 4.50, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(13, 1, 5, 15.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(14, 2, 5, 15.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(15, 3, 5, 15.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(16, 1, 6, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(17, 2, 6, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(18, 3, 6, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(19, 1, 7, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(20, 2, 7, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(21, 3, 7, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(22, 1, 8, 5.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(23, 2, 8, 5.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(24, 3, 8, 5.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(25, 1, 9, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(26, 2, 9, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(27, 3, 9, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(28, 1, 10, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(29, 2, 10, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(30, 3, 10, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(31, 1, 11, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(32, 2, 11, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(33, 3, 11, 95.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(34, 1, 12, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(35, 2, 12, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(36, 3, 12, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(37, 1, 13, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(38, 2, 13, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(39, 3, 13, 4.00, '2026-01-01', '2026-12-31', 1, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `employee_payroll_config`
--

CREATE TABLE `employee_payroll_config` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `interview_type` enum('Phone','Video','In-Person','Technical','HR') DEFAULT 'In-Person',
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `interviewer_name` varchar(200) DEFAULT NULL,
  `interviewer_id` int(11) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled','Rescheduled') DEFAULT 'Scheduled',
  `feedback` text DEFAULT NULL,
  `rating` int(11) DEFAULT 0 COMMENT 'Rating 1-5',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `interviews`
--

INSERT INTO `interviews` (`id`, `application_id`, `interview_type`, `interview_date`, `interview_time`, `location`, `meeting_link`, `interviewer_name`, `interviewer_id`, `status`, `feedback`, `rating`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Technical', '2026-01-15', '10:00:00', 'Office - Meeting Room A', NULL, 'John Doe', NULL, 'Scheduled', NULL, 0, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(2, 3, 'HR', '2026-01-16', '14:00:00', 'Office - HR Department', NULL, 'Jane Smith', NULL, 'Scheduled', NULL, 0, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `status` enum('Applied','Screening','Interview','Offered','Hired','Rejected') DEFAULT 'Applied',
  `notes` text DEFAULT NULL,
  `rating` int(11) DEFAULT 0 COMMENT 'Rating 1-5',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `applicant_id`, `application_date`, `status`, `notes`, `rating`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-01-06', 'Interview', NULL, 4, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(2, 1, 4, '2026-01-07', 'Screening', NULL, 3, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(3, 2, 2, '2026-01-08', 'Interview', NULL, 5, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(4, 3, 3, '2026-01-05', 'Offered', NULL, 4, NULL, NULL, '2026-01-07 10:39:36', '2026-01-07 10:39:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_postings`
--

CREATE TABLE `job_postings` (
  `id` int(11) NOT NULL,
  `job_title` varchar(200) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `employment_type` enum('Full-Time','Part-Time','Contract','Internship') DEFAULT 'Full-Time',
  `location` varchar(200) DEFAULT NULL,
  `vacancies` int(11) DEFAULT 1,
  `status` enum('Open','Closed','On Hold') DEFAULT 'Open',
  `posted_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `job_postings`
--

INSERT INTO `job_postings` (`id`, `job_title`, `department_id`, `position_id`, `job_description`, `requirements`, `responsibilities`, `salary_range`, `employment_type`, `location`, `vacancies`, `status`, `posted_date`, `closing_date`, `posted_by`, `created_at`, `updated_at`) VALUES
(1, 'Senior Software Engineer', 1, 1, 'We are looking for an experienced Senior Software Engineer to join our development team.', '- Bachelor degree in Computer Science\n- 5+ years experience in software development\n- Proficient in PHP, JavaScript, MySQL\n- Experience with Laravel/React', '- Design and develop web applications\n- Code review and mentoring\n- Collaborate with cross-functional teams', 'Rp 15,000,000 - Rp 20,000,000', 'Full-Time', 'Jakarta', 2, 'Open', '2026-01-01', '2026-02-01', 1, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(2, 'HR Manager', 2, 2, 'Seeking an experienced HR Manager to lead our human resources department.', '- Bachelor degree in HR Management\n- 7+ years experience in HR\n- Strong leadership skills\n- Knowledge of labor laws', '- Develop HR strategies\n- Manage recruitment process\n- Handle employee relations', 'Rp 12,000,000 - Rp 18,000,000', 'Full-Time', 'Jakarta', 1, 'Open', '2026-01-05', '2026-02-05', 1, '2026-01-07 10:39:36', '2026-01-07 10:39:36'),
(3, 'Marketing Specialist', 3, 3, 'Looking for a creative Marketing Specialist to drive our marketing campaigns.', '- Bachelor degree in Marketing\n- 3+ years experience\n- Digital marketing expertise\n- Strong communication skills', '- Plan and execute marketing campaigns\n- Manage social media\n- Analyze marketing metrics', 'Rp 8,000,000 - Rp 12,000,000', 'Full-Time', 'Jakarta', 1, 'Open', '2026-01-03', '2026-01-31', 1, '2026-01-07 10:39:36', '2026-01-07 10:39:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kpi_categories`
--

CREATE TABLE `kpi_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT 0.00 COMMENT 'Weight in percentage (0-100)',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kpi_categories`
--

INSERT INTO `kpi_categories` (`id`, `category_name`, `description`, `weight`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sales Performance', 'Indikator kinerja penjualan dan revenue', 30.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(2, 'Customer Service', 'Indikator kepuasan dan layanan pelanggan', 25.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(3, 'Quality & Productivity', 'Indikator kualitas kerja dan produktivitas', 25.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(4, 'Teamwork & Collaboration', 'Indikator kerjasama tim dan kolaborasi', 10.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(5, 'Professional Development', 'Indikator pengembangan diri dan kompetensi', 10.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kpi_evaluations`
--

CREATE TABLE `kpi_evaluations` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `indicator_id` int(11) NOT NULL,
  `period` varchar(7) NOT NULL COMMENT 'Format: YYYY-MM',
  `actual_value` decimal(10,2) DEFAULT 0.00,
  `score` decimal(5,2) DEFAULT 0.00 COMMENT 'Calculated score (0-100)',
  `self_assessment` text DEFAULT NULL,
  `manager_assessment` text DEFAULT NULL,
  `status` enum('Draft','Self-Assessed','Manager-Reviewed','Approved') DEFAULT 'Draft',
  `evaluated_by` int(11) DEFAULT NULL COMMENT 'User ID who evaluated',
  `evaluated_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kpi_evaluations`
--

INSERT INTO `kpi_evaluations` (`id`, `assignment_id`, `employee_id`, `indicator_id`, `period`, `actual_value`, `score`, `self_assessment`, `manager_assessment`, `status`, `evaluated_by`, `evaluated_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-01', 15.95, 5.50, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(2, 2, 2, 1, '2026-01', 79.63, 81.67, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(3, 3, 3, 1, '2026-01', 69.43, 2.17, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(4, 4, 1, 2, '2026-01', 0.31, 6.31, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(5, 5, 2, 2, '2026-01', 2.86, 0.27, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(6, 6, 3, 2, '2026-01', 3.58, 48.42, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(7, 7, 1, 3, '2026-01', 52.57, 17.59, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(8, 8, 2, 3, '2026-01', 30.22, 98.35, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(9, 9, 3, 3, '2026-01', 1.06, 10.28, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(10, 10, 1, 4, '2026-01', 2.41, 10.16, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(11, 11, 2, 4, '2026-01', 0.31, 0.49, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(12, 12, 3, 4, '2026-01', 4.19, 17.99, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(13, 13, 1, 5, '2026-01', 6.89, 37.29, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(14, 14, 2, 5, '2026-01', 12.91, 46.71, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(15, 15, 3, 5, '2026-01', 3.31, 51.87, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(16, 16, 1, 6, '2026-01', 4.14, 65.11, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(17, 17, 2, 6, '2026-01', 13.14, 70.35, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(18, 18, 3, 6, '2026-01', 12.34, 50.66, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(19, 19, 1, 7, '2026-01', 16.29, 29.46, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(20, 20, 2, 7, '2026-01', 98.44, 3.79, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(21, 21, 3, 7, '2026-01', 23.65, 6.88, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(22, 22, 1, 8, '2026-01', 63.47, 96.69, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(23, 23, 2, 8, '2026-01', 93.03, 75.09, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(24, 24, 3, 8, '2026-01', 96.34, 56.44, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(25, 25, 1, 9, '2026-01', 4.66, 96.59, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(26, 26, 2, 9, '2026-01', 0.17, 27.28, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(27, 27, 3, 9, '2026-01', 1.31, 49.02, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(28, 28, 1, 10, '2026-01', 3.33, 85.87, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(29, 29, 2, 10, '2026-01', 1.48, 90.42, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(30, 30, 3, 10, '2026-01', 3.16, 45.05, NULL, NULL, 'Draft', NULL, NULL, NULL, '2026-01-07 09:53:41', '2026-01-07 09:53:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kpi_indicators`
--

CREATE TABLE `kpi_indicators` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `indicator_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `measurement_type` enum('Numeric','Percentage','Rating','Boolean') DEFAULT 'Numeric',
  `target_value` decimal(10,2) DEFAULT 0.00,
  `weight` decimal(5,2) DEFAULT 0.00 COMMENT 'Weight within category (0-100)',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kpi_indicators`
--

INSERT INTO `kpi_indicators` (`id`, `category_id`, `indicator_name`, `description`, `measurement_type`, `target_value`, `weight`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monthly Sales Target', 'Pencapaian target penjualan bulanan', 'Percentage', 100.00, 40.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(2, 1, 'New Customer Acquisition', 'Jumlah pelanggan baru per bulan', 'Numeric', 10.00, 30.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(3, 1, 'Revenue Growth', 'Pertumbuhan revenue dibanding bulan sebelumnya', 'Percentage', 10.00, 30.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(4, 2, 'Customer Satisfaction Score', 'Skor kepuasan pelanggan (CSAT)', 'Rating', 4.50, 40.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(5, 2, 'Response Time', 'Rata-rata waktu respon (dalam menit)', 'Numeric', 15.00, 30.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(6, 2, 'Issue Resolution Rate', 'Persentase masalah yang terselesaikan', 'Percentage', 95.00, 30.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(7, 3, 'Task Completion Rate', 'Persentase tugas yang diselesaikan tepat waktu', 'Percentage', 95.00, 35.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(8, 3, 'Error Rate', 'Persentase kesalahan dalam pekerjaan', 'Percentage', 5.00, 30.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(9, 3, 'Output Quality Score', 'Skor kualitas hasil kerja', 'Rating', 4.00, 35.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(10, 4, 'Team Collaboration Score', 'Skor kolaborasi dengan tim', 'Rating', 4.00, 50.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(11, 4, 'Meeting Attendance', 'Persentase kehadiran dalam meeting tim', 'Percentage', 95.00, 50.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(12, 5, 'Training Completion', 'Jumlah training yang diselesaikan', 'Numeric', 4.00, 50.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41'),
(13, 5, 'Skill Improvement', 'Skor peningkatan kompetensi', 'Rating', 4.00, 50.00, 1, '2026-01-07 09:53:41', '2026-01-07 09:53:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-01-15', '2026-01-17', 3, 'Liburan keluarga', 'Approved', 1, '2026-01-05 03:00:00', NULL, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(2, 2, 2, '2026-01-10', '2026-01-12', 3, 'Sakit demam', 'Approved', 1, '2026-01-09 07:30:00', NULL, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(3, 3, 1, '2026-01-20', '2026-01-22', 3, 'Acara keluarga', 'Approved', 1, '2026-01-06 02:15:00', NULL, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(4, 4, 1, '2026-01-25', '2026-01-27', 3, 'Keperluan pribadi', 'Pending', NULL, NULL, NULL, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(5, 5, 2, '2026-01-18', '2026-01-19', 2, 'Kontrol kesehatan', 'Pending', NULL, NULL, NULL, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(6, 6, 1, '2026-01-08', '2026-01-10', 3, 'Liburan', 'Rejected', 1, '2026-01-07 04:00:00', NULL, '2026-01-07 09:26:55', '2026-01-07 09:26:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `leave_name` varchar(100) NOT NULL,
  `max_days` int(11) NOT NULL DEFAULT 12,
  `description` text DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `leave_types`
--

INSERT INTO `leave_types` (`id`, `leave_name`, `max_days`, `description`, `is_paid`, `created_at`, `updated_at`) VALUES
(1, 'Cuti Tahunan', 12, 'Cuti tahunan yang dibayar', 1, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(2, 'Cuti Sakit', 14, 'Cuti karena sakit dengan surat dokter', 1, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(3, 'Cuti Menikah', 3, 'Cuti untuk pernikahan karyawan', 1, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(4, 'Cuti Melahirkan', 90, 'Cuti melahirkan untuk karyawan wanita', 1, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(5, 'Cuti Besar', 7, 'Cuti besar setelah 6 tahun bekerja', 1, '2026-01-07 09:26:55', '2026-01-07 09:26:55'),
(6, 'Izin Tidak Dibayar', 30, 'Izin tanpa gaji', 0, '2026-01-07 09:26:55', '2026-01-07 09:26:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `office_locations`
--

CREATE TABLE `office_locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_meters` int(11) DEFAULT 100,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `office_locations`
--

INSERT INTO `office_locations` (`id`, `location_name`, `address`, `latitude`, `longitude`, `radius_meters`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Kantor Pusat Jakarta', 'Jl. Sudirman No. 123, Jakarta Selatan', -6.20880000, 106.84560000, 100, 1, '2026-01-07 11:22:00', '2026-01-07 11:22:00'),
(2, 'Kantor Cabang Bandung', 'Jl. Asia Afrika No. 45, Bandung', -6.91750000, 107.61910000, 150, 1, '2026-01-07 11:22:00', '2026-01-07 11:22:00'),
(3, 'Kantor Cabang Surabaya', 'Jl. Tunjungan No. 78, Surabaya', -7.25750000, 112.75210000, 100, 1, '2026-01-07 11:22:00', '2026-01-07 11:22:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payroll_components`
--

CREATE TABLE `payroll_components` (
  `id` int(11) NOT NULL,
  `component_name` varchar(100) NOT NULL,
  `component_type` enum('Earning','Deduction') NOT NULL,
  `calculation_type` enum('Fixed','Percentage','Formula') NOT NULL,
  `default_amount` decimal(15,2) DEFAULT 0.00,
  `is_taxable` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payroll_components`
--

INSERT INTO `payroll_components` (`id`, `component_name`, `component_type`, `calculation_type`, `default_amount`, `is_taxable`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Gaji Pokok', 'Earning', 'Fixed', 0.00, 1, 1, 'Gaji pokok karyawan', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(2, 'Tunjangan Transportasi', 'Earning', 'Fixed', 500000.00, 1, 1, 'Tunjangan transportasi bulanan', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(3, 'Tunjangan Makan', 'Earning', 'Fixed', 750000.00, 1, 1, 'Tunjangan makan bulanan', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(4, 'Tunjangan Kesehatan', 'Earning', 'Fixed', 300000.00, 1, 1, 'Tunjangan kesehatan', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(5, 'Tunjangan Keluarga', 'Earning', 'Percentage', 10.00, 1, 1, 'Tunjangan keluarga 10% dari gaji pokok', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(6, 'Bonus Kinerja', 'Earning', 'Fixed', 0.00, 1, 1, 'Bonus berdasarkan kinerja', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(7, 'Lembur', 'Earning', 'Formula', 0.00, 1, 1, 'Pembayaran lembur', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(8, 'BPJS Kesehatan', 'Deduction', 'Percentage', 1.00, 0, 1, 'Potongan BPJS Kesehatan 1%', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(9, 'BPJS Ketenagakerjaan', 'Deduction', 'Percentage', 2.00, 0, 1, 'Potongan BPJS Ketenagakerjaan 2%', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(10, 'PPh 21', 'Deduction', 'Percentage', 5.00, 0, 1, 'Pajak Penghasilan Pasal 21', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(11, 'Potongan Keterlambatan', 'Deduction', 'Formula', 0.00, 0, 1, 'Potongan karena terlambat', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(12, 'Pinjaman Karyawan', 'Deduction', 'Fixed', 0.00, 0, 1, 'Cicilan pinjaman karyawan', '2026-01-07 09:29:59', '2026-01-07 09:29:59'),
(13, 'Potongan Alpha', 'Deduction', 'Formula', 0.00, 0, 1, 'Potongan karena tidak hadir tanpa keterangan', '2026-01-07 09:29:59', '2026-01-07 09:29:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `period_month` int(11) NOT NULL,
  `period_year` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_date` date NOT NULL,
  `status` enum('Draft','Processed','Paid','Closed') DEFAULT 'Draft',
  `total_employees` int(11) DEFAULT 0,
  `total_gross` decimal(15,2) DEFAULT 0.00,
  `total_deductions` decimal(15,2) DEFAULT 0.00,
  `total_net` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payroll_periods`
--

INSERT INTO `payroll_periods` (`id`, `period_name`, `period_month`, `period_year`, `start_date`, `end_date`, `payment_date`, `status`, `total_employees`, `total_gross`, `total_deductions`, `total_net`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 'January 2026', 1, 2026, '2026-01-01', '2026-01-31', '2026-01-07', 'Processed', 8, 99300000.00, 6631290.32, 92668709.68, 7, '2026-01-07 09:39:45', '2026-01-07 09:39:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payroll_slips`
--

CREATE TABLE `payroll_slips` (
  `id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `total_earnings` decimal(15,2) NOT NULL,
  `total_deductions` decimal(15,2) NOT NULL,
  `net_salary` decimal(15,2) NOT NULL,
  `attendance_days` int(11) DEFAULT 0,
  `working_days` int(11) DEFAULT 0,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `late_count` int(11) DEFAULT 0,
  `status` enum('Draft','Approved','Paid') DEFAULT 'Draft',
  `payment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payroll_slips`
--

INSERT INTO `payroll_slips` (`id`, `period_id`, `employee_id`, `basic_salary`, `total_earnings`, `total_deductions`, `net_salary`, `attendance_days`, `working_days`, `overtime_hours`, `late_count`, `status`, `payment_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 7000000.00, 9250000.00, 560000.00, 8690000.00, 4, 31, 0.00, 0, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(2, 4, 2, 10000000.00, 12550000.00, 900000.00, 11650000.00, 2, 31, 0.00, 2, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(3, 4, 3, 12000000.00, 14750000.00, 960000.00, 13790000.00, 3, 31, 0.00, 0, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(4, 4, 4, 20000000.00, 23550000.00, 1600000.00, 21950000.00, 3, 31, 0.00, 0, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(5, 4, 5, 5000000.00, 7050000.00, 611290.32, 6438709.68, 2, 31, 0.00, 1, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(6, 4, 6, 5000000.00, 7050000.00, 400000.00, 6650000.00, 4, 31, 0.00, 0, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(7, 4, 7, 15000000.00, 18050000.00, 1200000.00, 16850000.00, 0, 31, 0.00, 0, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45'),
(8, 4, 8, 5000000.00, 7050000.00, 400000.00, 6650000.00, 0, 31, 0.00, 0, 'Draft', NULL, NULL, '2026-01-07 09:39:45', '2026-01-07 09:39:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payroll_slip_details`
--

CREATE TABLE `payroll_slip_details` (
  `id` int(11) NOT NULL,
  `slip_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `component_name` varchar(100) NOT NULL,
  `component_type` enum('Earning','Deduction') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payroll_slip_details`
--

INSERT INTO `payroll_slip_details` (`id`, `slip_id`, `component_id`, `component_name`, `component_type`, `amount`, `created_at`) VALUES
(1, 1, 5, 'Tunjangan Keluarga', 'Earning', 700000.00, '2026-01-07 09:39:45'),
(2, 1, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(3, 1, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(4, 1, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(5, 1, 8, 'BPJS Kesehatan', 'Deduction', 70000.00, '2026-01-07 09:39:45'),
(6, 1, 9, 'BPJS Ketenagakerjaan', 'Deduction', 140000.00, '2026-01-07 09:39:45'),
(7, 1, 10, 'PPh 21', 'Deduction', 350000.00, '2026-01-07 09:39:45'),
(8, 2, 5, 'Tunjangan Keluarga', 'Earning', 1000000.00, '2026-01-07 09:39:45'),
(9, 2, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(10, 2, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(11, 2, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(12, 2, 8, 'BPJS Kesehatan', 'Deduction', 100000.00, '2026-01-07 09:39:45'),
(13, 2, 9, 'BPJS Ketenagakerjaan', 'Deduction', 200000.00, '2026-01-07 09:39:45'),
(14, 2, 11, 'Potongan Keterlambatan', 'Deduction', 100000.00, '2026-01-07 09:39:45'),
(15, 2, 10, 'PPh 21', 'Deduction', 500000.00, '2026-01-07 09:39:45'),
(16, 3, 5, 'Tunjangan Keluarga', 'Earning', 1200000.00, '2026-01-07 09:39:45'),
(17, 3, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(18, 3, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(19, 3, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(20, 3, 8, 'BPJS Kesehatan', 'Deduction', 120000.00, '2026-01-07 09:39:45'),
(21, 3, 9, 'BPJS Ketenagakerjaan', 'Deduction', 240000.00, '2026-01-07 09:39:45'),
(22, 3, 10, 'PPh 21', 'Deduction', 600000.00, '2026-01-07 09:39:45'),
(23, 4, 5, 'Tunjangan Keluarga', 'Earning', 2000000.00, '2026-01-07 09:39:45'),
(24, 4, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(25, 4, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(26, 4, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(27, 4, 8, 'BPJS Kesehatan', 'Deduction', 200000.00, '2026-01-07 09:39:45'),
(28, 4, 9, 'BPJS Ketenagakerjaan', 'Deduction', 400000.00, '2026-01-07 09:39:45'),
(29, 4, 10, 'PPh 21', 'Deduction', 1000000.00, '2026-01-07 09:39:45'),
(30, 5, 5, 'Tunjangan Keluarga', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(31, 5, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(32, 5, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(33, 5, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(34, 5, 8, 'BPJS Kesehatan', 'Deduction', 50000.00, '2026-01-07 09:39:45'),
(35, 5, 9, 'BPJS Ketenagakerjaan', 'Deduction', 100000.00, '2026-01-07 09:39:45'),
(36, 5, 13, 'Potongan Alpha', 'Deduction', 161290.32, '2026-01-07 09:39:45'),
(37, 5, 11, 'Potongan Keterlambatan', 'Deduction', 50000.00, '2026-01-07 09:39:45'),
(38, 5, 10, 'PPh 21', 'Deduction', 250000.00, '2026-01-07 09:39:45'),
(39, 6, 5, 'Tunjangan Keluarga', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(40, 6, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(41, 6, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(42, 6, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(43, 6, 8, 'BPJS Kesehatan', 'Deduction', 50000.00, '2026-01-07 09:39:45'),
(44, 6, 9, 'BPJS Ketenagakerjaan', 'Deduction', 100000.00, '2026-01-07 09:39:45'),
(45, 6, 10, 'PPh 21', 'Deduction', 250000.00, '2026-01-07 09:39:45'),
(46, 7, 5, 'Tunjangan Keluarga', 'Earning', 1500000.00, '2026-01-07 09:39:45'),
(47, 7, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(48, 7, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(49, 7, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(50, 7, 8, 'BPJS Kesehatan', 'Deduction', 150000.00, '2026-01-07 09:39:45'),
(51, 7, 9, 'BPJS Ketenagakerjaan', 'Deduction', 300000.00, '2026-01-07 09:39:45'),
(52, 7, 10, 'PPh 21', 'Deduction', 750000.00, '2026-01-07 09:39:45'),
(53, 8, 5, 'Tunjangan Keluarga', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(54, 8, 4, 'Tunjangan Kesehatan', 'Earning', 300000.00, '2026-01-07 09:39:45'),
(55, 8, 3, 'Tunjangan Makan', 'Earning', 750000.00, '2026-01-07 09:39:45'),
(56, 8, 2, 'Tunjangan Transportasi', 'Earning', 500000.00, '2026-01-07 09:39:45'),
(57, 8, 8, 'BPJS Kesehatan', 'Deduction', 50000.00, '2026-01-07 09:39:45'),
(58, 8, 9, 'BPJS Ketenagakerjaan', 'Deduction', 100000.00, '2026-01-07 09:39:45'),
(59, 8, 10, 'PPh 21', 'Deduction', 250000.00, '2026-01-07 09:39:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `level` int(11) DEFAULT NULL,
  `position_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `positions`
--

INSERT INTO `positions` (`id`, `position_name`, `level`, `position_code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Chief Executive Officer', NULL, 'CEO', 'Top executive responsible for overall company operations', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(2, 'Chief Technology Officer', NULL, 'CTO', 'Oversees technology strategy and development', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(3, 'HR Manager', 5, 'HRM', 'Manages human resources department', '2026-01-07 08:22:28', '2026-01-07 09:16:58'),
(4, 'Senior Developer', 3, 'SDEV', 'Experienced software developer', '2026-01-07 08:22:28', '2026-01-07 09:16:58'),
(5, 'Junior Developer', 2, 'JDEV', 'Entry-level software developer', '2026-01-07 08:22:28', '2026-01-07 09:16:58'),
(6, 'Marketing Manager', 5, 'MKTM', 'Leads marketing initiatives', '2026-01-07 08:22:28', '2026-01-07 09:16:58'),
(7, 'Sales Executive', NULL, 'SLSE', 'Handles sales and client relations', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(8, 'Accountant', NULL, 'ACC', 'Manages financial records and reporting', '2026-01-07 08:22:28', '2026-01-07 08:22:28'),
(9, 'Operations Manager', 5, 'OPM', 'Oversees operational activities', '2026-01-07 08:22:28', '2026-01-07 09:16:58'),
(10, 'Administrative Assistant', NULL, 'ADMIN', 'Provides administrative support', '2026-01-07 08:22:28', '2026-01-07 08:22:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_category`, `description`, `updated_at`) VALUES
(1, 'work_start_time', '08:00', 'attendance', 'Jam masuk kerja (format HH:MM)', '2026-01-07 10:04:10'),
(2, 'work_end_time', '17:00', 'attendance', 'Jam pulang kerja (format HH:MM)', '2026-01-07 10:04:10'),
(3, 'late_tolerance_minutes', '15', 'attendance', 'Toleransi keterlambatan dalam menit', '2026-01-07 10:04:10'),
(4, 'early_leave_tolerance_minutes', '15', 'attendance', 'Toleransi pulang cepat dalam menit', '2026-01-07 10:04:10'),
(5, 'working_days_per_week', '5', 'attendance', 'Jumlah hari kerja per minggu', '2026-01-07 10:04:10'),
(6, 'break_start_time', '12:00', 'attendance', 'Jam mulai istirahat', '2026-01-07 10:04:10'),
(7, 'break_end_time', '13:00', 'attendance', 'Jam selesai istirahat', '2026-01-07 10:04:10'),
(8, 'overtime_multiplier', '1.5', 'attendance', 'Multiplier untuk perhitungan lembur', '2026-01-07 10:04:10'),
(9, 'weekend_days', 'Saturday,Sunday', 'attendance', 'Hari libur akhir pekan (comma separated)', '2026-01-07 10:04:10'),
(10, 'annual_leave_days', '12', 'leave', 'Jumlah cuti tahunan per tahun', '2026-01-07 10:04:10'),
(11, 'sick_leave_days', '12', 'leave', 'Jumlah cuti sakit per tahun', '2026-01-07 10:04:10'),
(12, 'min_days_before_leave', '3', 'leave', 'Minimal hari sebelum mengajukan cuti', '2026-01-07 10:04:10'),
(13, 'max_consecutive_leave_days', '14', 'leave', 'Maksimal hari cuti berturut-turut', '2026-01-07 10:04:10'),
(14, 'carry_forward_leave', '1', 'leave', 'Izinkan carry forward cuti (1=yes, 0=no)', '2026-01-07 10:04:10'),
(15, 'max_carry_forward_days', '5', 'leave', 'Maksimal hari cuti yang bisa di-carry forward', '2026-01-07 10:04:10'),
(16, 'payroll_period', 'monthly', 'payroll', 'Periode penggajian (monthly/biweekly/weekly)', '2026-01-07 10:04:10'),
(17, 'payroll_day', '25', 'payroll', 'Tanggal pembayaran gaji', '2026-01-07 10:04:10'),
(18, 'tax_percentage', '5', 'payroll', 'Persentase pajak penghasilan', '2026-01-07 10:04:10'),
(19, 'insurance_percentage', '2', 'payroll', 'Persentase potongan asuransi', '2026-01-07 10:04:10'),
(20, 'late_deduction_amount', '50000', 'payroll', 'Potongan per keterlambatan (Rp)', '2026-01-07 10:04:10'),
(21, 'absence_deduction_type', 'daily_salary', 'payroll', 'Tipe potongan alpha (daily_salary/fixed_amount)', '2026-01-07 10:04:10'),
(22, 'company_name', 'PT. HRIS Indonesia', 'general', 'Nama perusahaan', '2026-01-07 10:04:10'),
(23, 'company_address', 'Jakarta, Indonesia', 'general', 'Alamat perusahaan', '2026-01-07 10:04:10'),
(24, 'company_phone', '+62 21 1234567', 'general', 'Nomor telepon perusahaan', '2026-01-07 10:04:10'),
(25, 'company_email', 'info@hris.com', 'general', 'Email perusahaan', '2026-01-07 10:04:10'),
(26, 'timezone', 'Asia/Jakarta', 'general', 'Timezone sistem', '2026-01-07 10:04:10'),
(27, 'date_format', 'd/m/Y', 'general', 'Format tanggal', '2026-01-07 10:04:10'),
(28, 'currency', 'IDR', 'general', 'Mata uang', '2026-01-07 10:04:10'),
(29, 'language', 'id', 'general', 'Bahasa sistem (id/en)', '2026-01-07 10:04:10'),
(30, 'email_notifications', '1', 'notification', 'Aktifkan notifikasi email (1=yes, 0=no)', '2026-01-07 10:04:10'),
(31, 'leave_approval_notification', '1', 'notification', 'Notifikasi persetujuan cuti', '2026-01-07 10:04:10'),
(32, 'payroll_notification', '1', 'notification', 'Notifikasi slip gaji', '2026-01-07 10:04:10'),
(33, 'birthday_notification', '1', 'notification', 'Notifikasi ulang tahun karyawan', '2026-01-07 10:04:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','HR','Employee') DEFAULT 'Employee',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`, `status`, `employee_id`) VALUES
(5, 'admin', 'admin@hris.com', '$2y$10$eD.9YSId2AJAzBHNPbVJFOuVPZJKHmXGbMWqT7uLqxager8Wv8xWC', 'Admin', 1, NULL, '2026-01-07 08:35:15', '2026-01-07 08:35:15', 'Active', NULL),
(6, 'hr_manager', 'hr@hris.com', '$2y$10$eD.9YSId2AJAzBHNPbVJFOuVPZJKHmXGbMWqT7uLqxager8Wv8xWC', 'HR', 1, NULL, '2026-01-07 08:35:15', '2026-01-07 08:35:15', 'Active', NULL),
(7, 'adminmhc', 'rizkifahrezi990@gmail.com', '$2y$10$jlQe2rxH.Yb8Fwtz1DV8XOa2OisxBEFsx2nKVMrz4Lbq30udirXVa', 'Admin', 1, '2026-01-07 12:22:53', '2026-01-07 08:42:03', '2026-01-07 12:22:53', 'Active', NULL),
(8, 'karyawan', 'karyawan@gmail.com', '$2y$10$9JLxWW7bgh1xadjC3C6ndOdkDrLV13CFiQJAcomguRS6B9fioVPXi', 'Employee', 1, NULL, '2026-01-07 12:23:41', '2026-01-07 12:23:41', 'Active', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indeks untuk tabel `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_applicant` (`applicant_id`),
  ADD KEY `idx_type` (`document_type`);

--
-- Indeks untuk tabel `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`employee_id`,`attendance_date`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_attendance_employee` (`employee_id`),
  ADD KEY `idx_attendance_status` (`status`),
  ADD KEY `idx_attendance_month` (`attendance_date`,`employee_id`),
  ADD KEY `fk_attendance_location` (`office_location_id`);

--
-- Indeks untuk tabel `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_number` (`contract_number`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_contract_number` (`contract_number`),
  ADD KEY `idx_contract_employee` (`employee_id`),
  ADD KEY `idx_contract_status` (`contract_status`),
  ADD KEY `idx_contract_type` (`contract_type`),
  ADD KEY `idx_contract_dates` (`start_date`,`end_date`);

--
-- Indeks untuk tabel `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_code` (`department_code`);

--
-- Indeks untuk tabel `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_employee_code` (`employee_code`),
  ADD KEY `idx_employee_status` (`employment_status`),
  ADD KEY `idx_employee_department` (`department_id`),
  ADD KEY `idx_employee_position` (`position_id`);

--
-- Indeks untuk tabel `employee_kpi_assignments`
--
ALTER TABLE `employee_kpi_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_indicator` (`indicator_id`),
  ADD KEY `idx_period` (`period_start`,`period_end`);

--
-- Indeks untuk tabel `employee_payroll_config`
--
ALTER TABLE `employee_payroll_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payroll_config_employee` (`employee_id`),
  ADD KEY `idx_payroll_config_component` (`component_id`);

--
-- Indeks untuk tabel `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `idx_date` (`interview_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_job` (`job_id`),
  ADD KEY `idx_applicant` (`applicant_id`);

--
-- Indeks untuk tabel `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_posted_date` (`posted_date`);

--
-- Indeks untuk tabel `kpi_categories`
--
ALTER TABLE `kpi_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `kpi_evaluations`
--
ALTER TABLE `kpi_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `indicator_id` (`indicator_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_period` (`period`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `kpi_indicators`
--
ALTER TABLE `kpi_indicators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_employee` (`employee_id`),
  ADD KEY `idx_leave_status` (`status`),
  ADD KEY `idx_leave_dates` (`start_date`,`end_date`),
  ADD KEY `idx_leave_type` (`leave_type_id`);

--
-- Indeks untuk tabel `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `office_locations`
--
ALTER TABLE `office_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_coordinates` (`latitude`,`longitude`);

--
-- Indeks untuk tabel `payroll_components`
--
ALTER TABLE `payroll_components`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_period` (`period_month`,`period_year`),
  ADD KEY `idx_payroll_period_status` (`status`);

--
-- Indeks untuk tabel `payroll_slips`
--
ALTER TABLE `payroll_slips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_period` (`employee_id`,`period_id`),
  ADD KEY `idx_payroll_slip_period` (`period_id`),
  ADD KEY `idx_payroll_slip_employee` (`employee_id`),
  ADD KEY `idx_payroll_slip_status` (`status`);

--
-- Indeks untuk tabel `payroll_slip_details`
--
ALTER TABLE `payroll_slip_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slip_id` (`slip_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indeks untuk tabel `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position_code` (`position_code`);

--
-- Indeks untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_category` (`setting_category`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_username` (`username`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `applicant_documents`
--
ALTER TABLE `applicant_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `employee_kpi_assignments`
--
ALTER TABLE `employee_kpi_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `employee_payroll_config`
--
ALTER TABLE `employee_payroll_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kpi_categories`
--
ALTER TABLE `kpi_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `kpi_evaluations`
--
ALTER TABLE `kpi_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `kpi_indicators`
--
ALTER TABLE `kpi_indicators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `office_locations`
--
ALTER TABLE `office_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `payroll_components`
--
ALTER TABLE `payroll_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `payroll_periods`
--
ALTER TABLE `payroll_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `payroll_slips`
--
ALTER TABLE `payroll_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `payroll_slip_details`
--
ALTER TABLE `payroll_slip_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD CONSTRAINT `applicant_documents_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_location` FOREIGN KEY (`office_location_id`) REFERENCES `office_locations` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contracts_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `employee_kpi_assignments`
--
ALTER TABLE `employee_kpi_assignments`
  ADD CONSTRAINT `employee_kpi_assignments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_kpi_assignments_ibfk_2` FOREIGN KEY (`indicator_id`) REFERENCES `kpi_indicators` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `employee_payroll_config`
--
ALTER TABLE `employee_payroll_config`
  ADD CONSTRAINT `employee_payroll_config_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_payroll_config_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `payroll_components` (`id`);

--
-- Ketidakleluasaan untuk tabel `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `job_postings_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_postings_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kpi_evaluations`
--
ALTER TABLE `kpi_evaluations`
  ADD CONSTRAINT `kpi_evaluations_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `employee_kpi_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_evaluations_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_evaluations_ibfk_3` FOREIGN KEY (`indicator_id`) REFERENCES `kpi_indicators` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kpi_indicators`
--
ALTER TABLE `kpi_indicators`
  ADD CONSTRAINT `kpi_indicators_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `kpi_categories` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `payroll_slips`
--
ALTER TABLE `payroll_slips`
  ADD CONSTRAINT `payroll_slips_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_slips_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payroll_slip_details`
--
ALTER TABLE `payroll_slip_details`
  ADD CONSTRAINT `payroll_slip_details_ibfk_1` FOREIGN KEY (`slip_id`) REFERENCES `payroll_slips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_slip_details_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `payroll_components` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
