-- ============================================================================
-- UNIVERSITY STUDENT INFORMATION AND ACADEMIC MANAGEMENT SYSTEM
-- Database Schema & Initial Seed Data
-- Compatible with MySQL / MariaDB (XAMPP / WAMP)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `university_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `university_db`;

-- ----------------------------------------------------------------------------
-- Table: users (Module 1: User Login and Registration)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'Registrar',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default demo user: username = "admin", password = "password"
-- (New users can also sign up directly from the splash login page)
INSERT INTO `users` (`full_name`, `username`, `email`, `password_hash`, `role`)
VALUES (
  'Academic Registrar',
  'admin',
  'registrar@university.edu',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Administrator'
)
ON DUPLICATE KEY UPDATE `username` = `username`;

-- ----------------------------------------------------------------------------
-- Table: students (Module 2 & 3: Student Management & MySQL Storage)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reg_no` VARCHAR(20) NOT NULL,
  `student_name` VARCHAR(100) NOT NULL,
  `course` VARCHAR(80) NOT NULL,
  `semester` TINYINT UNSIGNED NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `contact_number` VARCHAR(15) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_students_reg_no` (`reg_no`),
  UNIQUE KEY `uq_students_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Student Records
INSERT INTO `students` (`reg_no`, `student_name`, `course`, `semester`, `email`, `contact_number`) VALUES
('BCA2024001', 'Aarav Sharma', 'BCA', 5, 'aarav.sharma@university.edu', '9876543210'),
('BCA2024002', 'Diya Nair', 'BCA', 5, 'diya.nair@university.edu', '9845012345'),
('BCA2025014', 'Rohan Verma', 'B.Sc Computer Science', 3, 'rohan.verma@university.edu', '9741122334'),
('MCA2025008', 'Meera Krishnan', 'MCA', 3, 'meera.krishnan@university.edu', '9900112233'),
('BCA2026003', 'Kabir Menon', 'BCA', 1, 'kabir.menon@university.edu', '9611223344')
ON DUPLICATE KEY UPDATE `reg_no` = `reg_no`;
