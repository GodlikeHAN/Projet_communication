
CREATE DATABASE IF NOT EXISTS project_communication
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


USE project_communication;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
   	password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);











