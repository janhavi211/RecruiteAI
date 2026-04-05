-- ============================================================
-- Resume Shortlisting System - Database Schema
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS resume_shortlisting;
USE resume_shortlisting;

-- ============================================================
-- USERS TABLE (candidates, HR, admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,          -- bcrypt hashed
    role ENUM('candidate','hr','admin') DEFAULT 'candidate',
    is_verified TINYINT(1) DEFAULT 0,        -- email OTP verified
    otp VARCHAR(6) DEFAULT NULL,
    otp_expiry DATETIME DEFAULT NULL,
    company_name VARCHAR(150) DEFAULT NULL,  -- for HR users
    phone VARCHAR(20) DEFAULT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- JOBS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hr_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    skills_required TEXT NOT NULL,           -- comma-separated skills
    experience_min INT DEFAULT 0,            -- in years
    experience_max INT DEFAULT 10,
    location VARCHAR(100) DEFAULT 'Remote',
    job_type ENUM('Full-time','Part-time','Contract','Internship') DEFAULT 'Full-time',
    salary_min INT DEFAULT 0,
    salary_max INT DEFAULT 0,
    status ENUM('active','closed','draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hr_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- RESUMES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    extracted_skills TEXT DEFAULT NULL,      -- JSON array of skills
    extracted_text LONGTEXT DEFAULT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- APPLICATIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    resume_id INT DEFAULT NULL,
    status ENUM('applied','shortlisted','rejected','interview_scheduled','hired') DEFAULT 'applied',
    match_percentage DECIMAL(5,2) DEFAULT 0.00,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_application (user_id, job_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE SET NULL
);

-- ============================================================
-- SAVED JOBS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, job_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

-- ============================================================
-- INTERVIEWS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS interviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    interview_date DATE NOT NULL,
    interview_time TIME NOT NULL,
    interview_type ENUM('online','in-person','phone') DEFAULT 'online',
    meeting_link VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    email_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SEED DATA: Admin user (password: admin123)
-- ============================================================
INSERT INTO users (name, email, password, role, is_verified) VALUES
('Admin', 'admin@rss.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- ============================================================
-- SAMPLE HR (password: hr123456)
-- ============================================================
INSERT INTO users (name, email, password, role, is_verified, company_name) VALUES
('TechCorp HR', 'hr@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', 1, 'TechCorp Pvt Ltd');

-- ============================================================
-- SAMPLE JOBS
-- ============================================================
INSERT INTO jobs (hr_id, title, description, skills_required, experience_min, experience_max, location, job_type, salary_min, salary_max) VALUES
(2, 'Full Stack Developer', 'We are looking for a skilled Full Stack Developer to join our team. You will work on exciting projects using modern web technologies.', 'PHP,MySQL,JavaScript,HTML,CSS,React,Node.js', 1, 4, 'Pune, India', 'Full-time', 400000, 800000),
(2, 'Python Data Analyst', 'Analyze large datasets and provide insights using Python and data visualization tools.', 'Python,Pandas,NumPy,Matplotlib,SQL,Machine Learning', 0, 3, 'Remote', 'Full-time', 350000, 650000),
(2, 'UI/UX Designer', 'Design beautiful and intuitive user interfaces for our products.', 'Figma,Adobe XD,CSS,HTML,User Research,Prototyping', 1, 5, 'Mumbai, India', 'Full-time', 300000, 600000),
(2, 'DevOps Engineer', 'Manage cloud infrastructure and CI/CD pipelines for our growing platform.', 'AWS,Docker,Kubernetes,Linux,CI/CD,Python,Bash', 2, 6, 'Remote', 'Full-time', 600000, 1200000),
(2, 'React Frontend Intern', 'Join us as an intern and work on building modern React applications.', 'React,JavaScript,HTML,CSS,Git', 0, 1, 'Bangalore, India', 'Internship', 15000, 25000);