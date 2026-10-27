CREATE DATABASE social_share_tracker
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE social_share_tracker;

CREATE TABLE social_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_url TEXT NOT NULL,
    platform VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_platform (platform),
    INDEX idx_created_at (created_at)
);


CREATE TABLE admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);