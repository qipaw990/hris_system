-- Update User Passwords
-- Password for both users: admin123

USE hris_db;

-- Delete existing users first
DELETE FROM users WHERE username IN ('admin', 'hr_manager');

-- Insert users with correct password hash
-- Password: admin123
INSERT INTO users (username, email, password, role, is_active) VALUES
('admin', 'admin@hris.com', '$2y$10$eD.9YSId2AJAzBHNPbVJFOuVPZJKHmXGbMWqT7uLqxager8Wv8xWC', 'admin', 1),
('hr_manager', 'hr@hris.com', '$2y$10$eD.9YSId2AJAzBHNPbVJFOuVPZJKHmXGbMWqT7uLqxager8Wv8xWC', 'hr', 1);
