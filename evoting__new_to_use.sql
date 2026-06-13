-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : sam. 13 juin 2026 à 16:22
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `evoting`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidate`
--

CREATE TABLE `candidate` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `candidate`
--

INSERT INTO `candidate` (`id`, `user_id`, `post_id`, `poll_id`, `status`) VALUES
(51, 1, 33, 53, 0),
(52, 4, 33, 53, 0),
(57, 1, 36, 55, 0),
(58, 2, 36, 55, 0),
(59, 34, 37, 55, 0),
(60, 35, 37, 55, 0);

-- --------------------------------------------------------

--
-- Structure de la table `card`
--

CREATE TABLE `card` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `card_code` varchar(255) NOT NULL,
  `used` tinyint(1) NOT NULL,
  `linkableToUser` tinyint(1) NOT NULL,
  `linkedUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `card`
--

INSERT INTO `card` (`id`, `poll_id`, `card_code`, `used`, `linkableToUser`, `linkedUser`) VALUES
(156, 53, '67207', 0, 0, 0),
(157, 53, '67737', 0, 0, 0),
(158, 53, '68267', 0, 0, 0),
(159, 53, '68797', 0, 0, 0),
(160, 53, '69327', 0, 0, 0),
(161, 53, '69857', 0, 0, 0),
(162, 53, '70387', 0, 0, 0),
(163, 53, '70917', 0, 0, 0),
(164, 53, '71447', 0, 0, 0),
(165, 53, '71977', 0, 0, 0),
(186, 55, '58851', 0, 1, 34),
(187, 55, '59401', 0, 1, 35),
(188, 55, '59951', 0, 1, 36),
(189, 55, '60501', 0, 1, 37),
(190, 55, '61051', 0, 1, 0),
(191, 55, '61601', 0, 1, 0),
(192, 55, '62151', 0, 1, 0),
(193, 55, '62701', 0, 1, 0),
(194, 55, '63251', 0, 1, 0),
(195, 55, '63801', 0, 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `enrolements`
--

CREATE TABLE `enrolements` (
  `id` int(11) NOT NULL,
  `id_poll` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `has_card` tinyint(1) NOT NULL,
  `card_code` varchar(255) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enrolements`
--

INSERT INTO `enrolements` (`id`, `id_poll`, `id_user`, `has_card`, `card_code`, `expired`, `date_time`) VALUES
(16, 55, 34, 1, '58851', 0, '2026-06-13 14:07:12'),
(17, 55, 35, 1, '59401', 0, '2026-06-13 14:07:12'),
(18, 55, 36, 1, '59951', 0, '2026-06-13 14:07:12'),
(19, 55, 37, 1, '60501', 0, '2026-06-13 14:21:52');

-- --------------------------------------------------------

--
-- Structure de la table `poll`
--

CREATE TABLE `poll` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `in_card_mode` tinyint(1) NOT NULL DEFAULT 0,
  `card_user_link_mode` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `poll`
--

INSERT INTO `poll` (`id`, `title`, `date_start`, `date_end`, `status`, `description`, `in_card_mode`, `card_user_link_mode`) VALUES
(53, 'Test', '2026-06-08 22:11:00', '2026-06-09 22:11:00', 'passed', 'akjaj', 1, 0),
(55, 'F.S.E.G', '2026-06-15 14:41:00', '2026-06-17 14:41:00', 'inactif', 'Le strutin de l\'economie', 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `post_name` varchar(255) NOT NULL,
  `voice_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `post`
--

INSERT INTO `post` (`id`, `poll_id`, `post_name`, `voice_count`) VALUES
(33, 53, 'Presidence', 0),
(36, 55, 'Presidence', 0),
(37, 55, 'prefacture', 0);

-- --------------------------------------------------------

--
-- Structure de la table `result`
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
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `matricule` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `rfid` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `has_image` tinyint(1) NOT NULL DEFAULT 0,
  `image_name` varchar(300) DEFAULT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `matricule`, `email`, `rfid`, `is_admin`, `has_image`, `image_name`, `status`) VALUES
(1, 'david lusenge oswalde', '9023', 'davidlusenge@gmail.com', '12345676543', 1, 0, '', 'actif'),
(2, 'danny lusenge ', '9123', 'dannylusenge@gmail.com', '2565432345654', 0, 0, '', 'actif '),
(4, 'daniel fraklin', '1822', 'daniellfrnakin', '839421761829741', 0, 0, '', 'active'),
(5, 'jeannet maneno', '1234kdc', 'jeannete@gmail.com', 'eijfoweuf', 0, 1, '5ee8385b_img_694a7f762c291.PNG', 'active'),
(34, 'MULENDA OMEONGA JOHN', '4225', 'mulendaomeongajhon@gmail.com', '2810134471', 0, 0, '', 'active'),
(35, 'MUGHOLE MAKWANO Monique', '6023', 'mugholemakwanomonique@gmail.com', '2787493671', 0, 0, '', 'active'),
(36, 'KAHINDO KASITORO Lucie', '16025', 'kahindokasitorolucie@gmail.com', '2795146631', 0, 0, '', 'active'),
(37, 'divine mwenge divine', '5023', 'divinemwenge@gmail.com', '2787693671', 0, 0, NULL, 'active');

-- --------------------------------------------------------

--
-- Structure de la table `voice`
--

CREATE TABLE `voice` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `card_code` varchar(20) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `voice`
--

INSERT INTO `voice` (`id`, `poll_id`, `post_id`, `user_id`, `card_code`, `candidate_id`, `timestamp`) VALUES
(87, 53, 33, 0, '67207', 51, '2026-06-07 22:13:06'),
(88, 53, 33, 0, '67737', 52, '2026-06-07 22:14:19'),
(89, 53, 33, 0, '68267', 52, '2026-06-07 22:32:20'),
(90, 53, 33, 0, '68797', 51, '2026-06-07 22:33:02'),
(91, 53, 33, 0, '69327', 51, '2026-06-07 22:34:43');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `enrolements`
--
ALTER TABLE `enrolements`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `poll`
--
ALTER TABLE `poll`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `voice`
--
ALTER TABLE `voice`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT pour la table `card`
--
ALTER TABLE `card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT pour la table `enrolements`
--
ALTER TABLE `enrolements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `poll`
--
ALTER TABLE `poll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `result`
--
ALTER TABLE `result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `voice`
--
ALTER TABLE `voice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
