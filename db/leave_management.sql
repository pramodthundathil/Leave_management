-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 08:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `leave_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$DJXkRgSJYJhkW4V8CkHd7exlPBVmUBD7/mtII5BzOTYQa01Hez1oa', '2025-03-29 08:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_certificates`
--

CREATE TABLE `employee_certificates` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_certificates`
--

INSERT INTO `employee_certificates` (`id`, `employee_id`, `file_name`, `upload_date`, `file_path`) VALUES
(7, 24, 'sprint7_burndown_chart_updated.png', '2025-04-08 09:13:09', 'C:\\xampp\\htdocs\\ELP\\employee/uploads/sprint7_burndown_chart_updated.png');

-- --------------------------------------------------------

--
-- Table structure for table `leavetypes`
--

CREATE TABLE `leavetypes` (
  `LeaveTypeID` int(11) NOT NULL,
  `LeaveName` varchar(100) NOT NULL,
  `MaxPerYear` int(11) DEFAULT NULL,
  `MaxAtATime` int(11) DEFAULT NULL,
  `Accumulable` tinyint(1) DEFAULT 1,
  `GenderSpecific` enum('All','Male','Female') DEFAULT 'All',
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leavetypes`
--

INSERT INTO `leavetypes` (`LeaveTypeID`, `LeaveName`, `MaxPerYear`, `MaxAtATime`, `Accumulable`, `GenderSpecific`, `Description`) VALUES
(1, 'Earned Leave', 30, 180, 1, 'All', '1/11th of duty period; max 300 days accumulation'),
(2, 'Half Pay Leave', 20, NULL, 1, 'All', '20 days per year, paid at half salary'),
(3, 'Casual Leave', 20, NULL, 0, 'All', 'Not accumulable, 20 days/year'),
(5, 'Maternity Leave', 180, 180, 0, 'Female', '180 days per pregnancy'),
(7, 'Paternity Leave', 15, 15, 0, 'Male', '15 days after childbirth'),
(8, 'Special Leave', NULL, NULL, 0, 'All', 'Special cases like chemotherapy, etc.');

-- --------------------------------------------------------

--
-- Table structure for table `leave_balance`
--

CREATE TABLE `leave_balance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type` varchar(255) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `leave_type` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `medical_certificate` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` varchar(1000) DEFAULT NULL,
  `days` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `user_id`, `leave_type_id`, `leave_type`, `start_date`, `end_date`, `reason`, `medical_certificate`, `status`, `approved_by`, `approval_date`, `applied_at`, `submitted_at`, `created_at`, `rejection_reason`, `days`) VALUES
(31, 30, 3, '', '2025-04-25', '2025-04-30', 'Test', NULL, 'approved', 29, '2025-04-25', '2025-04-25 07:13:47', '2025-04-25 07:13:47', '2025-04-25 07:13:47', NULL, 6),
(32, 30, 1, '', '2025-04-26', '2025-04-28', '0', NULL, 'rejected', NULL, NULL, '2025-04-25 08:04:06', '2025-04-25 08:04:06', '2025-04-25 08:04:06', NULL, 3),
(33, 30, 7, '', '2025-04-25', '2025-04-26', '0', NULL, 'rejected', NULL, NULL, '2025-04-25 08:14:37', '2025-04-25 08:14:37', '2025-04-25 08:14:37', NULL, 2),
(34, 30, 2, '', '2025-04-26', '2025-04-30', 'Test', NULL, 'approved', 29, '2025-04-25', '2025-04-25 09:19:16', '2025-04-25 09:19:16', '2025-04-25 09:19:16', NULL, 5),
(37, 30, 1, '', '2025-04-29', '2025-05-02', 'Test', NULL, 'approved', 29, '2025-04-28', '2025-04-28 06:08:49', '2025-04-28 06:08:49', '2025-04-28 06:08:49', NULL, 4),
(38, 30, 1, '', '2025-04-28', '2025-05-02', 'test', NULL, 'approved', 29, '2025-04-28', '2025-04-28 06:28:11', '2025-04-28 06:28:11', '2025-04-28 06:28:11', NULL, 5),
(39, 30, 3, '', '2025-04-28', '2025-04-30', 'test', NULL, 'approved', 29, '2025-04-28', '2025-04-28 06:29:12', '2025-04-28 06:29:12', '2025-04-28 06:29:12', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userleaves`
--

