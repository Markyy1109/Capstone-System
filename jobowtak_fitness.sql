-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2026 at 02:49 PM
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
-- Database: `jobowtak_fitness`
--

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `category` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `category`, `description`, `instructions`, `created_at`, `updated_at`) VALUES
(1, 'Push-ups', 'Strength', 'Upper body exercise', NULL, NULL, NULL),
(2, 'Squats', 'Strength', 'Lower body exercise', NULL, NULL, NULL),
(3, 'Running', 'Cardio', 'Cardiovascular exercise', NULL, NULL, NULL),
(4, 'Plank', 'Core', 'Core stability exercise', NULL, NULL, NULL),
(5, 'Lunges', 'Strength', 'Leg strengthening exercise', 'cbfghgsgf', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `member_code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `member_code`, `name`, `email`, `phone`, `address`, `birthdate`, `gender`, `status`, `created_at`, `updated_at`) VALUES
(2, 3, 'JBG-12AAE927EA', 'marky lopez', 'marklopez09@gmail.com', NULL, NULL, NULL, NULL, 'Active', NULL, NULL),
(3, NULL, 'MARKY', 'Mark John Lopez', 'markjohnlopez74@gmail.com', '09944936674', 'Peñafrancia', '2006-05-09', 'Male', 'Active', NULL, NULL),
(4, NULL, 'mrky', 'Mark John Lopez', 'markjohnlopez74@gmail.com', '09944936674', 'Peñafrancia', '2006-05-09', 'Male', 'Active', NULL, NULL),
(5, 4, 'JBG-B9B4FB8665', 'trainer', 'hrgdbfhhhrt12@gmail.com', NULL, NULL, NULL, NULL, 'Active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `membership_type` enum('Monthly','Quarterly','Semi-Annual','Annual') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Expired','Cancelled') DEFAULT 'Active',
  `fee` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `member_id`, `membership_type`, `start_date`, `end_date`, `status`, `fee`, `created_at`, `updated_at`) VALUES
(2, 2, 'Monthly', '2026-09-11', '2026-10-11', 'Expired', 700.00, NULL, NULL),
(3, 2, 'Monthly', '2026-11-10', '2026-12-11', 'Active', 700.00, NULL, NULL),
(4, 3, 'Monthly', '2026-11-10', '2026-12-11', 'Cancelled', 700.00, NULL, NULL),
(5, 4, 'Monthly', '2026-11-10', '2026-12-11', 'Active', 700.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'Paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `membership_id`, `amount`, `payment_date`, `payment_method`, `reference_number`, `payment_status`, `notes`, `created_at`) VALUES
(1, 1, 1000.00, '2026-11-09', 'Credit/Debit Card', '12631654728', 'Pending', NULL, '2026-11-09 13:13:06'),
(2, 3, 700.00, '2026-11-10', 'Cash', '12631654728', 'Paid', NULL, '2026-11-10 14:00:57'),
(3, 4, 700.00, '2026-11-10', 'GCash', '12631654728', 'Paid', 'cvdxfdsfsbghgd', '2026-11-10 15:20:27'),
(4, 5, 1400.00, '2026-11-10', 'Bank Transfer', '12631654728', 'Paid', 'cxv dfbdfvv', '2026-11-10 16:06:54');

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `measurement_date` date NOT NULL,
  `weight_kg` decimal(6,2) NOT NULL,
  `height_cm` decimal(6,2) NOT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `member_id`, `measurement_date`, `weight_kg`, `height_cm`, `bmi`, `notes`, `created_at`, `updated_at`) VALUES
(2, 2, '2026-11-10', 55.00, 170.00, 19.03, NULL, NULL, NULL),
(3, 3, '2026-11-10', 55.00, 164.98, 20.21, 'cvxfdgdfg', NULL, NULL),
(4, 4, '2026-11-10', 65.00, 165.00, 23.88, 'dfgrgdbf', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','trainer','member') DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(2, 'mark lopez', 'markjohnlopez74@gmail.com', '$2y$10$tanEZR7KBevapG4Ih5Fsk.YdbJ9V24R5pBSjzN7f/gCce/lwMGJZS', 'member', NULL, NULL),
(3, 'marky lopez', 'marklopez09@gmail.com', '$2y$10$3bI4HbDBLj3D7SmUmH.5/.v8NVU6Kum2nXi6yEE7eOorTK.qnRjBa', 'member', NULL, NULL),
(4, 'trainer', 'hrgdbfhhhrt12@gmail.com', '$2y$10$IBxHkqLYqbWH1Rbdc.D5kupENpa79417rdMjJu93a5RSFDy5K/q1W', 'member', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workouts`
--

CREATE TABLE `workouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `exercise_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(120) NOT NULL,
  `goal` varchar(255) DEFAULT NULL,
  `exercise_name` varchar(120) NOT NULL,
  `sets` int(10) UNSIGNED DEFAULT NULL,
  `repetitions` int(10) UNSIGNED DEFAULT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `schedule` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workouts`
--

INSERT INTO `workouts` (`id`, `member_id`, `exercise_id`, `title`, `goal`, `exercise_name`, `sets`, `repetitions`, `duration_minutes`, `schedule`, `notes`, `created_at`, `updated_at`) VALUES
(2, 2, 4, 'push day', 'vbfgjt', 'Plank', 3, 15, 30, 'M,W,F', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_code` (`member_code`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workouts`
--
ALTER TABLE `workouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `workouts`
--
ALTER TABLE `workouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workouts`
--
ALTER TABLE `workouts`
  ADD CONSTRAINT `workouts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workouts_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
