-- Kebele Civil Registration Management System Database
CREATE DATABASE IF NOT EXISTS kebele_system;
USE kebele_system;

-- 1. Users Table (Authentication & Roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Super Admin', 'Admin') DEFAULT 'Admin',
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Persons Table (Core module for all residents)
CREATE TABLE IF NOT EXISTS persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    grandfather_name VARCHAR(100) NOT NULL,
    sex ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(150),
    nationality VARCHAR(100) DEFAULT 'Ethiopian',
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Single',
    educational_level ENUM('No Formal Education', 'Primary (1-8)', 'Secondary (9-12)', 'Certificate/Diploma', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD/Doctorate') DEFAULT 'No Formal Education',
    occupational_status ENUM('Employed', 'Self-Employed', 'Unemployed', 'Student', 'Retired', 'Farmer', 'Housewife', 'Other') DEFAULT 'Unemployed',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Migration: Add columns if they don't exist (run after initial setup)
-- ALTER TABLE persons ADD COLUMN IF NOT EXISTS educational_level ENUM('No Formal Education','Primary (1-8)','Secondary (9-12)','Certificate/Diploma','Bachelor\'s Degree','Master\'s Degree','PhD/Doctorate') DEFAULT 'No Formal Education';
-- ALTER TABLE persons ADD COLUMN IF NOT EXISTS occupational_status ENUM('Employed','Self-Employed','Unemployed','Student','Retired','Farmer','Housewife','Other') DEFAULT 'Unemployed';

-- 2.1 Death Records Table (for existing residents)
CREATE TABLE IF NOT EXISTS death_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT UNIQUE NOT NULL,
    date_of_death DATE NOT NULL,
    cause_of_death VARCHAR(255),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
);

-- 3. Birth Certificates Table
CREATE TABLE IF NOT EXISTS birth_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(100) UNIQUE NOT NULL,
    person_id INT NOT NULL,
    mother_name VARCHAR(150) NOT NULL,
    mother_nationality VARCHAR(100) DEFAULT 'Ethiopian',
    father_nationality VARCHAR(100) DEFAULT 'Ethiopian',
    registrar_id INT,
    registered_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (registrar_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 4. Death Certificates Table
CREATE TABLE IF NOT EXISTS death_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(100) UNIQUE NOT NULL,
    person_id INT NOT NULL,
    title VARCHAR(50),
    place_of_death VARCHAR(150),
    date_of_death DATE NOT NULL,
    registrar_id INT,
    registered_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (registrar_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. Marriage Certificates Table
CREATE TABLE IF NOT EXISTS marriage_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(100) UNIQUE NOT NULL,
    husband_id INT NOT NULL,
    wife_id INT NOT NULL,
    place_of_marriage VARCHAR(150),
    date_of_marriage DATE NOT NULL,
    registrar_id INT,
    registered_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (husband_id) REFERENCES persons(id) ON DELETE RESTRICT,
    FOREIGN KEY (wife_id) REFERENCES persons(id) ON DELETE RESTRICT,
    FOREIGN KEY (registrar_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 6. Divorce Certificates Table
CREATE TABLE IF NOT EXISTS divorce_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(100) UNIQUE NOT NULL,
    husband_id INT NOT NULL,
    wife_id INT NOT NULL,
    place_of_divorce VARCHAR(150),
    date_of_divorce DATE NOT NULL,
    registrar_id INT,
    registered_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (husband_id) REFERENCES persons(id) ON DELETE RESTRICT,
    FOREIGN KEY (wife_id) REFERENCES persons(id) ON DELETE RESTRICT,
    FOREIGN KEY (registrar_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Super Admin
-- Password is 'admin123' (hashed using BCRYPT)
INSERT INTO users (username, password, role, name) 
VALUES ('superadmin', '$2y$10$e.wP/q6hU9t83I4z0B2x.O./EHQZOT05.GfQn5vN2c3Xp0/T794E6', 'Super Admin', 'System Administrator')
ON DUPLICATE KEY UPDATE username='superadmin';
