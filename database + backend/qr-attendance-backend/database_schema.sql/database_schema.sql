-- Tạo database
CREATE DATABASE IF NOT EXISTS QR_ATTENDANCE
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE QR_ATTENDANCE;

-- Bảng ACCOUNT
CREATE TABLE ACCOUNT (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','lecturer','student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng STUDENT
CREATE TABLE STUDENT (
    account_id INT PRIMARY KEY,
    student_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    email VARCHAR(100),
    FOREIGN KEY (account_id) REFERENCES ACCOUNT(id) ON DELETE CASCADE
);

-- Bảng LECTURER
CREATE TABLE LECTURER (
    account_id INT PRIMARY KEY,
    lecturer_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    faculty VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    FOREIGN KEY (account_id) REFERENCES ACCOUNT(id) ON DELETE CASCADE
);

-- Bảng CLASS
CREATE TABLE CLASS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) UNIQUE NOT NULL
);

-- Bảng COURSE
CREATE TABLE COURSE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    credits TINYINT UNSIGNED NOT NULL,
    lecturer_id INT NOT NULL,
    description TEXT,
    FOREIGN KEY (lecturer_id) REFERENCES LECTURER(account_id) ON DELETE CASCADE
);

-- Bảng ENROLLMENT
CREATE TABLE ENROLLMENT (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester VARCHAR(10) NOT NULL,
    year YEAR NOT NULL,
    FOREIGN KEY (student_id) REFERENCES STUDENT(account_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES COURSE(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enroll (student_id, course_id, semester, year)
);

-- Bảng SECTION
CREATE TABLE SECTION (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    class_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time VARCHAR(20) NOT NULL,
    status ENUM('active','expired','completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES COURSE(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES CLASS(id) ON DELETE CASCADE,
    FOREIGN KEY (lecturer_id) REFERENCES LECTURER(account_id) ON DELETE CASCADE
);

-- Bảng QR_CODE
CREATE TABLE QR_CODE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    qr_code VARCHAR(64) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (section_id) REFERENCES SECTION(id) ON DELETE CASCADE
);

-- Bảng ATTENDANCE_SESSION
CREATE TABLE ATTENDANCE_SESSION (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qr_code_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    total_present INT DEFAULT 0,
    total_absent INT DEFAULT 0,
    status ENUM('open','closed') DEFAULT 'open',
    FOREIGN KEY (qr_code_id) REFERENCES QR_CODE(id) ON DELETE CASCADE
);

-- Bảng ATTENDANCE
CREATE TABLE ATTENDANCE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present','absent','late') DEFAULT 'present',
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendance_session_id) REFERENCES ATTENDANCE_SESSION(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES STUDENT(account_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (attendance_session_id, student_id)
);