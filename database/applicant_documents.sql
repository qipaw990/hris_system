-- Add documents table for applicant supporting documents
USE hris_db;

CREATE TABLE IF NOT EXISTS applicant_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    document_type ENUM('Resume', 'Cover Letter', 'Certificate', 'Portfolio', 'ID Card', 'Other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    INDEX idx_applicant (applicant_id),
    INDEX idx_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample documents
INSERT INTO applicant_documents (applicant_id, document_type, document_name, file_path, file_size) VALUES
(1, 'Resume', 'Ahmad_Wijaya_CV.pdf', 'uploads/resumes/ahmad_wijaya_cv.pdf', 245678),
(1, 'Cover Letter', 'Cover_Letter_Ahmad.pdf', 'uploads/cover_letters/cover_letter_ahmad.pdf', 89456),
(2, 'Resume', 'Siti_Nurhaliza_Resume.pdf', 'uploads/resumes/siti_nurhaliza_resume.pdf', 312456),
(3, 'Resume', 'Budi_Santoso_CV.pdf', 'uploads/resumes/budi_santoso_cv.pdf', 198765),
(3, 'Portfolio', 'Marketing_Portfolio.pdf', 'uploads/portfolios/marketing_portfolio.pdf', 567890);
