-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 08:46 PM
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
-- Database: `healthcare`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `profile` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `profile`) VALUES
(9, 'Admin@tausif', '$2y$10$zomXrXdywHRclzQVSq9QpedpAuq9RZOfS2T5s5Fz2/lr1poIYqk/2', '67f536efc3a35.png'),
(10, 'Admin@gagan', '$2y$10$prbjM0.14apaJK4OfTTsvupVuwo7tGR/26wQWXNGOaZOa5L2hqkEe', '67f53751c6160.png'),
(12, 'Admin@aniket', '$2y$10$5R.E/nspaAQtptdKwhegHuN28hz8dJCtPUSL3v39WRHNKc7VZZqOq', '67f8bcc9c471b.png');

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `id` int(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `phone` int(10) NOT NULL,
  `appointment_date` varchar(100) NOT NULL,
  `symptoms` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `date_booked` varchar(100) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`id`, `first_name`, `surname`, `gender`, `phone`, `appointment_date`, `symptoms`, `status`, `date_booked`, `doctor_id`) VALUES
(3, 'MOHAMMAD', 'TAUSIF', 'Male', 2147483647, '2025-05-01', 'testing', 'Checked', '2025-04-10', NULL),
(5, 'MOHAMMAD', 'TAUSIF', 'Male', 2147483647, '2025-04-11', 'test', 'Checked', '2025-04-10', NULL),
(6, 'MOHAMMAD', 'TAUSIF', 'Male', 2147483647, '2025-04-11', 'test', 'Checked', '2025-04-10 13:06:19', NULL),
(13, 'MOHAMMAD', 'TAUSIF', 'Male', 2147483647, '2025-04-06', 'eee test', 'Checked', '2025-04-10', NULL),
(15, 'faruk', 'shrma', 'Male', 2147483647, '2025-04-19', 'yes', 'Checked', '2025-04-10', NULL),
(24, 'www', 'wwww', 'Male', 2147483647, '2222-02-22', '222', 'Checked', '2025-04-10', NULL),
(25, 'test', 'ew', 'Female', 2147483647, '1111-02-23', '22222', 'Pending', '2025-04-10', NULL),
(26, 'MOHAMMAD22', 'TAUSIF', 'Male', 790823469, '2111-11-11', '222', 'Pending', '2025-04-10', NULL),
(32, 'theycallmeshinchann', 'user', 'Male', 2147483647, '2025-04-19', 'headache', 'Checked', '2025-04-10', 14),
(34, 'theycallmeshinchann', 'TAUSIF', 'Male', 2147483647, '2012-02-22', 'test', 'Checked', '2025-04-11', 14),
(35, 'MOHAMMAD', 'TAUSIF', 'Male', 2147483647, '1000-11-22', 'www', 'Checked', '2025-04-11', 14),
(36, 'MOHAMMAD', 'TAUSIF', 'Male', 2147483647, '2025-04-10', 'www', 'Checked', '2025-04-11', 14),
(39, 'theycallmeshinchann', 'test124', 'Male', 2147483647, '2025-04-27', 'Headache !!', 'Pending', '2025-04-19', 14),
(40, 'theycallmeshinchann', 'test124', 'Male', 2147483647, '2025-04-27', 'Headache !!', 'Pending', '2025-04-19', 14),
(41, 'theycallmeshinchann', 'test156', 'Male', 2147483647, '2025-04-26', 'Headache !1', 'Checked', '2025-04-19', 16),
(42, 'theycallmeshinchann', 'TAUSIF2667', 'Male', 909823469, '2025-04-11', 'www', 'Pending', '2025-04-19', 17),
(43, 'theycallmeshinchann', 'TAUSIF78666', 'Male', 2147483647, '2025-04-27', 'qqq', 'Checked', '2025-04-19', 14);

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `id` int(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `phone` int(10) NOT NULL,
  `country` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `salary` varchar(100) NOT NULL,
  `date_reg` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `profile` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`id`, `first_name`, `surname`, `username`, `email`, `gender`, `phone`, `country`, `password`, `salary`, `date_reg`, `status`, `profile`) VALUES
(14, 'Priya', 'Sharma', 'Priyu_sharma', 'priya14@gmail.com', 'female', 2147483647, 'uk', '$2y$10$cRp277dP6a9GHq78A4VPFeg9UX04ivODCyGNBFsqTT9yRjLSV2XVW', '10000', '2025-04-08 20:56:57', 'Approved', 'profile_67f767011c30b.jpg'),
(16, 'Nita', 'kaushik', 'nita_12', 'nitakaushik@gmail.com', 'male', 2147483647, 'canada', 'nita123', '', '2025-04-08 21:00:55', 'pending', ''),
(17, 'MOHAMMAD', 'TAUSIF', 'priyu_sharmaQ11', 'mohdtausif11641@gmail.com', 'female', 2147483647, 'india', '$2y$10$pjSMTj4Ylp0vbZO0AUJJDurPZC/9OCI86FUE19ySZbLF6JchIEVj6', '', '2025-04-09 23:29:08', 'pending', ''),
(18, 'MOHAMMAD', 'TAUSIF', 'kytr_3', 'mohdtausif5641@gmail.com', 'male', 2147483647, 'india', '$2y$10$YhKl1kYq0GJq7GHN64s/fO7c7eMpqLWz3jHji4pFxg.ZXTiCB2ftq', '', '2025-04-11 12:26:03', 'Approved', '');

