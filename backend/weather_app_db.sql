-- Weather App Database Schema
-- Milestone 2: Backend Setup and CRUD Operations
-- Created: November 2025

-- Create Database
CREATE DATABASE IF NOT EXISTS weather_app_db;
USE weather_app_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    pass VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Cities Table
CREATE TABLE IF NOT EXISTS cities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    zip_code VARCHAR(20),
    country VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_zip_code (zip_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Subscriptions Table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lasts_until DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_lasts_until (lasts_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id INT NOT NULL,
    payment_method ENUM('card', 'bank_transfer') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    card_number VARCHAR(255),
    bank_transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    INDEX idx_subscription_id (subscription_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Saved Filters Table
CREATE TABLE IF NOT EXISTS saved_filters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    city_id INT NOT NULL,
    forecast_days INT DEFAULT 7,
    min_temp_selected DECIMAL(5, 2),
    max_temp_selected DECIMAL(5, 2),
    avg_temp_selected DECIMAL(5, 2),
    weather_cond_selected VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_city_id (city_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data (Optional)
INSERT INTO users (fname, lname, email, pass, is_admin) VALUES 
('John', 'Doe', 'john@example.com', '$2y$10$example_hash_here', FALSE);

INSERT INTO cities (name, zip_code, country) VALUES 
('New York', '10001', 'USA'),
('Los Angeles', '90001', 'USA'),
('London', 'SW1A 1AA', 'UK'),
('Paris', '75001', 'France'),
('Berlin', '10115', 'Germany');

-- Indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_cities_name ON cities(name);
CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_payments_subscription_id ON payments(subscription_id);
CREATE INDEX idx_saved_filters_user_city ON saved_filters(user_id, city_id);
