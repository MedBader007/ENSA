-- Create database
CREATE DATABASE IF NOT EXISTS gestion_stages CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_stages;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    role ENUM('admin', 'enseignant', 'etudiant') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    user_id INT PRIMARY KEY,
    apogee_number VARCHAR(20) UNIQUE NOT NULL,
    filiere VARCHAR(100) NOT NULL,
    niveau VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Teachers table
CREATE TABLE teachers (
    user_id INT PRIMARY KEY,
    department VARCHAR(100) NOT NULL,
    speciality VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Projects table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('stage-initiation', 'stage-ingenieur', 'pfe', 'module') NOT NULL,
    module VARCHAR(100),
    status ENUM('pending', 'validated', 'rejected') DEFAULT 'pending',
    student_id INT NOT NULL,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(user_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(user_id)
);

-- Files table
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Evaluations table
CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    teacher_id INT NOT NULL,
    note DECIMAL(4,2) NOT NULL,
    comment TEXT,
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(user_id)
);

-- Create indexes
CREATE INDEX idx_project_status ON projects(status);
CREATE INDEX idx_project_type ON projects(type);
CREATE INDEX idx_student_filiere ON students(filiere);
CREATE INDEX idx_teacher_department ON teachers(department);