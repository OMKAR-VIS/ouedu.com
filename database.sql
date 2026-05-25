-- Our Education Mentor - Full Database Schema
-- Note: php/config.php auto-creates this on first run.
-- Import only AFTER XAMPP MySQL is running.

CREATE DATABASE IF NOT EXISTS oureducationmentor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE oureducationmentor;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(12) UNIQUE NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    mobile VARCHAR(15) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    progress INT NOT NULL DEFAULT 0,
    videos_watched INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS student_id_cards (
    user_id INT PRIMARY KEY,
    blood_group VARCHAR(10) DEFAULT NULL,
    address TEXT,
    father_name VARCHAR(120) DEFAULT NULL,
    mother_name VARCHAR(120) DEFAULT NULL,
    dob VARCHAR(20) DEFAULT NULL,
    class_name VARCHAR(80) DEFAULT 'JAC Class 10',
    school_name VARCHAR(180) DEFAULT 'Our Education Mentor',
    extra_note TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('video','pdf') NOT NULL,
    course VARCHAR(80) NOT NULL,
    subject VARCHAR(80) DEFAULT NULL,
    title VARCHAR(180) NOT NULL,
    youtube_url VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(40) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    target ENUM('website','dashboard') NOT NULL DEFAULT 'website',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course VARCHAR(80) NOT NULL,
    subject VARCHAR(80) NOT NULL,
    title VARCHAR(180) NOT NULL,
    test_url VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
