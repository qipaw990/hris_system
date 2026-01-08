-- Add level column to positions table
USE hris_db;

ALTER TABLE positions 
ADD COLUMN level INT NULL AFTER position_name;

-- Update existing positions with default levels
UPDATE positions SET level = 3 WHERE position_name LIKE '%Senior%';
UPDATE positions SET level = 2 WHERE position_name LIKE '%Junior%';
UPDATE positions SET level = 5 WHERE position_name LIKE '%Manager%';
UPDATE positions SET level = 6 WHERE position_name LIKE '%Director%';
UPDATE positions SET level = 7 WHERE position_name LIKE '%CTO%' OR position_name LIKE '%CEO%';
