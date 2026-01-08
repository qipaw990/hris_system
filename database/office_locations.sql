-- Office Locations Table
CREATE TABLE IF NOT EXISTS office_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(255) NOT NULL,
    address TEXT,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_meters INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_coordinates (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add GPS tracking columns to attendance table
ALTER TABLE attendance 
ADD COLUMN IF NOT EXISTS check_in_latitude DECIMAL(10, 8),
ADD COLUMN IF NOT EXISTS check_in_longitude DECIMAL(11, 8),
ADD COLUMN IF NOT EXISTS check_out_latitude DECIMAL(10, 8),
ADD COLUMN IF NOT EXISTS check_out_longitude DECIMAL(11, 8),
ADD COLUMN IF NOT EXISTS office_location_id INT,
ADD COLUMN IF NOT EXISTS distance_meters INT,
ADD CONSTRAINT fk_attendance_location FOREIGN KEY (office_location_id) REFERENCES office_locations(id) ON DELETE SET NULL;

-- Insert sample office locations
INSERT INTO office_locations (location_name, address, latitude, longitude, radius_meters, is_active) VALUES
('Kantor Pusat Jakarta', 'Jl. Sudirman No. 123, Jakarta Selatan', -6.2088, 106.8456, 100, TRUE),
('Kantor Cabang Bandung', 'Jl. Asia Afrika No. 45, Bandung', -6.9175, 107.6191, 150, TRUE),
('Kantor Cabang Surabaya', 'Jl. Tunjungan No. 78, Surabaya', -7.2575, 112.7521, 100, TRUE);
