-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 29, 2026 at 06:55 PM
-- Server version: 8.0.46
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `exam_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int NOT NULL,
  `course_code` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credits` int NOT NULL DEFAULT '3',
  `lecturer_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_title`, `credits`, `lecturer_id`, `created_at`) VALUES
(1, 'CS101', 'Bachelor in Computer Science (Honours)', 3, 2, '2026-09-28 18:35:11'),
(2, 'CS205', 'Bachelor of Arts (Hons) in Applied English Language Studies', 3, 3, '2026-09-28 18:35:11'),
(3, 'CS310', 'Bachelor of Accountancy (Honours)', 3, 2, '2026-09-28 18:35:11'),
(4, 'CS320', 'Bachelor of Information Technology (Honours) in Cyber Security ', 3, 3, '2026-09-28 18:35:11'),
(5, 'CS405', 'Bachelor in Artificial Intelligence (Honours)', 4, 2, '2026-09-28 18:35:11'),
(6, 'BA150', 'Bachelor of Information Technology (Honours) in Computer Application Development', 3, 4, '2026-09-28 18:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

CREATE TABLE `examinations` (
  `exam_id` int NOT NULL,
  `course_id` int NOT NULL,
  `subject_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `venue` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('scheduled','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `examinations`
--

INSERT INTO `examinations` (`exam_id`, `course_id`, `subject_name`, `exam_date`, `start_time`, `end_time`, `venue`, `status`, `created_at`) VALUES
(1, 1, 'Introduction to Programming', '2026-11-03', '12:02:00', '14:00:00', 'Exam Hall B', 'scheduled', '2026-09-28 18:35:11'),
(2, 2, 'Database Systems', '2026-11-03', '14:00:00', '16:00:00', 'Exam Hall B', 'scheduled', '2026-09-28 18:35:11'),
(3, 3, 'Web API Development', '2026-11-04', '09:00:00', '11:00:00', 'Computer Lab 1', 'scheduled', '2026-09-28 18:35:11'),
(4, 4, 'Cyber Security Fundamentals', '2026-11-05', '14:00:00', '16:00:00', 'Computer Lab 1', 'scheduled', '2026-09-28 18:35:11'),
(5, 5, 'Ethical Hacking', '2026-11-06', '09:00:00', '12:00:00', 'Main Auditorium', 'scheduled', '2026-09-28 18:35:11'),
(6, 6, 'Business Analytics', '2026-11-14', '10:00:00', '12:00:00', 'Exam Hall A', 'scheduled', '2026-09-28 18:35:11'),
(8, 4, 'Cyber Security Fundamentals', '2026-10-10', '11:33:00', '01:33:00', 'Exam Hall A', 'scheduled', '2026-09-29 15:33:42'),
(9, 4, 'Cyber Security Fundamentals', '2026-10-10', '10:00:00', '12:00:00', 'Exam Hall C', 'scheduled', '2026-09-29 15:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 5, 'Examination Timetable Released', 'Your Semester 1 examination timetable is now available.', 0, '2026-09-28 18:35:11'),
(2, 6, 'Examination Timetable Released', 'Your Semester 1 examination timetable is now available.', 1, '2026-09-28 18:35:11'),
(3, 7, 'Results Published', 'Your results for CS101 and CS310 have been published.', 0, '2026-09-28 18:35:11'),
(4, 2, 'Result Submission Reminder', 'Please finalise CS101 mark entry before 25 November.', 0, '2026-09-28 18:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int NOT NULL,
  `student_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `course_id` int NOT NULL,
  `marks` decimal(5,2) NOT NULL,
  `grade` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_point` decimal(3,2) NOT NULL,
  `published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `student_id`, `subject_id`, `course_id`, `marks`, `grade`, `grade_point`, `published`, `created_at`) VALUES
(1, 5, 1, 1, 85.50, 'A', 4.00, 1, '2026-09-28 18:35:11'),
(2, 5, 2, 2, 78.00, 'A-', 3.67, 1, '2026-09-28 18:35:11'),
(3, 5, 3, 3, 91.00, 'A', 4.00, 1, '2026-09-28 18:35:11'),
(4, 6, 1, 1, 72.00, 'B+', 3.33, 1, '2026-09-28 18:35:11'),
(5, 6, 2, 2, 65.00, 'B', 3.00, 1, '2026-09-28 18:35:11'),
(6, 6, 0, 4, 58.00, 'C+', 2.33, 1, '2026-09-28 18:35:11'),
(7, 7, 1, 1, 80.00, 'A-', 3.67, 1, '2026-09-28 18:35:11'),
(8, 7, 3, 3, 88.00, 'A', 4.00, 1, '2026-09-28 18:35:11'),
(9, 8, 0, 5, 69.00, 'B', 3.00, 0, '2026-09-28 18:35:11'),
(10, 9, 0, 6, 90.00, 'A', 3.33, 0, '2026-09-28 18:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int NOT NULL,
  `subject_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `course_id`) VALUES
(1, 'INFORMATION TECHNOLOGY', 1),
(2, 'DATABASE SYSTEMS', 2),
(3, 'WEB API DEVELOPMENT', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','lecturer','student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `matric_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_ic` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `matric_no`, `no_ic`, `phone`, `is_active`, `created_at`) VALUES
(1, 'Ahmad Faizal', 'admin@uni.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, '900101010101', '012-1111111', 1, '2026-09-28 18:35:11'),
(2, 'Dr. Tan Wei Ming', 'tan@uni.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', NULL, '800202020202', '012-2222222', 1, '2026-09-28 18:35:11'),
(3, 'Dr. Lee Chong Hao', 'lee@uni.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', NULL, '810303030303', '012-3333333', 1, '2026-09-28 18:35:11'),
(4, 'Ms. Wong Li Ying', 'wong@uni.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', NULL, '820404040404', '012-4444444', 1, '2026-09-28 18:35:11'),
(5, 'Siti Nurhaliza', 'siti@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S2023001', '010203040506', '013-5555555', 1, '2026-09-28 18:35:11'),
(6, 'Haziq Aziz', 'haziq@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S2023002', '020304050607', '013-6666666', 1, '2026-09-28 18:35:11'),
(7, 'Chong Mei Ling', 'mei@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S2023003', '030405060708', '013-7777777', 1, '2026-09-28 18:35:11'),
(8, 'Arun Prakash', 'arun@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S2023004', '040506070809', '013-8888888', 1, '2026-09-28 18:35:11'),
(9, 'Nur Aisyah', 'aisyah@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S2023005', '050607080910', '013-9999999', 1, '2026-09-28 18:35:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `examinations`
--
ALTER TABLE `examinations`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `unique_result` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `examinations`
--
ALTER TABLE `examinations`
  MODIFY `exam_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `examinations`
--
ALTER TABLE `examinations`
  ADD CONSTRAINT `examinations_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
