-- Add basic_salary column to employees table
USE hris_db;

ALTER TABLE employees 
ADD COLUMN basic_salary DECIMAL(15,2) DEFAULT 0 AFTER employment_status;

-- Update existing employees with sample basic salary based on position
UPDATE employees SET basic_salary = 5000000 WHERE position_id = 1; -- Entry level
UPDATE employees SET basic_salary = 7000000 WHERE position_id = 2; -- Junior
UPDATE employees SET basic_salary = 10000000 WHERE position_id = 3; -- Senior
UPDATE employees SET basic_salary = 12000000 WHERE position_id = 4; -- Lead
UPDATE employees SET basic_salary = 15000000 WHERE position_id = 5; -- Manager
UPDATE employees SET basic_salary = 20000000 WHERE position_id = 6; -- Director

-- Set default for employees without position
UPDATE employees SET basic_salary = 5000000 WHERE basic_salary = 0;
