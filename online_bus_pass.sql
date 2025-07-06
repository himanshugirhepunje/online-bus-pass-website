-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2025 at 09:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_bus_pass`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'msrtc2025', '2025', '2025-03-29 17:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0 COMMENT '0=unread, 1=read',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `otp_expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp`
--

INSERT INTO `otp` (`id`, `email`, `otp`, `otp_expiry`, `created_at`, `is_used`) VALUES
(20, 'himanshugirhepunje4817@gmail.com', '651551', '2025-07-06 09:42:46', '2025-03-30 06:56:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `source` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `valid_until` date NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `payment_status` enum('pending','success','failed') DEFAULT 'pending',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `source`, `destination`, `valid_until`, `cost`, `transaction_id`, `payment_status`, `date`, `rejection_reason`) VALUES
(17, 1, 'sivanibandh', 'sakoli', '2025-05-27', 400.00, 'i45jiotgio4gi', 'success', '2025-04-27 17:24:56', 'ewr43iu5tu4545'),
(18, 1, 'sivanibandh', 'sakoli', '2025-08-06', 400.00, 'fuegutguhyhyh', 'success', '2025-07-06 07:34:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `route_costs`
--

CREATE TABLE `route_costs` (
  `id` int(11) NOT NULL,
  `source` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_costs`
--

INSERT INTO `route_costs` (`id`, `source`, `destination`, `cost`) VALUES
(1, 'sakoli', 'dharmapuri', 200.00),
(2, 'sakoli', 'kumbhli', 200.00),
(3, 'sakoli', 'sawarband', 200.00),
(4, 'sakoli', 'sivanibandh', 400.00),
(5, 'sakoli', 'sangadi', 500.00),
(6, 'sakoli', 'dongargaon/sangadi', 500.00),
(7, 'sakoli', 'salebardi', 600.00),
(8, 'sakoli', 'dighori/mothi', 700.00),
(9, 'sakoli', 'sakhra/tawsi', 800.00),
(10, 'sakoli', 'chichal/barwaha', 800.00),
(11, 'sakoli', 'borgoan', 900.00),
(12, 'sakoli', 'antargoan', 900.00),
(13, 'sakoli', 'lakahndur', 1000.00),
(14, 'sakoli', 'ghusobatola', 500.00),
(15, 'sakoli', 'silezari', 600.00),
(16, 'sakoli', 'bondgoan/devi', 700.00),
(17, 'sakoli', 'nimgoan', 700.00),
(18, 'sakoli', 'arrttondi', 800.00),
(19, 'sakoli', 'arjuni/mor', 900.00),
(20, 'sakoli', 'sendurwafa', 200.00),
(21, 'sakoli', 'ukara/fata', 300.00),
(22, 'sakoli', 'saudad', 300.00),
(23, 'sakoli', 'bamhani/saudad', 300.00),
(24, 'sakoli', 'kohmara', 500.00),
(25, 'sakoli', 'arjuni/sadak', 600.00),
(26, 'sakoli', 'wadegoan', 300.00),
(27, 'sakoli', 'khajari', 520.00),
(28, 'sakoli', 'mundipar', 300.00),
(29, 'sakoli', 'pipalgoan', 350.00),
(30, 'sakoli', 'manegoan/road', 400.00),
(31, 'sakoli', 'lakhani', 700.00),
(32, 'sakoli', 'murmadi', 700.00),
(33, 'sakoli', 'gadegoan', 800.00),
(34, 'dharmapuri', 'sakoli', 200.00),
(35, 'kumbhli', 'sakoli', 200.00),
(36, 'sawarband', 'sakoli', 200.00),
(37, 'sivanibandh', 'sakoli', 400.00),
(38, 'sangadi', 'sakoli', 500.00),
(39, 'dongargaon/sangadi', 'sakoli', 500.00),
(40, 'salebardi', 'sakoli', 600.00),
(41, 'dighori/mothi', 'sakoli', 700.00),
(42, 'sakhra/tawsi', 'sakoli', 800.00),
(43, 'chichal/barwaha', 'sakoli', 800.00),
(44, 'borgoan', 'sakoli', 900.00),
(45, 'antargoan', 'sakoli', 900.00),
(46, 'lakahndur', 'sakoli', 1000.00),
(47, 'ghusobatola', 'sakoli', 500.00),
(48, 'silezari', 'sakoli', 600.00),
(49, 'bondgoan/devi', 'sakoli', 700.00),
(50, 'nimgoan', 'sakoli', 700.00),
(51, 'arrttondi', 'sakoli', 800.00),
(52, 'arjuni/mor', 'sakoli', 900.00),
(53, 'sendurwafa', 'sakoli', 200.00),
(54, 'ukara/fata', 'sakoli', 300.00),
(55, 'saudad', 'sakoli', 300.00),
(56, 'bamhani/saudad', 'sakoli', 300.00),
(57, 'kohmara', 'sakoli', 500.00),
(58, 'arjuni/sadak', 'sakoli', 600.00),
(59, 'wadegoan', 'sakoli', 300.00),
(60, 'khajari', 'sakoli', 520.00),
(61, 'mundipar', 'sakoli', 300.00),
(62, 'pipalgoan', 'sakoli', 350.00),
(63, 'manegoan/road', 'sakoli', 400.00),
(64, 'lakhani', 'sakoli', 700.00),
(65, 'murmadi', 'sakoli', 700.00),
(66, 'gadegoan', 'sakoli', 800.00),
(67, 'sakoli', 'City C', 400.00);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `collage_name` varchar(50) NOT NULL,
  `passport_photo` varchar(255) NOT NULL,
  `id_card` varchar(255) NOT NULL,
  `bonafide_certificate` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `name`, `phone`, `email`, `dob`, `age`, `gender`, `collage_name`, `passport_photo`, `id_card`, `bonafide_certificate`, `status`, `rejection_reason`, `created_at`) VALUES
(3, 1, 'Himanshu Girhepunje', '7721045077', 'himanshugirhepunje4817@gmail.com', '2004-02-03', 21, 'Male', 'Goverment Polytechnic Sakoli', 'uploads/1743331698_WhatsApp Image 2025-02-25 at 19.59.28_6f481f61.jpg', 'uploads/1743331698_WhatsApp Image 2025-02-25 at 19.59.28_6f481f61.jpg', 'uploads/1743331698_WhatsApp Image 2025-02-25 at 19.59.28_6f481f61.jpg', 'approved', NULL, '2025-03-30 10:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `created_at`) VALUES
(1, 'Himanshu Girhepunje', '7721045077', 'himanshugirhepunje4817@gmail.com', '$2y$10$UxgRnG1frOEwCEo8Gz8NjuJ1e5voKDM8ieiXzNl3X/v5dBC1Lzrk.', '2025-03-29 18:40:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contacts_email` (`email`),
  ADD KEY `idx_contacts_is_read` (`is_read`),
  ADD KEY `idx_contacts_created_at` (`created_at`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_ibfk_1` (`user_id`);

--
-- Indexes for table `route_costs`
--
ALTER TABLE `route_costs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `source` (`source`,`destination`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`,`email`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `route_costs`
--
ALTER TABLE `route_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `students` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