-- --------------------------------------------------------

--
-- Table structure for table `form_settings`
--

CREATE TABLE `form_settings` (
  `id` int(11) NOT NULL,
  `form_name` varchar(50) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_settings`
--

INSERT INTO `form_settings` (`id`, `form_name`, `status`) VALUES
(1, 'doctor_application', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `id` int(100) NOT NULL,
  `doctor` varchar(100) NOT NULL,
  `patient` varchar(100) NOT NULL,
  `date_discharge` varchar(100) NOT NULL,
  `amount_paid` int(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `date_check` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`id`, `doctor`, `patient`, `date_discharge`, `amount_paid`, `description`, `date_check`) VALUES
(16, 'theycallmeshinchann TAUSIF', 'theycallmeshinchann TAUSIF', '2025-04-11 05:41:35', 500, 'take a Rest', '2025-04-11 05:41:35'),
(20, 'MOHAMMAD TAUSIF', 'MOHAMMAD TAUSIF', '2025-04-19 18:12:39', 7895, 'Paracetamol, Headache Problem', '2025-04-19 18:12:39'),
(21, 'theycallmeshinchann TAUSIF112', 'theycallmeshinchann TAUSIF112', '2025-04-19 18:15:34', 4500, 'FULL CHECK UP & TEST', '2025-04-19 18:15:34'),
(22, 'theycallmeshinchann TAUSIF112', 'theycallmeshinchann TAUSIF112', '2025-04-19 18:52:34', 450, 'yep', '2025-04-19 18:52:34'),
(23, 'theycallmeshinchann user', 'theycallmeshinchann user', '2025-04-19 18:54:54', 789, 'test', '2025-04-19 18:54:54'),
(24, '14', '', '', 0, '', ''),
(25, 'Nita kaushik', '', '', 0, '', ''),
(26, 'theycallmeshinchann test156', 'theycallmeshinchann test156', '2025-04-19 19:08:35', 7890, 'er test', '2025-04-19 19:08:35'),
(27, 'theycallmeshinchann TAUSIF78666', 'theycallmeshinchann TAUSIF78666', '2025-04-19 19:13:25', 78922222, 'uid', '2025-04-19 19:13:25');

-- --------------------------------------------------------

--
-- Table structure for table `news_notice`
--

CREATE TABLE `news_notice` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_notice`
--

INSERT INTO `news_notice` (`id`, `type`, `title`, `content`, `date_created`) VALUES
(17, 'news', 'Yess !!!', 'testing', '2025-04-09 21:18:27'),
(18, 'news', 'testing', '', '2025-04-09 21:22:34'),
(19, 'news', 'final testing', 'Test', '2025-04-09 21:24:52'),
(20, 'news', 'final testing', 'Test', '2025-04-09 21:26:06'),
(21, 'news', 'testing Date', 'Date test', '2025-04-09 21:26:23'),
(22, 'news', 'again test 10 apr', 'tyest', '2025-04-10 00:57:53'),
(23, 'announcement', 'again test 10 apr show', 'test', '2025-04-10 00:58:35'),
(29, 'announcement', 'closed', 'ff', '2025-04-11 12:27:38');

-- --------------------------------------------------------

--
-- Table structure for table `pateint`
--

CREATE TABLE `pateint` (
  `id` int(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` int(10) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `date_reg` varchar(100) NOT NULL,
  `profile` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pateint`
--

INSERT INTO `pateint` (`id`, `first_name`, `surname`, `username`, `email`, `phone`, `gender`, `country`, `password`, `date_reg`, `profile`) VALUES
(5, 'MOHAMMAD', 'TAUSIF', 'theycallmeshinchann', 'mohdtausif641@gmail.com', 2147483647, 'male', 'india', '$2y$10$Ok8cv5kq7lOF1fNP8cnzGOvZHimC2WAZlxzytXZt9vJsIxOzw/VCS', '2025-04-09 18:55:18', 'profile_68222316de244.png'),
(6, 'Gagandeep', 'chandrakar', 'gagan_chandrakar', 'gagan2002@gmail.com', 2147483647, 'male', 'india', '$2y$10$0ut7gq7wwLoTXBUW40cCweFfT5yI6EdIV854aqQyVjElJk8M171I2', '2025-04-10 20:03:06', 'default.jpg'),
(7, 'aniket', 'gujar', 'aniket_gujarr', 'aniket@gmail.com', 2147483647, 'male', 'india', '$2y$10$ybJb7dcK9.fRJJjmsuI13.hccj/46d3j1cwg.OAYPMaY.EfPXw4cy', '2025-04-11 08:45:06', 'default.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `id` int(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `date_send` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`id`, `title`, `message`, `username`, `date_send`) VALUES
(10, 'HEADACHE UPDATE', 'DOCTOR MY HEADACHE IS ALMOST CURED\r\nTHANKS !!!!!', 'theycallmeshinchanN', '2025-04-19');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`name`, `value`) VALUES
('doctor_application', 'open'),
('doctor_form_status', 'closed');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_settings`
--
ALTER TABLE `form_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_notice`
--
ALTER TABLE `news_notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pateint`
--
ALTER TABLE `pateint`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `form_settings`
--
ALTER TABLE `form_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `news_notice`
--
ALTER TABLE `news_notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `pateint`
--
ALTER TABLE `pateint`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
