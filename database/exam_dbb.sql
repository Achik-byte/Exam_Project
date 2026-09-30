-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 30, 2026 at 08:39 PM
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
  `subject_id` int NOT NULL,
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

INSERT INTO `examinations` (`exam_id`, `course_id`, `subject_id`, `exam_date`, `start_time`, `end_time`, `venue`, `status`, `created_at`) VALUES
(10, 1, 1, '2026-11-03', '09:00:00', '11:00:00', 'Exam Hall A', 'scheduled', '2026-09-30 11:21:18'),
(11, 1, 4, '2026-11-04', '09:00:00', '11:00:00', 'Exam Hall A', 'scheduled', '2026-09-30 11:21:18'),
(12, 1, 5, '2026-11-05', '14:00:00', '16:00:00', 'Exam Hall B', 'scheduled', '2026-09-30 11:21:18'),
(13, 1, 6, '2026-11-06', '09:00:00', '11:00:00', 'Exam Hall B', 'scheduled', '2026-09-30 11:21:18'),
(14, 2, 2, '2026-11-03', '14:00:00', '16:00:00', 'Exam Hall C', 'scheduled', '2026-09-30 11:21:18'),
(15, 2, 7, '2026-11-04', '14:00:00', '16:00:00', 'Exam Hall C', 'scheduled', '2026-09-30 11:21:18'),
(16, 2, 8, '2026-11-05', '09:00:00', '11:00:00', 'Exam Hall A', 'scheduled', '2026-09-30 11:21:18'),
(17, 2, 9, '2026-11-06', '14:00:00', '16:00:00', 'Exam Hall B', 'scheduled', '2026-09-30 11:21:18'),
(18, 3, 3, '2026-11-03', '09:00:00', '11:00:00', 'Computer Lab 1', 'scheduled', '2026-09-30 11:21:18'),
(19, 3, 10, '2026-11-04', '09:00:00', '11:00:00', 'Computer Lab 2', 'scheduled', '2026-09-30 11:21:18'),
(20, 3, 11, '2026-11-05', '14:00:00', '16:00:00', 'Computer Lab 1', 'scheduled', '2026-09-30 11:21:18'),
(21, 3, 12, '2026-11-06', '09:00:00', '11:00:00', 'Computer Lab 2', 'scheduled', '2026-09-30 11:21:18'),
(22, 4, 13, '2026-11-03', '14:00:00', '16:00:00', 'Main Auditorium', 'scheduled', '2026-09-30 11:21:18'),
(23, 4, 14, '2026-11-04', '14:00:00', '16:00:00', 'Main Auditorium', 'scheduled', '2026-09-30 11:21:18'),
(24, 4, 15, '2026-11-05', '09:00:00', '11:00:00', 'Computer Lab 1', 'scheduled', '2026-09-30 11:21:18'),
(25, 4, 16, '2026-11-06', '14:00:00', '16:00:00', 'Computer Lab 2', 'scheduled', '2026-09-30 11:21:18'),
(26, 5, 17, '2026-11-03', '09:00:00', '12:00:00', 'Main Auditorium', 'scheduled', '2026-09-30 11:21:18'),
(27, 5, 18, '2026-11-04', '09:00:00', '12:00:00', 'Main Auditorium', 'scheduled', '2026-09-30 11:21:18'),
(28, 5, 19, '2026-11-05', '09:00:00', '12:00:00', 'Exam Hall A', 'scheduled', '2026-09-30 11:21:18'),
(29, 5, 20, '2026-11-06', '09:00:00', '12:00:00', 'Exam Hall B', 'scheduled', '2026-09-30 11:21:18'),
(30, 6, 21, '2026-11-03', '14:00:00', '16:00:00', 'Exam Hall C', 'scheduled', '2026-09-30 11:21:18'),
(31, 6, 22, '2026-11-04', '09:00:00', '11:00:00', 'Computer Lab 1', 'scheduled', '2026-09-30 11:21:18'),
(32, 6, 23, '2026-11-05', '14:00:00', '16:00:00', 'Computer Lab 2', 'scheduled', '2026-09-30 11:21:18'),
(33, 6, 24, '2026-11-06', '14:00:00', '16:00:00', 'Exam Hall C', 'scheduled', '2026-09-30 11:21:18');

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
(1, 5, '', '', 1, '2026-09-28 18:35:11'),
(2, 6, 'Examination Timetable Released', 'Your Semester 1 examination timetable is now available.', 1, '2026-09-28 18:35:11'),
(3, 7, '', '', 1, '2026-09-28 18:35:11'),
(4, 2, '', '', 1, '2026-09-28 18:35:11'),
(5, 2, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(6, 3, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(7, 4, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(8, 5, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(9, 6, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(10, 7, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(11, 8, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(12, 9, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(13, 15, 'EXAM LIST', 'APAAN TUH', 1, '2026-09-30 17:54:53'),
(14, 16, 'EXAM LIST', 'APAAN TUH', 0, '2026-09-30 17:54:53'),
(15, 5, 'PEMBETULAN', 'HAHAHAHAHA', 0, '2026-09-30 17:55:44'),
(16, 7, 'PEMBETULAN', 'HAHAHAHAHA', 0, '2026-09-30 17:55:44'),
(17, 9, 'PEMBETULAN', 'HAHAHAHAHA', 0, '2026-09-30 17:55:44'),
(18, 16, 'PEMBETULAN', 'HAHAHAHAHA', 0, '2026-09-30 17:55:44'),
(19, 1, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(20, 2, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(21, 3, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(22, 4, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(23, 5, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(24, 6, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(25, 7, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(26, 8, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(27, 9, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(28, 15, 'nah', 'amik', 1, '2026-09-30 18:03:07'),
(29, 16, 'nah', 'amik', 0, '2026-09-30 18:03:07'),
(30, 5, 'dah', 'yes', 0, '2026-09-30 18:03:50'),
(31, 7, 'dah', 'yes', 0, '2026-09-30 18:03:50'),
(32, 9, 'dah', 'yes', 0, '2026-09-30 18:03:50'),
(33, 16, 'dah', 'yes', 0, '2026-09-30 18:03:50'),
(34, 2, 'dah', 'yes', 0, '2026-09-30 18:03:50');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int NOT NULL,
  `student_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `course_id` int NOT NULL,
  `marks` decimal(5,2) DEFAULT NULL,
  `grade` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_point` decimal(3,2) DEFAULT '0.00',
  `published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `student_id`, `subject_id`, `course_id`, `marks`, `grade`, `grade_point`, `published`, `created_at`) VALUES
(11, 5, 1, 1, 85.50, 'A', 4.00, 1, '2026-09-30 11:21:34'),
(12, 5, 4, 1, 78.00, 'A-', 3.67, 1, '2026-09-30 11:21:34'),
(13, 5, 5, 1, 91.00, 'A', 4.00, 1, '2026-09-30 11:21:34'),
(14, 5, 6, 1, 82.00, 'A', 4.00, 1, '2026-09-30 11:21:34'),
(15, 6, 2, 2, 72.00, 'B+', 3.33, 1, '2026-09-30 11:21:34'),
(16, 6, 7, 2, 65.00, 'B', 3.00, 1, '2026-09-30 11:21:34'),
(17, 6, 8, 2, 58.00, 'C+', 2.33, 1, '2026-09-30 11:21:34'),
(18, 6, 9, 2, 75.00, 'A-', 3.67, 1, '2026-09-30 11:21:34'),
(19, 7, 3, 3, 88.00, 'A', 4.00, 1, '2026-09-30 11:21:34'),
(20, 7, 10, 3, 80.00, 'A-', 3.67, 1, '2026-09-30 11:21:34'),
(21, 7, 11, 3, 76.00, 'A-', 3.67, 1, '2026-09-30 11:21:34'),
(22, 7, 12, 3, 70.00, 'B+', 3.33, 1, '2026-09-30 11:21:34'),
(23, 8, 13, 4, 69.00, 'B', 3.00, 0, '2026-09-30 11:21:34'),
(24, 8, 14, 4, 74.00, 'B+', 3.33, 0, '2026-09-30 11:21:34'),
(25, 8, 15, 4, 81.00, 'A', 4.00, 0, '2026-09-30 11:21:34'),
(26, 8, 16, 4, 66.00, 'B', 3.00, 0, '2026-09-30 11:21:34'),
(27, 9, 17, 5, 90.00, 'A', 4.00, 0, '2026-09-30 11:21:34'),
(28, 9, 18, 5, 87.00, 'A', 4.00, 0, '2026-09-30 11:21:34'),
(29, 9, 19, 5, 79.00, 'A-', 3.67, 0, '2026-09-30 11:21:34'),
(30, 9, 20, 5, 83.00, 'A', 4.00, 0, '2026-09-30 11:21:34'),
(34, 15, 23, 6, NULL, NULL, NULL, 0, '2026-09-30 12:28:25'),
(35, 15, 24, 6, NULL, NULL, NULL, 0, '2026-09-30 12:28:25'),
(36, 16, 20, 5, NULL, NULL, NULL, 0, '2026-09-30 16:43:46'),
(37, 16, 18, 5, NULL, NULL, NULL, 0, '2026-09-30 16:43:46'),
(38, 16, 17, 5, NULL, NULL, NULL, 0, '2026-09-30 16:43:46'),
(39, 15, 21, 6, NULL, NULL, NULL, 0, '2026-09-30 17:41:01'),
(40, 15, 22, 6, NULL, NULL, NULL, 0, '2026-09-30 17:41:01'),
(44, 16, 19, 5, NULL, NULL, NULL, 0, '2026-09-30 17:45:12');

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
(3, 'WEB API DEVELOPMENT', 3),
(4, 'INTRODUCTION TO PROGRAMMING', 1),
(5, 'DATA STRUCTURES AND ALGORITHMS', 1),
(6, 'OPERATING SYSTEMS', 1),
(7, 'ACADEMIC WRITING', 2),
(8, 'ENGLISH LITERATURE', 2),
(9, 'LINGUISTICS', 2),
(10, 'FINANCIAL ACCOUNTING', 3),
(11, 'MANAGEMENT ACCOUNTING', 3),
(12, 'AUDITING PRINCIPLES', 3),
(13, 'NETWORK SECURITY', 4),
(14, 'CRYPTOGRAPHY', 4),
(15, 'ETHICAL HACKING', 4),
(16, 'DIGITAL FORENSICS', 4),
(17, 'MACHINE LEARNING', 5),
(18, 'DEEP LEARNING', 5),
(19, 'NATURAL LANGUAGE PROCESSING', 5),
(20, 'COMPUTER VISION', 5),
(21, 'MOBILE APP DEVELOPMENT', 6),
(22, 'WEB DEVELOPMENT', 6),
(23, 'SOFTWARE ENGINEERING', 6),
(24, 'UI UX DESIGN', 6);

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
(9, 'Nur Aisyah', 'aisyah@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S2023005', '050607080910', '013-9999999', 1, '2026-09-28 18:35:11'),
(15, 'amir', 'amir@student.edu', '$2y$10$6KLrHMIkvy8HJJbo3f9VAuNZ.0eLWP1vjmysNkIajB..S0p1O3WHC', 'student', 'S2023012', '030086080965', '01234576896', 1, '2026-09-30 12:28:25'),
(16, 'lia', 'lia@student.edu', '$2y$10$uQaFrhMqRRVQAN1M7NVVL..5mWySUJc45HyW.uyOSl7P/tAnuRdrK', 'student', 'S2023013', '005678080456', '0113456789', 1, '2026-09-30 16:43:46');

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
  ADD KEY `course_id` (`course_id`),
  ADD KEY `examinations_subject_fk` (`subject_id`);

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
  ADD UNIQUE KEY `unique_student_subject` (`student_id`,`subject_id`),
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
  MODIFY `exam_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  ADD CONSTRAINT `examinations_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `examinations_subject_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

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
