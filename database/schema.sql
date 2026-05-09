CREATE DATABASE IF NOT EXISTS vending_machine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE vending_machine;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'User') NOT NULL DEFAULT 'User',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    price DECIMAL(10,3) NOT NULL,
    quantity_available INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_name_price (name, price)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,3) NOT NULL,
    total_amount DECIMAL(12,3) NOT NULL,
    transaction_type ENUM('PURCHASE') NOT NULL DEFAULT 'PURCHASE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transactions_user_id (user_id),
    INDEX idx_transactions_product_id (product_id),
    CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_transactions_product FOREIGN KEY (product_id) REFERENCES products(id)
);