CREATE TABLE `userleaves` (
  `UserLeaveID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `LeaveTypeID` int(11) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `TotalEligible` int(11) DEFAULT NULL,
  `Taken` int(11) DEFAULT 0,
  `Balance` int(11) GENERATED ALWAYS AS (`TotalEligible` - `Taken`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userleaves`
--

INSERT INTO `userleaves` (`UserLeaveID`, `UserID`, `LeaveTypeID`, `Year`, `TotalEligible`, `Taken`) VALUES
(1, 24, 1, 2025, 30, 0),
(2, 25, 1, 2025, 30, 0),
(3, 26, 1, 2025, 30, 0),
(4, 28, 1, 2025, 30, 0),
(5, 29, 1, 2025, 30, 0),
(6, 30, 1, 2025, 30, 9),
(7, 24, 2, 2025, 20, 0),
(8, 25, 2, 2025, 20, 0),
(9, 26, 2, 2025, 20, 0),
(10, 28, 2, 2025, 20, 0),
(11, 29, 2, 2025, 20, 0),
(12, 30, 2, 2025, 20, 5),
(13, 24, 3, 2025, 20, 0),
(14, 25, 3, 2025, 20, 0),
(15, 26, 3, 2025, 20, 0),
(16, 28, 3, 2025, 20, 0),
(17, 29, 3, 2025, 20, 0),
(18, 30, 3, 2025, 20, 9),
(25, 28, 5, 2025, 180, 0),
(27, 29, 7, 2025, 15, 0),
(28, 30, 7, 2025, 15, 0),
(29, 24, 8, 2025, 0, 0),
(30, 25, 8, 2025, 0, 0),
(31, 26, 8, 2025, 0, 0),
(32, 28, 8, 2025, 0, 0),
(33, 29, 8, 2025, 0, 0),
(34, 30, 8, 2025, 0, 0),
(64, 31, 1, 2025, 30, 0),
(65, 31, 2, 2025, 20, 0),
(66, 31, 3, 2025, 20, 0),
(68, 31, 5, 2025, 180, 0),
(70, 31, 8, 2025, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('employee','admin') NOT NULL DEFAULT 'employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `designation`, `mobile`, `gender`, `password`, `role`) VALUES
(24, 'haritha', 'haritha@gmail.com', 'New', '', '', '$2y$10$95199q8XjOt9Oi.s63FE0eAD4Cv/qOgqDkfEhptJSvFTM72d06Y0W', 'employee'),
(25, 'appu', 'appu@gmail.com', 'junior supeintendent ', '', '', '$2y$10$o3Hld96N537qYYkV1CisD.c9l31yxdoWfV2InI8nfehGnR22RBqgy', 'employee'),
(26, 'oo', 'oo@gmail.com', 'junior supeintendent ', '', '', '$2y$10$nnUxs5mlwbkZX3U9d.MbWOJVyYbDfpVTZFFFu3AQZpjhGBmAsDnFO', 'employee'),
(28, 'beena', 'beena@gmail.com', 'office assistant ', '7894561235', 'Female', '$2y$10$8ym4h5flcbhgdzJwNCYOG.LMm7WfWJP3O9zMyj5.DnVq3Hem6Azja', 'employee'),
(29, 'Pramod Gopinath', 'gopinath.pramod@gmail.com', 'Developer', '9141109785', 'Male', '$2y$10$qzNZ7K5mt79h.I1zlHYh0eg9k27Rp1NXJVgQrOY8XBQCxJgbepZOu', 'admin'),
(30, 'test', 'test@gmail.com', 'Tester', '8089807696', 'Male', '$2y$10$EVsZzAlant9jlc8glqDhFeSb6dgWChWF.KBxqo8NI/pnzgXKP6Zzy', 'employee'),
(31, 'Test User', 'testuser@gmail.com', 'SI', '9207594314', 'Female', '$2y$10$HwZdAb1Ae15X9H9BdQAzu.upMIlnYqIxOoDaTjfjlsYlQiOSVeZxS', 'employee');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employee_certificates`
--
ALTER TABLE `employee_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leavetypes`
--
ALTER TABLE `leavetypes`
  ADD PRIMARY KEY (`LeaveTypeID`);

--
-- Indexes for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `fk_leave_type_id` (`leave_type_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `userleaves`
--
ALTER TABLE `userleaves`
  ADD PRIMARY KEY (`UserLeaveID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `userleaves_ibfk_2` (`LeaveTypeID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_certificates`
--
ALTER TABLE `employee_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leavetypes`
--
ALTER TABLE `leavetypes`
  MODIFY `LeaveTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_balance`
--
ALTER TABLE `leave_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userleaves`
--
ALTER TABLE `userleaves`
  MODIFY `UserLeaveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_certificates`
--
ALTER TABLE `employee_certificates`
  ADD CONSTRAINT `employee_certificates_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD CONSTRAINT `leave_balance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_leave_type_id` FOREIGN KEY (`leave_type_id`) REFERENCES `leavetypes` (`LeaveTypeID`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `userleaves`
--
ALTER TABLE `userleaves`
  ADD CONSTRAINT `userleaves_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `userleaves_ibfk_2` FOREIGN KEY (`LeaveTypeID`) REFERENCES `leavetypes` (`LeaveTypeID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
