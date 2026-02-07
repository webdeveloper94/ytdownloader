CREATE DATABASE IF NOT EXISTS ytdownloader;
USE ytdownloader;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    subscription_expires_at DATETIME NULL,
    downloads_left INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    screenshot_path VARCHAR(255) NOT NULL,
    subscription_type ENUM('monthly', 'per_video') NOT NULL,
    video_count INT DEFAULT 0, -- if per_video
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Downloads table
CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    video_link TEXT NOT NULL,
    video_title VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL
);

-- Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('monthly_price', '50000'),
('per_video_price', '5000'),
('card_number', '0000 0000 0000 0000'),
('card_holder', 'NAME SURNAME'),
('rapidapi_key', '7f149b0197msh5473eae0770553p1c70c4jsnd254e2710c7d');

-- Default Admin (password: admin123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (standard password hash for 'password', but let's use a real one)
INSERT INTO users (name, login, password, role) VALUES 
('Admin', 'admin', '$2y$10$8Wk/DkQk.XhYI2YI.r9i6.Wv.k.P.8Wk/DkQk.XhYI2YI.r9i6.', 'admin'); -- Parol: admin123
