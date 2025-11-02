
-- 1. Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    pass VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,


-- 2. Cities table
CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Saved_filters table
CREATE TABLE saved_filters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    city_id INT NOT NULL,
    forecast_days INT DEFAULT 1,
    min_temp_selected DECIMAL(5,2),
    max_temp_selected DECIMAL(5,2),
    avg_temp_selected DECIMAL(5,2),
    weather_cond_selected VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);

-- 4. Subscriptions table
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lasts_until TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    card_number VARCHAR(20),
    bank_transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);



-- Sample users
INSERT INTO users (fname, lname, email, pass, is_admin) VALUES
('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Admin', 'User', 'admin@weather.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Sample cities
INSERT INTO cities (name, zip_code) VALUES
('Sarajevo', '71000'),
('Fojnica', '71270'),
('Rakovica', '71220'),
('Tuzla', '75000'),
('Mostar', '88000'),
('London', 'SW1A 1AA'),
('New York', '10001'),
('Tokyo', '100-0001'),
('Sydney', '2000');

-- Sample subscriptions
INSERT INTO subscriptions (user_id, lasts_until) VALUES
(1, '2024-12-31 23:59:59'),
(2, '2024-11-30 23:59:59');

-- Sample saved filters
INSERT INTO saved_filters (user_id, city_id, forecast_days, min_temp_selected, max_temp_selected, avg_temp_selected, weather_cond_selected) VALUES
(1, 1, 3, 15.0, 25.0, 20.0, 'sunny'),
(1, 2, 1, 10.0, 20.0, 15.0, 'cloudy'),
(2, 3, 5, 5.0, 15.0, 10.0, 'rainy');

-- Sample payments
INSERT INTO payments (subscription_id, payment_method, amount, card_number, bank_transaction_id) VALUES
(1, 'credit_card', 4.99, '****1234', 'TXN123456789'),
(2, 'credit_card', 9.99, '****5678', 'TXN987654321');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_cities_name ON cities(name);
CREATE INDEX idx_saved_filters_user_id ON saved_filters(user_id);
CREATE INDEX idx_saved_filters_city_id ON saved_filters(city_id);
CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_payments_subscription_id ON payments(subscription_id);
CREATE INDEX idx_payments_bank_transaction_id ON payments(bank_transaction_id);

-- Show table structure
SHOW TABLES;
DESCRIBE users;
DESCRIBE cities;
DESCRIBE saved_filters;
DESCRIBE subscriptions;
DESCRIBE payments;
