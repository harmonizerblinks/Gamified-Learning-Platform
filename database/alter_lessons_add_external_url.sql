-- Add external_url to lesson_type ENUM
ALTER TABLE lessons 
MODIFY COLUMN lesson_type ENUM('video', 'text', 'pdf', 'mixed', 'external_url') DEFAULT 'text';
