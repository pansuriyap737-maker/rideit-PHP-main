-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 09:11 AM
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
-- Database: `triool_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `canceledtrip`
--

CREATE TABLE `canceledtrip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `car_number_plate` varchar(20) DEFAULT NULL,
  `pickup` varchar(255) DEFAULT NULL,
  `drop_location` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `canceled_by` enum('passenger','driver') DEFAULT 'passenger',
  `canceled_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `completedtrip`
--

CREATE TABLE `completedtrip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `car_number_plate` varchar(20) DEFAULT NULL,
  `pickup` varchar(255) DEFAULT NULL,
  `drop_location` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `completed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `first_name`, `last_name`, `email`, `contact_number`, `created_at`, `updated_at`) VALUES
(11, 'admin', '$2y$10$Lbuf/ROusyBHXMLxrC/gletVLNyyrbx.Ru0Fry.eEZVS4HydKTRgC', 'bbbb', 'lovercase', 'admin123@gmail.com', '7854123690', '2025-03-26 17:12:32', '2025-04-08 05:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `seats_booked` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `car_image` varchar(255) NOT NULL,
  `car_name` varchar(100) NOT NULL,
  `seating` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `number_plate` varchar(50) NOT NULL,
  `pickup_location` varchar(100) DEFAULT NULL,
  `drop_location` varchar(100) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `car_image`, `car_name`, `seating`, `city`, `amount`, `number_plate`, `pickup_location`, `drop_location`, `user_id`, `created_at`, `updated_at`, `date_time`) VALUES
(6, '1744051056_Screenshot (502).png', 'farari', 2, 'Surat', 100.00, 'GJ05xuwss', 'Navsari', 'Vapi', 2, '2025-04-07 15:07:36', '2025-04-08 11:47:16', '2025-04-16 01:00:00'),
(7, '1744056918_Screenshot (499).png', 'shivam', 4, 'Rajkot', 400.00, 'GJ05xuwsxp', 'Bharuch', 'Botad', 2, '2025-04-07 16:45:18', '2025-04-08 01:50:49', NULL),
(9, '1744058356_Screenshot (502).png', 'maruti', 3, 'Junagadh', 120.00, 'GJ05xuwsa', 'Morbi', 'Gondal', 2, '2025-04-07 17:09:16', '2025-04-08 02:09:16', NULL),
(10, '1744058525_Screenshot (498).png', 'Mastag', 3, 'Vadodara', 10000.00, 'GJ05xuwpa', 'Morbi', 'Porbandar', 2, '2025-04-07 17:12:05', '2025-04-08 02:12:20', NULL),
(11, '1744058759_Screenshot (501).png', 'shivam', 4, 'Bharuch', 12.00, 'GJ05xuwp', 'Mehsana', 'Navsari', 2, '2025-04-07 17:15:59', '2025-04-08 02:15:59', NULL),
(12, '1744058949_Screenshot (500).png', 'Food', 2, 'Mehsana', 40.00, 'GJ05xuwfgf', 'Navsari', 'Vapi', 2, '2025-04-07 20:49:09', '2025-04-08 02:20:02', NULL),
(13, '1744078929_Untitled design.png', 'shivam', 5, 'Surat', 40.00, 'GJ05xuwqqcf', 'Bharuch', 'Vapi', 2, '2025-04-07 22:52:09', '2025-04-08 11:45:24', '2025-04-26 22:57:00'),
(14, 'car_1744088574.png', 'shivam', 2, 'Bharuch', 900.00, 'GJ05xuwsfdsfdssffg', 'Veraval', 'Botad', 1, '2025-04-08 05:02:54', '2025-04-08 12:07:20', '2025-05-07 14:31:00'),
(16, '1744091941_drb logo.png', 'pavAN', 5, 'Surat', 550.00, 'GJ05xuwsss', 'Vapi', 'Veraval', 1, '2025-04-08 02:29:01', '2025-04-08 07:59:01', '2025-04-23 11:32:00'),
(18, '1744095836_Picture1.png', 'farari', 2, 'Rajkot', 1010.00, 'GJ05xuwszhjhj', 'Navsari', 'Vapi', 1, '2025-04-08 03:33:56', '2025-04-08 09:03:56', '2025-04-24 12:33:00'),
(19, '1744096222_XAMPP.png', 'fgfgfg', 1, 'Mehsana', 1000.00, 'GJ05xuwsssghgh', 'Navsari', 'Morbi', 3, '2025-04-08 03:40:22', '2025-04-08 09:10:22', '2025-04-18 12:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Parag Bhanjibhai Hadiya', 'hadiyaparag12@gmail.com', 'event ', 'fgfgdfg', '2025-04-07 14:30:36'),
(2, 'Parag Bhanjibhai Hadiya', 'hadiyaparag12@gmail.com', 'gfdgdgf', 'gfgfdgfdg', '2025-04-08 05:25:15');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `message`) VALUES
(16, 'dfdf', 'sdfdsf'),
(17, 'Parag Bhanjibhai Hadiya', 'this webiste is very good to usefully '),
(18, 'Alex', 'Great website—easy to navigate and very user-friendly!'),
(19, 'Jamie', 'Excellent content and design. I found everything I needed quickly!'),
(20, 'Taylor', 'Love the clean layout and fast load times. Keep it up!'),
(21, 'Jordan', 'Very impressive! The site is informative and visually appealing.'),
(22, 'Casey', 'Awesome experience overall. The structure and content are top-notch!'),
(23, 'Vanita', 'This Webiste good Gallery Event.\r\n'),
(24, 'PARAG BHANJIBHAI HADIYA', 'dfd'),
(25, 'parag', 'vcgfgfdg');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `driver_name` varchar(100) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `car_number_plate` varchar(50) DEFAULT NULL,
  `pickup` varchar(100) DEFAULT NULL,
  `drop_location` varchar(100) DEFAULT NULL,
  `payment_mode` varchar(20) DEFAULT NULL,
  `ride_datetime` datetime DEFAULT NULL,
  `ride_status` enum('pending','active','completed','canceled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `car_id`, `amount`, `razorpay_payment_id`, `payment_status`, `payment_date`, `driver_name`, `passenger_name`, `car_number_plate`, `pickup`, `drop_location`, `payment_mode`, `ride_datetime`) VALUES
(6, 3, 7, 400.00, 'pay_QGRpnfXYtWiCSF', 'Success', '2025-04-08 05:28:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 1, 6, 100.00, 'pay_QGSMwgKRnfoiYU', 'Success', '2025-04-08 06:00:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `contact`, `password`, `role`, `created_at`) VALUES
(1, 'ParagBhai', 'hp12@gmail.com', NULL, '$2y$10$nDpAoRM.cYWqiRaLznUXb.Li7uAZjbQ3y2nGNcSL9bECk0nuUS87a', 'user', '2025-04-07 14:32:41'),
(2, 'vani', 'hadiyaparag12@gmail.com', '8000874240', '$2y$10$DX36/H3cfvPyf9BD7RrpS.dgD6CmuW4iUjqG.l731ANDPzgUnBFXq', 'user', '2025-04-07 18:23:32'),
(3, 'jay', 'hadiyaparag@gmail.com', '8000874240', '$2y$10$B.Je0PNybKsSLCjXxV78wuuV8qbDz9KssvLwh1IPad1EzABJCeF9C', 'user', '2025-04-08 05:25:59');

-- --------------------------------------------------------

--
-- Table structure for table `pessanger`
--

CREATE TABLE `pessanger` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deactivatedpesenger`
--

CREATE TABLE `deactivatedpesenger` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deactivated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deactivateddrivers`
--

CREATE TABLE `deactivateddrivers` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deactivated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `number_plate` (`number_plate`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pessanger`
--
ALTER TABLE `pessanger`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `deactivatedpesenger`
--
ALTER TABLE `deactivatedpesenger`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `deactivateddrivers`
--
ALTER TABLE `deactivateddrivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

-- --------------------------------------------------------

--
-- Table structure for table `canceledtrip`
--

CREATE TABLE `canceledtrip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `car_number_plate` varchar(20) DEFAULT NULL,
  `pickup` varchar(255) DEFAULT NULL,
  `drop_location` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `canceled_by` enum('passenger','driver') DEFAULT 'passenger',
  `canceled_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `completedtrip`
--

CREATE TABLE `completedtrip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `car_number_plate` varchar(20) DEFAULT NULL,
  `pickup` varchar(255) DEFAULT NULL,
  `drop_location` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `completed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pessanger`
--
ALTER TABLE `pessanger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deactivateddrivers`
--
ALTER TABLE `deactivateddrivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
