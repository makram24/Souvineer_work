-- Database schema for daily work management system
CREATE DATABASE IF NOT EXISTS work_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE work_management;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily entries table
CREATE TABLE IF NOT EXISTS daily_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_date DATE NOT NULL,
    worked_hours DECIMAL(4,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_entry_date (entry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Images table
CREATE TABLE IF NOT EXISTS entry_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES daily_entries(id) ON DELETE CASCADE,
    INDEX idx_entry_id (entry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Videos table
CREATE TABLE IF NOT EXISTS entry_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    video_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES daily_entries(id) ON DELETE CASCADE,
    INDEX idx_entry_id (entry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: admin123)
-- IMPORTANT: Run setup.php after importing this SQL file to set the correct password hash
-- Or manually insert with: INSERT INTO users (username, password) VALUES ('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy');
-- The hash above is for 'admin123' - CHANGE THIS PASSWORD AFTER FIRST LOGIN!

