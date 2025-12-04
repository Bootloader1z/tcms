-- Create database if not exists
CREATE DATABASE IF NOT EXISTS TAS CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges
GRANT ALL PRIVILEGES ON TAS.* TO 'tcms_user'@'%';
FLUSH PRIVILEGES;

-- Use database
USE TAS;
