-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2024 at 09:08 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bankloan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `loanofficers`
--

CREATE TABLE `loanofficers` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loanofficers`
--

INSERT INTO `loanofficers` (`id`, `username`, `password`, `email`, `phone_number`, `created_at`, `updated_at`) VALUES
(1, 'officer12', '$2y$10$4hmsIshO2wTQJjss3W3ASOJlK5hO07twjRTzryWyjHsi2pd.p1Nnq', 'officer@gmail.com', '31765268270', '2024-11-25 06:31:20', '2024-11-25 06:34:09');

-- --------------------------------------------------------

--
-- Table structure for table `loan_applications`
--

CREATE TABLE `loan_applications` (
  `application_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `loan_type_id` int(11) NOT NULL,
  `amount_requested` decimal(10,2) NOT NULL,
  `application_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `assigned_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_applications`
--

INSERT INTO `loan_applications` (`application_id`, `customer_id`, `loan_type_id`, `amount_requested`, `application_status`, `feedback`, `assigned_officer_id`, `created_at`, `updated_at`) VALUES
(1, 4, 3, 450000.00, 'approved', 'Approved apploication ', 1, '2024-11-25 06:24:41', '2024-11-25 07:56:15'),
(2, 4, 9, 2321.00, 'approved', 'dsa', 1, '2024-11-25 07:11:17', '2024-11-25 07:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `loan_disbursements`
--

CREATE TABLE `loan_disbursements` (
  `disbursement_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `amount_disbursed` decimal(15,2) DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `repayment_status` enum('active','closed','overdue') NOT NULL,
  `transaction_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_disbursements`
--

INSERT INTO `loan_disbursements` (`disbursement_id`, `application_id`, `amount_disbursed`, `disbursement_date`, `repayment_status`, `transaction_image`, `created_at`, `updated_at`) VALUES
(1, 1, 23000.00, '2024-11-27', 'closed', 'uploads/67442b1999105.png', '2024-11-25 07:02:16', '2024-11-25 07:47:11'),
(2, 1, 12000.00, '2024-11-27', 'closed', 'uploads/67442b2fc957a.png', '2024-11-25 07:04:10', '2024-11-25 07:45:51'),
(5, 2, 324.00, '2024-11-28', 'closed', 'uploads/67442b5599372.png', '2024-11-25 07:11:54', '2024-11-25 07:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `loan_messages`
--

CREATE TABLE `loan_messages` (
  `log_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_messages`
--

INSERT INTO `loan_messages` (`log_id`, `customer_id`, `officer_id`, `message`, `log_date`) VALUES
(1, 4, 1, 'Hy', '2024-11-25 07:26:12'),
(2, 4, 1, 'How Are you ', '2024-11-25 07:26:18'),
(3, 4, 1, 'EYs', '2024-11-25 07:28:20');

-- --------------------------------------------------------

--
-- Table structure for table `loan_types`
--

CREATE TABLE `loan_types` (
  `loan_type_id` int(11) NOT NULL,
  `loan_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `repayment_terms` int(11) NOT NULL,
  `max_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_types`
--

INSERT INTO `loan_types` (`loan_type_id`, `loan_name`, `description`, `interest_rate`, `repayment_terms`, `max_amount`, `created_at`, `updated_at`) VALUES
(1, 'Personal Loan', 'A loan given for personal purposes like education, medical expenses, etc.', 6.00, 12, 50000.00, '2024-11-25 05:48:52', '2024-11-25 02:02:39'),
(2, 'Home Loan', 'A loan given for purchasing a home.', 3.75, 240, 1000000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(3, 'Car Loan', 'A loan for purchasing a new or used car.', 4.25, 60, 200000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(4, 'Business Loan', 'A loan for starting or expanding a business.', 6.50, 120, 500000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(5, 'Education Loan', 'A loan for financing education expenses.', 4.50, 120, 100000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(6, 'Home Renovation Loan', 'A loan for home improvement or renovation.', 5.25, 36, 200000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(7, 'Debt Consolidation Loan', 'A loan to consolidate existing debts into one.', 7.00, 48, 150000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(8, 'Travel Loan', 'A loan for travel and vacation expenses.', 8.00, 12, 50000.00, '2024-11-25 05:48:52', '2024-11-25 02:02:21'),
(9, 'Wedding Loan', 'A loan for covering wedding expenses.', 6.00, 24, 100000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52'),
(10, 'Medical Loan', 'A loan for covering medical expenses.', 5.75, 36, 100000.00, '2024-11-25 05:48:52', '2024-11-25 05:48:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `age`, `full_name`, `bio`) VALUES
(4, 'user1', '$2y$10$ft8WRvMtWkYWeajNLu2VK.J4A3UIgYaK0cRYn5J0C8oE4q1fKB0HK', 'nasiryt.827@gmail.com', '3176526827', 23, 'NASIR ABBAS', 'IT ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loanofficers`
--
ALTER TABLE `loanofficers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `loan_type_id` (`loan_type_id`),
  ADD KEY `assigned_officer_id` (`assigned_officer_id`);

--
-- Indexes for table `loan_disbursements`
--
ALTER TABLE `loan_disbursements`
  ADD PRIMARY KEY (`disbursement_id`);

--
-- Indexes for table `loan_messages`
--
ALTER TABLE `loan_messages`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `officer_id` (`officer_id`);

--
-- Indexes for table `loan_types`
--
ALTER TABLE `loan_types`
  ADD PRIMARY KEY (`loan_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loanofficers`
--
ALTER TABLE `loanofficers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan_applications`
--
ALTER TABLE `loan_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_disbursements`
--
ALTER TABLE `loan_disbursements`
  MODIFY `disbursement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loan_messages`
--
ALTER TABLE `loan_messages`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_types`
--
ALTER TABLE `loan_types`
  MODIFY `loan_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD CONSTRAINT `loan_applications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `loan_applications_ibfk_2` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types` (`loan_type_id`),
  ADD CONSTRAINT `loan_applications_ibfk_3` FOREIGN KEY (`assigned_officer_id`) REFERENCES `loanofficers` (`id`);

--
-- Constraints for table `loan_messages`
--
ALTER TABLE `loan_messages`
  ADD CONSTRAINT `loan_messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `loan_messages_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `loanofficers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
