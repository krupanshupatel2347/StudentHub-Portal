-- =====================================================================
-- Database SQL Dump: studenthub_db
-- Subject: Web Development Frameworks (ITUE203) - Practical 15
-- Student Name: Krupanshu Patel
-- Roll No: 25DCS088
-- University: CHARUSAT / Devang Patel Institute of Advance Technology and Research (DEPSTAR)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- Drop existing tables if re-importing
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;

-- 1. Table structure for table `users`
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(10) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `year` TINYINT NOT NULL,
  `gender` VARCHAR(10) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table structure for table `user_roles`
CREATE TABLE `user_roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table structure for table `students`
CREATE TABLE `students` (
  `student_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(10) NOT NULL,
  `course` VARCHAR(50) DEFAULT 'B.Tech CSE',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table structure for table `events`
CREATE TABLE `events` (
  `event_id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_name` VARCHAR(200) NOT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME NULL,
  `event_venue` VARCHAR(200) NOT NULL,
  `event_description` TEXT NULL,
  `poster` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table structure for table `remember_tokens`
CREATE TABLE `remember_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `selector` VARCHAR(24) NOT NULL UNIQUE,
  `token_hash` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table structure for table `audit_logs`
CREATE TABLE `audit_logs` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Seed Data
-- =====================================================================

-- Default Admin & Student credentials:
-- Admin: krupanshupatel2347@gmail.com / Admin@123
-- Student: rahul@example.com / Student@123
INSERT INTO `users` (`user_id`, `full_name`, `email`, `mobile`, `password`, `course`, `year`, `gender`, `created_at`) VALUES
(1, 'Krupanshu Patel', 'krupanshupatel2347@gmail.com', '9876543210', '$2y$10$w0fR32m5Fq2Wb9d4L878eObJ2f7z6P3hK1a8C0o5m4E3r2.Y8z8a2', 'B.Tech CSE', 2, 'Male', '2026-08-05 10:15:00'),
(2, 'Rahul Patel', 'rahul@example.com', '9876543211', '$2y$10$w0fR32m5Fq2Wb9d4L878eObJ2f7z6P3hK1a8C0o5m4E3r2.Y8z8a2', 'B.Tech IT', 2, 'Male', '2026-08-12 11:20:00'),
(3, 'Priya Shah', 'priya@example.com', '9876543212', '$2y$10$w0fR32m5Fq2Wb9d4L878eObJ2f7z6P3hK1a8C0o5m4E3r2.Y8z8a2', 'B.Tech CSE', 3, 'Female', '2026-08-19 14:45:00'),
(4, 'Aakash Mehta', 'aakash@example.com', '9876543213', '$2y$10$w0fR32m5Fq2Wb9d4L878eObJ2f7z6P3hK1a8C0o5m4E3r2.Y8z8a2', 'BCA', 1, 'Male', '2026-08-27 16:30:00'),
(5, 'Sneha Joshi', 'sneha@example.com', '9876543214', '$2y$10$w0fR32m5Fq2Wb9d4L878eObJ2f7z6P3hK1a8C0o5m4E3r2.Y8z8a2', 'B.Tech CE', 2, 'Female', '2026-09-05 09:10:00');

-- Assign Roles
INSERT INTO `user_roles` (`user_id`, `role`) VALUES
(1, 'admin'),
(2, 'student'),
(3, 'student'),
(4, 'student'),
(5, 'student');

-- Seed Students
INSERT INTO `students` (`student_id`, `full_name`, `email`, `mobile`, `course`, `created_at`) VALUES
(1, 'Krupanshu Patel', 'krupanshupatel2347@gmail.com', '9876543210', 'B.Tech CSE', '2026-08-05 10:15:00'),
(2, 'Rahul Patel', 'rahul@example.com', '9876543211', 'B.Tech IT', '2026-08-12 11:20:00'),
(3, 'Priya Shah', 'priya@example.com', '9876543212', 'B.Tech CSE', '2026-08-19 14:45:00'),
(4, 'Aakash Mehta', 'aakash@example.com', '9876543213', 'BCA', '2026-08-27 16:30:00'),
(5, 'Sneha Joshi', 'sneha@example.com', '9876543214', 'B.Tech CE', '2026-09-05 09:10:00');

-- Seed Events
INSERT INTO `events` (`event_id`, `event_name`, `event_date`, `event_time`, `event_venue`, `event_description`, `created_at`) VALUES
(1, 'CHARUSAT Tech Fest 2026', '2026-10-05', '10:00:00', 'CHARUSAT Main Auditorium', 'A technical festival featuring coding, robotics, project exhibitions and technical competitions.', '2026-09-01 10:00:00'),
(2, 'Annual Sports Meet 2026', '2026-10-10', '08:00:00', 'CHARUSAT Sports Ground', 'Annual sports event including cricket, football, volleyball, badminton and athletics.', '2026-09-02 11:00:00'),
(3, 'Navratri Celebration', '2026-10-18', '19:00:00', 'CHARUSAT Campus Ground', 'A cultural celebration with traditional Garba, music and performances by students.', '2026-09-03 14:30:00'),
(4, 'Campus Placement Drive', '2026-10-22', '09:00:00', 'Placement Cell', 'Campus recruitment drive with leading tech companies offering internship and placement opportunities.', '2026-09-05 12:00:00');

-- Seed Initial Audit Logs
INSERT INTO `audit_logs` (`user_id`, `user_name`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'Krupanshu Patel', 'Admin Login', 'Administrator logged into the portal.', '127.0.0.1', '2026-10-05 16:21:00'),
(1, 'Krupanshu Patel', 'Viewed Admin Dashboard', 'Admin opened the Practical 14 dashboard.', '127.0.0.1', '2026-10-05 16:22:15'),
(1, 'Krupanshu Patel', 'Database Synchronization', 'Initial database schema and seeds verified.', '127.0.0.1', '2026-10-08 17:00:00');
