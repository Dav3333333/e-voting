-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 03:12 PM
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
-- Database: `evoting`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`id`, `user_id`, `post_id`, `poll_id`, `status`) VALUES
(1, 1, 2, 1, 0),
(2, 1, 3, 1, 1),
(3, 1, 4, 1, 0),
(4, 1, 5, 7, 0),
(5, 2, 2, 1, 0),
(6, 2, 3, 1, 1),
(7, 2, 4, 1, 1),
(9, 4, 2, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

CREATE TABLE `card` (
  `id` int(11) NOT NULL,
  `user_matricule` int(11) NOT NULL,
  `card_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poll`
--

CREATE TABLE `poll` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll`
--

INSERT INTO `poll` (`id`, `title`, `date_start`, `date_end`, `status`, `description`) VALUES
(1, 'Election du comite estudiantin a l\'ucbc', '2025-06-17 00:29:07', '2025-06-19 00:29:07', 'en attente', 'nous devons vote notre presedent pour l\'amour de Dieu'),
(4, 'Election du gouvermement americain 2026', '2025-06-09 00:29:06', '2025-06-13 00:29:06', 'en attente', 'tout le mode est appeller a voter'),
(5, 'un exemple de poll lors de test de la methode', '2025-06-25 12:20:40', '2025-06-26 12:20:40', 'inactif', 'La description lors du test de recupereation'),
(6, 'un exemple de poll lors de test de la methode', '2025-06-25 12:20:40', '2025-06-26 12:20:40', 'inactif', 'La description lors du test de recupereation'),
(7, 'Dirigeant du marche', '2025-07-18 10:59:20', '2025-07-22 10:59:20', 'passed', 'Le vote des personne qui vont diriger notre marchee'),
(8, 'jfndsjxncoasa', '2025-07-16 04:09:00', '2025-07-17 06:09:00', 'inactif', 'oeijoqjisc');

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `post_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `poll_id`, `post_name`) VALUES
(2, 1, 'Faculty president'),
(3, 1, 'Primotion guild'),
(4, 1, 'Vote des cp'),
(5, 7, 'kahala za mbele '),
(6, 7, 'kahala za ku balabala'),
(7, 7, 'le chef de kahala'),
(8, 8, 'Chef des vendeur des cacao'),
(9, 8, 'Chef des vendeur des cacao'),
(10, 8, 'CHef de vendeur des choses'),
(11, 8, 'un test aussi'),
(12, 1, 'Vote des vice cps'),
(13, 4, 'Vote du president'),
(14, 6, 'Un post pour l\'exemple du scrutin'),
(15, 4, 'Le ministre du gouvernement');

-- --------------------------------------------------------

--
-- Table structure for table `result`
--

CREATE TABLE `result` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `voices` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `matricule` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `rfid` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `matricule`, `email`, `rfid`, `is_admin`, `status`) VALUES
(1, 'david lusenge oswalde', '9023', 'davidlusenge@gmail.com', '12345676543', 1, 'actif'),
(2, 'danny lusenge ', '9123', 'dannylusenge@gmail.com', '2565432345654', 0, 'actif '),
(3, 'anifa vasikania', '1824', 'muyisavasikania@gmail.com', '839421761829741', 0, 'active'),
(4, 'daniel fraklin', '1822', 'daniellfrnakin', '839421761829741', 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `voice`
--

CREATE TABLE `voice` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voice`
--

INSERT INTO `voice` (`id`, `poll_id`, `post_id`, `user_id`, `candidate_id`, `timestamp`) VALUES
(5, 1, 1, 1, 1, '2025-06-17 22:22:18'),
(6, 1, 2, 1, 1, '2025-06-17 22:22:18'),
(7, 1, 3, 1, 1, '2025-06-18 22:56:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poll`
--
ALTER TABLE `poll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voice`
--
ALTER TABLE `voice`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `card`
--
ALTER TABLE `card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poll`
--
ALTER TABLE `poll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `voice`
--
ALTER TABLE `voice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
