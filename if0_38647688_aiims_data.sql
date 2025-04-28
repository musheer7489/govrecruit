-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql208.infinityfree.com
-- Generation Time: Apr 28, 2025 at 06:34 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38647688_aiims_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `advertisements`
--

CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL,
  `advertisement_number` varchar(50) NOT NULL,
  `application_start_date` date NOT NULL,
  `application_end_date` date NOT NULL,
  `last_date_payment` date NOT NULL,
  `detail_link` varchar(255) DEFAULT NULL,
  `apply_link` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advertisements`
--

INSERT INTO `advertisements` (`id`, `advertisement_number`, `application_start_date`, `application_end_date`, `last_date_payment`, `detail_link`, `apply_link`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'HSCC/SRD/2025/01', '2025-04-10', '2025-05-02', '2025-05-02', 'pdf/HSCC-SRD-2025-01.pdf', 'register.php', 1, '2025-04-11 05:19:23', '2025-04-11 05:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` enum('application','technical','eligibility','status','other') NOT NULL,
  `message` text NOT NULL,
  `application_id` varchar(20) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','in_progress','resolved') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_submissions`
--

INSERT INTO `contact_submissions` (`id`, `first_name`, `last_name`, `email`, `phone`, `subject`, `message`, `application_id`, `submission_date`, `status`) VALUES
(1, 'Mohd', 'Moin', 'user1@user.com', '9090989890', 'application', 'I Have a big query', '', '2025-04-03 06:06:55', 'new'),
(2, 'Mohd', 'Moin', 'user1@user.com', '9090989890', 'application', 'I Have a big query', '', '2025-04-03 06:08:36', 'resolved'),
(3, 'Mohd', 'Moin', 'user2@user.com', '9759773601', 'application', 'I have a query', '', '2025-04-03 06:09:36', 'in_progress'),
(4, 'Mohd', 'Moin', 'musheer.fready@gmail.com', '9759773601', 'technical', 'I have a query', '', '2025-04-03 06:14:04', 'new'),
(5, 'Mohd', 'Moin', 'user1@user.com', '9090989890', 'application', 'How to COMPLETE Form', '', '2025-04-03 06:17:23', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `type` enum('new','reminder','result','alert') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `link`, `created_at`, `is_active`, `type`) VALUES
(6, 'Recruitment for Advt No.: - HSCC/SRD/2025/01', 'pdf/HSCC-SRD-2025-01.pdf', '2025-04-11 05:10:28', 1, 'new');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('success','failed') DEFAULT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `payment_status`, `transaction_id`, `order_id`, `created_at`) VALUES
(1, 1000621049, '100.00', 'success', 'Pay_suye7ewrhi', 'id987r98we', '2025-04-13 18:20:45');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `advertisement_id` int(11) NOT NULL,
  `post_name` varchar(255) NOT NULL,
  `eligibility` text DEFAULT NULL,
  `total_vacancies` int(11) DEFAULT 0,
  `vacancies_general` int(11) DEFAULT 0,
  `vacancies_obc` int(11) DEFAULT 0,
  `vacancies_sc` int(11) DEFAULT 0,
  `vacancies_st` int(11) DEFAULT 0,
  `vacancies_ews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `advertisement_id`, `post_name`, `eligibility`, `total_vacancies`, `vacancies_general`, `vacancies_obc`, `vacancies_sc`, `vacancies_st`, `vacancies_ews`, `created_at`, `updated_at`) VALUES
(27, 3, 'General Manager', 'Degree/Diploma in Engineering/Architecture or MBA in Construction/Healthcare\\r\\nManagement.', 45, 18, 12, 7, 3, 5, '2025-04-11 05:19:23', '2025-04-11 05:19:23'),
(28, 3, 'Sub Statistical Officer/Block Statistical Officer', 'Bachelorâ€™s Degree in Statistics/Mathematics/Economics (with Statistics as a\\r\\nsubject) OR Postgraduate Diploma in Applied Statistics.', 62, 25, 17, 9, 5, 6, '2025-04-11 05:19:23', '2025-04-11 05:19:23'),
(29, 3, 'Diploma Technician', 'Diploma in Civil/Electrical/Mechanical Engineering (3 years).', 30, 12, 8, 5, 2, 3, '2025-04-11 05:19:23', '2025-04-11 05:19:23'),
(30, 3, 'Junior Assistant', 'Graduate in any discipline with 50% marks.', 50, 20, 13, 8, 4, 5, '2025-04-11 05:19:23', '2025-04-11 05:19:23'),
(31, 3, 'Multi-Tasking Staff (MTS)', 'Class 10th Pass and Basic knowledge of office support work.', 91, 37, 24, 14, 7, 9, '2025-04-11 05:19:23', '2025-04-11 05:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `job_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `job_data`, `name`, `email`, `password`, `otp`, `otp_expiry`, `is_verified`, `mobile`, `role`, `failed_attempts`, `locked_until`, `personal_info`, `address`, `education`, `experience`, `photo_signature`, `is_final_submitted`, `reset_otp`, `reset_otp_expiry`, `created_at`) VALUES
(1000621048, '{\"advertisement_number\":\"HSCC\\/SRD\\/2025\\/01\",\"applications\":[{\"post_id\":\"27\",\"post_title\":\"General Manager\",\"qualifications\":\"Degree\\/Diploma in Engineering\\/Architecture or MBA in Construction\\/Healthcare\\\\r\\\\nManagement.\"},{\"post_id\":\"31\",\"post_title\":\"Multi-Tasking Staff (MTS)\",\"qualifications\":\"Class 10th Pass and Basic knowledge of office support work.\"}]}', 'Mohd Moin', 'musheer.fready@gmail.com', '$2y$10$B7d9PTnMkf40GVqiKNVdluq6FQLdg3j8fTZOm4pZHKcTJGuos6lqS', NULL, NULL, 1, '9809809800', 'user', 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-04-12 06:22:35'),
(1000621049, '{\"advertisement_number\":\"HSCC\\/SRD\\/2025\\/01\",\"applications\":[{\"post_id\":\"27\",\"post_title\":\"General Manager\",\"qualifications\":\"Degree\\/Diploma in Engineering\\/Architecture or MBA in Construction\\/Healthcare\\\\r\\\\nManagement.\"}]}', 'Mohd mohsin ', 'raja.shekh02@gmail.com', '$2y$10$vuf0KzLlUJGtlhBTfZAfGuz432AysGcOSyZeqTr7UC9cVbKJaajgu', NULL, NULL, 1, '8218387957', 'user', 0, NULL, '{\"fullname\":\"Mohd mohsin\",\"email\":\"1995-04-01\",\"mobile\":\"Male\",\"fathername\":\"Abdul aziz\",\"mothername\":\"Wakeelan begam\",\"gender\":\"Male\",\"dob\":\"1995-04-01\",\"aadhar\":\"0000000000\",\"category\":\"General\",\"marital\":\"Unmarried\",\"nationality\":\"Indian\",\"exman\":\"No\",\"disability\":\"No\"}', '{\"full_address\":\"Mohalla prem nagar\",\"state\":\"Uttar Pradesh\",\"city\":\"Bareilly\",\"pincode\":\"262406\"}', '{\"matriculation\":{\"passing_year\":\"2009\",\"college\":\"Skic\",\"board\":\"Up board\",\"percentage\":\"52\"},\"intermediate\":{\"passing_year\":\"2011\",\"college\":\"Skic\",\"board\":\"Up\",\"percentage\":\"52\"},\"graduation\":{\"passing_year\":\"2019\",\"college\":\"Bly\",\"board\":\"Mjpru\",\"percentage\":\"48\"}}', '[{\"job_title\":\"NA\",\"company\":\"\",\"start_date\":\"\",\"end_date\":\"\",\"experience_years\":\"\"}]', '{\"photo\":\"1000621049_photo.jpg\",\"signature\":\"1000621049_signature.jpg\"}', 1, NULL, NULL, '2025-04-13 18:17:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advertisements`
--
ALTER TABLE `advertisements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `advertisement_number` (`advertisement_number`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advertisement_id` (`advertisement_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advertisements`
--
ALTER TABLE `advertisements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`advertisement_id`) REFERENCES `advertisements` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
