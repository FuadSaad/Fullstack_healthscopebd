-- HealthScope BD Database Setup
-- Run this in phpMyAdmin after installing XAMPP

CREATE DATABASE IF NOT EXISTS healthscope_db;
USE healthscope_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Disease reports table
CREATE TABLE IF NOT EXISTS disease_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    disease_name VARCHAR(255) NOT NULL,
    symptoms TEXT,
    severity ENUM('mild', 'moderate', 'severe') DEFAULT 'mild',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    location_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_disease (disease_name),
    INDEX idx_location (latitude, longitude),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Symptom checks table
CREATE TABLE IF NOT EXISTS symptom_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    symptoms JSON NOT NULL,
    predicted_disease VARCHAR(255),
    severity ENUM('mild', 'moderate', 'severe'),
    recommendations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert demo user for testing
INSERT INTO users (name, email, phone, password_hash) VALUES
('Demo User', 'demo@healthscope.com', '+8801234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Password: password123

-- Insert sample disease reports
INSERT INTO disease_reports (disease_name, symptoms, severity, latitude, longitude, location_name) VALUES
('Dengue', 'Fever, Headache, Muscle pain', 'moderate', 23.8103, 90.4125, 'Dhaka'),
('COVID-19', 'Fever, Cough, Fatigue', 'severe', 22.3569, 91.7832, 'Chittagong'),
('Typhoid', 'High Fever, Weakness', 'moderate', 24.3636, 88.6241, 'Rajshahi'),
('Malaria', 'Fever, Chills, Sweating', 'severe', 22.8456, 89.5403, 'Khulna');
