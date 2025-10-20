-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 20 oct. 2025 à 04:31
-- Version du serveur : 5.7.40
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `jetreserve`
--

-- --------------------------------------------------------

--
-- Structure de la table `avions`
--

DROP TABLE IF EXISTS `avions`;
CREATE TABLE IF NOT EXISTS `avions` (
  `id_avion` int(11) NOT NULL AUTO_INCREMENT,
  `modele` varchar(100) NOT NULL,
  `id_compagnie` int(11) NOT NULL,
  `capacite_total` int(11) NOT NULL,
  `configuration_sieges` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avion`),
  KEY `id_compagnie` (`id_compagnie`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `avions`
--

INSERT INTO `avions` (`id_avion`, `modele`, `id_compagnie`, `capacite_total`, `configuration_sieges`, `created_at`) VALUES
(1, 'Airbus A320', 1, 180, '{\"affaires\": 24, \"premiere\": 6, \"economique\": 150}', '2025-10-11 15:00:00'),
(2, 'Boeing 777', 2, 396, '{\"affaires\": 56, \"premiere\": 16, \"economique\": 324}', '2025-10-11 15:00:00'),
(3, 'Airbus A321', 3, 220, '{\"affaires\": 24, \"premiere\": 6, \"economique\": 190}', '2025-10-11 15:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `compagnies`
--

DROP TABLE IF EXISTS `compagnies`;
CREATE TABLE IF NOT EXISTS `compagnies` (
  `id_compagnie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_compagnie` varchar(100) NOT NULL,
  `code_compagnie` varchar(10) NOT NULL,
  `pays` varchar(100) NOT NULL,
  PRIMARY KEY (`id_compagnie`),
  UNIQUE KEY `code_compagnie` (`code_compagnie`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `compagnies`
--

INSERT INTO `compagnies` (`id_compagnie`, `nom_compagnie`, `code_compagnie`, `pays`) VALUES
(1, 'Air France', 'AF', 'France'),
(2, 'Emirates', 'EK', 'Emirate'),
(3, 'Turkish Airlines', 'TK', 'Turquie');

-- --------------------------------------------------------

--
-- Structure de la table `emails`
--

DROP TABLE IF EXISTS `emails`;
CREATE TABLE IF NOT EXISTS `emails` (
  `id_email` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `sujet` varchar(150) NOT NULL,
  `contenu` text,
  `type` enum('confirmation','recu','ticket') DEFAULT 'confirmation',
  `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('envoyé','échoué') DEFAULT 'envoyé',
  PRIMARY KEY (`id_email`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `emails`
--

INSERT INTO `emails` (`id_email`, `id_user`, `sujet`, `contenu`, `type`, `date_envoi`, `statut`) VALUES
(1, 1, 'Confirmation de réservation', 'Votre réservation pour le vol Paris-Abidjan est confirmée.', 'confirmation', '2025-10-07 22:26:46', 'envoyé'),
(2, 1, 'Reçu de paiement', 'Votre paiement de 450.00€ a été reçu.', 'recu', '2025-10-07 22:26:46', 'envoyé');

-- --------------------------------------------------------

--
-- Structure de la table `options_bagage`
--

DROP TABLE IF EXISTS `options_bagage`;
CREATE TABLE IF NOT EXISTS `options_bagage` (
  `id_option` int(11) NOT NULL AUTO_INCREMENT,
  `nom_option` varchar(100) NOT NULL,
  `description` text,
  `poids_max` decimal(5,2) DEFAULT NULL,
  `prix_supplement` decimal(8,2) NOT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_option`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `options_bagage`
--

INSERT INTO `options_bagage` (`id_option`, `nom_option`, `description`, `poids_max`, `prix_supplement`, `statut`) VALUES
(1, 'Bagage à main', 'Bagage cabine standard (8kg)', '8.00', '0.00', 'actif'),
(2, 'Bagage en soute 20kg', 'Bagage en soute jusqu\'à 20kg', '20.00', '25.00', 'actif'),
(3, 'Bagage en soute 30kg', 'Bagage en soute jusqu\'à 30kg', '30.00', '45.00', 'actif'),
(4, 'Excédent bagage 5kg', 'Supplément pour 5kg supplémentaires', '5.00', '15.00', 'actif'),
(5, 'Bagage sport', 'Équipement sportif (ski, golf, etc.)', '30.00', '35.00', 'actif'),
(6, 'Bagage fragile', 'Articles fragiles - traitement spécial', '20.00', '20.00', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id_paiement` int(11) NOT NULL AUTO_INCREMENT,
  `id_reservation` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `mode_paiement` enum('carte','mobile money','paypal') NOT NULL,
  `statut` enum('réussi','en attente','échoué') DEFAULT 'en attente',
  `date_paiement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_paiement`),
  KEY `id_reservation` (`id_reservation`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id_paiement`, `id_reservation`, `montant`, `mode_paiement`, `statut`, `date_paiement`) VALUES
(1, 1, '450.00', 'carte', 'réussi', '2025-10-07 22:26:46'),
(2, 2, '700.00', 'paypal', 'en attente', '2025-10-07 22:26:46');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_vol` int(11) NOT NULL,
  `statut` enum('confirmé','en attente','annulé') DEFAULT 'en attente',
  `date_reservation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nombre_passagers` int(11) DEFAULT '1',
  `prix_total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_reservation`),
  KEY `id_user` (`id_user`),
  KEY `id_vol` (`id_vol`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id_reservation`, `id_user`, `id_vol`, `statut`, `date_reservation`, `nombre_passagers`, `prix_total`) VALUES
(1, 1, 1, 'confirmé', '2025-10-07 22:26:46', 1, '450.00'),
(2, 2, 2, 'en attente', '2025-10-07 22:26:46', 2, '1400.00'),
(3, 1, 1, 'en attente', '2025-10-19 18:28:24', 2, '900.00'),
(4, 1, 1, 'en attente', '2025-10-19 19:29:55', 1, '450.00'),
(5, 1, 1, 'en attente', '2025-10-20 02:57:42', 1, '650.00'),
(6, 1, 1, 'en attente', '2025-10-20 03:01:04', 1, '550.00'),
(7, 1, 1, 'en attente', '2025-10-20 03:15:12', 1, '650.00');

-- --------------------------------------------------------

--
-- Structure de la table `reservation_bagages`
--

DROP TABLE IF EXISTS `reservation_bagages`;
CREATE TABLE IF NOT EXISTS `reservation_bagages` (
  `id_reservation_bagage` int(11) NOT NULL AUTO_INCREMENT,
  `id_reservation` int(11) DEFAULT NULL,
  `id_option` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT '1',
  `prix_applique` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id_reservation_bagage`),
  KEY `id_reservation` (`id_reservation`),
  KEY `id_option` (`id_option`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `reservation_sieges`
--

DROP TABLE IF EXISTS `reservation_sieges`;
CREATE TABLE IF NOT EXISTS `reservation_sieges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_reservation` int(11) NOT NULL,
  `id_siege` int(11) NOT NULL,
  `prix_paye` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_reservation` (`id_reservation`),
  KEY `id_siege` (`id_siege`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `reservation_sieges`
--

INSERT INTO `reservation_sieges` (`id`, `id_reservation`, `id_siege`, `prix_paye`, `created_at`) VALUES
(1, 1, 45, '450.00', '2025-10-07 22:26:46'),
(2, 2, 78, '700.00', '2025-10-07 22:26:46'),
(3, 2, 79, '700.00', '2025-10-07 22:26:46'),
(4, 3, 25, '450.00', '2025-10-19 18:28:24'),
(5, 3, 26, '450.00', '2025-10-19 18:28:24'),
(6, 4, 115, '450.00', '2025-10-19 19:29:55'),
(7, 5, 1, '650.00', '2025-10-20 02:57:42'),
(8, 6, 8, '550.00', '2025-10-20 03:01:04'),
(9, 7, 2, '650.00', '2025-10-20 03:15:12');

-- --------------------------------------------------------

--
-- Structure de la table `sieges_avion`
--

DROP TABLE IF EXISTS `sieges_avion`;
CREATE TABLE IF NOT EXISTS `sieges_avion` (
  `id_siege` int(11) NOT NULL AUTO_INCREMENT,
  `id_avion` int(11) NOT NULL,
  `rang` int(11) NOT NULL,
  `position` varchar(2) NOT NULL,
  `cote` enum('gauche','droit') NOT NULL,
  `classe` enum('économique','affaires','première') DEFAULT 'économique',
  `supplement_prix` decimal(10,2) DEFAULT '0.00',
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_siege`),
  KEY `id_avion` (`id_avion`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `sieges_avion`
--

INSERT INTO `sieges_avion` (`id_siege`, `id_avion`, `rang`, `position`, `cote`, `classe`, `supplement_prix`, `statut`) VALUES
(1, 1, 1, 'A', 'gauche', 'première', '200.00', 'actif'),
(2, 1, 1, 'B', 'gauche', 'première', '200.00', 'actif'),
(3, 1, 1, 'C', 'gauche', 'première', '200.00', 'actif'),
(4, 1, 1, 'D', 'droit', 'première', '200.00', 'actif'),
(5, 1, 1, 'E', 'droit', 'première', '200.00', 'actif'),
(6, 1, 1, 'F', 'droit', 'première', '200.00', 'actif'),
(7, 1, 2, 'A', 'gauche', 'affaires', '100.00', 'actif'),
(8, 1, 2, 'B', 'gauche', 'affaires', '100.00', 'actif'),
(9, 1, 2, 'C', 'gauche', 'affaires', '100.00', 'actif'),
(10, 1, 2, 'D', 'droit', 'affaires', '100.00', 'actif'),
(11, 1, 2, 'E', 'droit', 'affaires', '100.00', 'actif'),
(12, 1, 2, 'F', 'droit', 'affaires', '100.00', 'actif'),
(13, 1, 3, 'A', 'gauche', 'affaires', '100.00', 'actif'),
(14, 1, 3, 'B', 'gauche', 'affaires', '100.00', 'actif'),
(15, 1, 3, 'C', 'gauche', 'affaires', '100.00', 'actif'),
(16, 1, 3, 'D', 'droit', 'affaires', '100.00', 'actif'),
(17, 1, 3, 'E', 'droit', 'affaires', '100.00', 'actif'),
(18, 1, 3, 'F', 'droit', 'affaires', '100.00', 'actif'),
(19, 1, 4, 'A', 'gauche', 'affaires', '100.00', 'actif'),
(20, 1, 4, 'B', 'gauche', 'affaires', '100.00', 'actif'),
(21, 1, 4, 'C', 'gauche', 'affaires', '100.00', 'actif'),
(22, 1, 4, 'D', 'droit', 'affaires', '100.00', 'actif'),
(23, 1, 4, 'E', 'droit', 'affaires', '100.00', 'actif'),
(24, 1, 4, 'F', 'droit', 'affaires', '100.00', 'actif'),
(25, 1, 5, 'A', 'gauche', 'économique', '0.00', 'actif'),
(26, 1, 5, 'B', 'gauche', 'économique', '0.00', 'actif'),
(27, 1, 5, 'C', 'gauche', 'économique', '0.00', 'actif'),
(28, 1, 5, 'D', 'droit', 'économique', '0.00', 'actif'),
(29, 1, 5, 'E', 'droit', 'économique', '0.00', 'actif'),
(30, 1, 5, 'F', 'droit', 'économique', '0.00', 'actif'),
(31, 1, 6, 'A', 'gauche', 'économique', '0.00', 'actif'),
(32, 1, 6, 'B', 'gauche', 'économique', '0.00', 'actif'),
(33, 1, 6, 'C', 'gauche', 'économique', '0.00', 'actif'),
(34, 1, 6, 'D', 'droit', 'économique', '0.00', 'actif'),
(35, 1, 6, 'E', 'droit', 'économique', '0.00', 'actif'),
(36, 1, 6, 'F', 'droit', 'économique', '0.00', 'actif'),
(37, 1, 7, 'A', 'gauche', 'économique', '0.00', 'actif'),
(38, 1, 7, 'B', 'gauche', 'économique', '0.00', 'actif'),
(39, 1, 7, 'C', 'gauche', 'économique', '0.00', 'actif'),
(40, 1, 7, 'D', 'droit', 'économique', '0.00', 'actif'),
(41, 1, 7, 'E', 'droit', 'économique', '0.00', 'actif'),
(42, 1, 7, 'F', 'droit', 'économique', '0.00', 'actif'),
(43, 1, 8, 'A', 'gauche', 'économique', '0.00', 'actif'),
(44, 1, 8, 'B', 'gauche', 'économique', '0.00', 'actif'),
(45, 1, 8, 'C', 'gauche', 'économique', '0.00', 'actif'),
(46, 1, 8, 'D', 'droit', 'économique', '0.00', 'actif'),
(47, 1, 8, 'E', 'droit', 'économique', '0.00', 'actif'),
(48, 1, 8, 'F', 'droit', 'économique', '0.00', 'actif'),
(49, 1, 9, 'A', 'gauche', 'économique', '0.00', 'actif'),
(50, 1, 9, 'B', 'gauche', 'économique', '0.00', 'actif'),
(51, 1, 9, 'C', 'gauche', 'économique', '0.00', 'actif'),
(52, 1, 9, 'D', 'droit', 'économique', '0.00', 'actif'),
(53, 1, 9, 'E', 'droit', 'économique', '0.00', 'actif'),
(54, 1, 9, 'F', 'droit', 'économique', '0.00', 'actif'),
(55, 1, 10, 'A', 'gauche', 'économique', '0.00', 'actif'),
(56, 1, 10, 'B', 'gauche', 'économique', '0.00', 'actif'),
(57, 1, 10, 'C', 'gauche', 'économique', '0.00', 'actif'),
(58, 1, 10, 'D', 'droit', 'économique', '0.00', 'actif'),
(59, 1, 10, 'E', 'droit', 'économique', '0.00', 'actif'),
(60, 1, 10, 'F', 'droit', 'économique', '0.00', 'actif'),
(61, 1, 11, 'A', 'gauche', 'économique', '0.00', 'actif'),
(62, 1, 11, 'B', 'gauche', 'économique', '0.00', 'actif'),
(63, 1, 11, 'C', 'gauche', 'économique', '0.00', 'actif'),
(64, 1, 11, 'D', 'droit', 'économique', '0.00', 'actif'),
(65, 1, 11, 'E', 'droit', 'économique', '0.00', 'actif'),
(66, 1, 11, 'F', 'droit', 'économique', '0.00', 'actif'),
(67, 1, 12, 'A', 'gauche', 'économique', '0.00', 'actif'),
(68, 1, 12, 'B', 'gauche', 'économique', '0.00', 'actif'),
(69, 1, 12, 'C', 'gauche', 'économique', '0.00', 'actif'),
(70, 1, 12, 'D', 'droit', 'économique', '0.00', 'actif'),
(71, 1, 12, 'E', 'droit', 'économique', '0.00', 'actif'),
(72, 1, 12, 'F', 'droit', 'économique', '0.00', 'actif'),
(73, 1, 13, 'A', 'gauche', 'économique', '0.00', 'actif'),
(74, 1, 13, 'B', 'gauche', 'économique', '0.00', 'actif'),
(75, 1, 13, 'C', 'gauche', 'économique', '0.00', 'actif'),
(76, 1, 13, 'D', 'droit', 'économique', '0.00', 'actif'),
(77, 1, 13, 'E', 'droit', 'économique', '0.00', 'actif'),
(78, 1, 13, 'F', 'droit', 'économique', '0.00', 'actif'),
(79, 1, 14, 'A', 'gauche', 'économique', '0.00', 'actif'),
(80, 1, 14, 'B', 'gauche', 'économique', '0.00', 'actif'),
(81, 1, 14, 'C', 'gauche', 'économique', '0.00', 'actif'),
(82, 1, 14, 'D', 'droit', 'économique', '0.00', 'actif'),
(83, 1, 14, 'E', 'droit', 'économique', '0.00', 'actif'),
(84, 1, 14, 'F', 'droit', 'économique', '0.00', 'actif'),
(85, 1, 15, 'A', 'gauche', 'économique', '0.00', 'actif'),
(86, 1, 15, 'B', 'gauche', 'économique', '0.00', 'actif'),
(87, 1, 15, 'C', 'gauche', 'économique', '0.00', 'actif'),
(88, 1, 15, 'D', 'droit', 'économique', '0.00', 'actif'),
(89, 1, 15, 'E', 'droit', 'économique', '0.00', 'actif'),
(90, 1, 15, 'F', 'droit', 'économique', '0.00', 'actif'),
(91, 1, 16, 'A', 'gauche', 'économique', '0.00', 'actif'),
(92, 1, 16, 'B', 'gauche', 'économique', '0.00', 'actif'),
(93, 1, 16, 'C', 'gauche', 'économique', '0.00', 'actif'),
(94, 1, 16, 'D', 'droit', 'économique', '0.00', 'actif'),
(95, 1, 16, 'E', 'droit', 'économique', '0.00', 'actif'),
(96, 1, 16, 'F', 'droit', 'économique', '0.00', 'actif'),
(97, 1, 17, 'A', 'gauche', 'économique', '0.00', 'actif'),
(98, 1, 17, 'B', 'gauche', 'économique', '0.00', 'actif'),
(99, 1, 17, 'C', 'gauche', 'économique', '0.00', 'actif'),
(100, 1, 17, 'D', 'droit', 'économique', '0.00', 'actif'),
(101, 1, 17, 'E', 'droit', 'économique', '0.00', 'actif'),
(102, 1, 17, 'F', 'droit', 'économique', '0.00', 'actif'),
(103, 1, 18, 'A', 'gauche', 'économique', '0.00', 'actif'),
(104, 1, 18, 'B', 'gauche', 'économique', '0.00', 'actif'),
(105, 1, 18, 'C', 'gauche', 'économique', '0.00', 'actif'),
(106, 1, 18, 'D', 'droit', 'économique', '0.00', 'actif'),
(107, 1, 18, 'E', 'droit', 'économique', '0.00', 'actif'),
(108, 1, 18, 'F', 'droit', 'économique', '0.00', 'actif'),
(109, 1, 19, 'A', 'gauche', 'économique', '0.00', 'actif'),
(110, 1, 19, 'B', 'gauche', 'économique', '0.00', 'actif'),
(111, 1, 19, 'C', 'gauche', 'économique', '0.00', 'actif'),
(112, 1, 19, 'D', 'droit', 'économique', '0.00', 'actif'),
(113, 1, 19, 'E', 'droit', 'économique', '0.00', 'actif'),
(114, 1, 19, 'F', 'droit', 'économique', '0.00', 'actif'),
(115, 1, 20, 'A', 'gauche', 'économique', '0.00', 'actif'),
(116, 1, 20, 'B', 'gauche', 'économique', '0.00', 'actif'),
(117, 1, 20, 'C', 'gauche', 'économique', '0.00', 'actif'),
(118, 1, 20, 'D', 'droit', 'économique', '0.00', 'actif'),
(119, 1, 20, 'E', 'droit', 'économique', '0.00', 'actif'),
(120, 1, 20, 'F', 'droit', 'économique', '0.00', 'actif'),
(121, 2, 1, 'A', 'gauche', 'première', '300.00', 'actif'),
(122, 2, 1, 'B', 'gauche', 'première', '300.00', 'actif'),
(123, 2, 1, 'C', 'gauche', 'première', '300.00', 'actif'),
(124, 2, 1, 'D', 'droit', 'première', '300.00', 'actif'),
(125, 2, 1, 'E', 'droit', 'première', '300.00', 'actif'),
(126, 2, 1, 'F', 'droit', 'première', '300.00', 'actif'),
(127, 2, 1, 'G', 'droit', 'première', '300.00', 'actif'),
(128, 2, 1, 'H', 'droit', 'première', '300.00', 'actif'),
(129, 2, 2, 'A', 'gauche', 'affaires', '150.00', 'actif'),
(130, 2, 2, 'B', 'gauche', 'affaires', '150.00', 'actif'),
(131, 2, 2, 'C', 'gauche', 'affaires', '150.00', 'actif'),
(132, 2, 2, 'D', 'droit', 'affaires', '150.00', 'actif'),
(133, 2, 2, 'E', 'droit', 'affaires', '150.00', 'actif'),
(134, 2, 2, 'F', 'droit', 'affaires', '150.00', 'actif'),
(135, 2, 2, 'G', 'droit', 'affaires', '150.00', 'actif'),
(136, 2, 2, 'H', 'droit', 'affaires', '150.00', 'actif'),
(137, 3, 1, 'A', 'gauche', 'première', '250.00', 'actif'),
(138, 3, 1, 'B', 'gauche', 'première', '250.00', 'actif'),
(139, 3, 1, 'C', 'gauche', 'première', '250.00', 'actif'),
(140, 3, 1, 'D', 'droit', 'première', '250.00', 'actif'),
(141, 3, 1, 'E', 'droit', 'première', '250.00', 'actif'),
(142, 3, 1, 'F', 'droit', 'première', '250.00', 'actif'),
(143, 3, 2, 'A', 'gauche', 'affaires', '120.00', 'actif'),
(144, 3, 2, 'B', 'gauche', 'affaires', '120.00', 'actif'),
(145, 3, 2, 'C', 'gauche', 'affaires', '120.00', 'actif'),
(146, 3, 2, 'D', 'droit', 'affaires', '120.00', 'actif'),
(147, 3, 2, 'E', 'droit', 'affaires', '120.00', 'actif'),
(148, 3, 2, 'F', 'droit', 'affaires', '120.00', 'actif'),
(149, 3, 3, 'A', 'gauche', 'économique', '0.00', 'actif'),
(150, 3, 3, 'B', 'gauche', 'économique', '0.00', 'actif'),
(151, 3, 3, 'C', 'gauche', 'économique', '0.00', 'actif'),
(152, 3, 3, 'D', 'droit', 'économique', '0.00', 'actif'),
(153, 3, 3, 'E', 'droit', 'économique', '0.00', 'actif'),
(154, 3, 3, 'F', 'droit', 'économique', '0.00', 'actif'),
(155, 3, 4, 'A', 'gauche', 'économique', '0.00', 'actif'),
(156, 3, 4, 'B', 'gauche', 'économique', '0.00', 'actif'),
(157, 3, 4, 'C', 'gauche', 'économique', '0.00', 'actif'),
(158, 3, 4, 'D', 'droit', 'économique', '0.00', 'actif'),
(159, 3, 4, 'E', 'droit', 'économique', '0.00', 'actif'),
(160, 3, 4, 'F', 'droit', 'économique', '0.00', 'actif'),
(161, 3, 5, 'A', 'gauche', 'économique', '0.00', 'actif'),
(162, 3, 5, 'B', 'gauche', 'économique', '0.00', 'actif'),
(163, 3, 5, 'C', 'gauche', 'économique', '0.00', 'actif'),
(164, 3, 5, 'D', 'droit', 'économique', '0.00', 'actif'),
(165, 3, 5, 'E', 'droit', 'économique', '0.00', 'actif'),
(166, 3, 5, 'F', 'droit', 'économique', '0.00', 'actif'),
(167, 3, 6, 'A', 'gauche', 'économique', '0.00', 'actif'),
(168, 3, 6, 'B', 'gauche', 'économique', '0.00', 'actif'),
(169, 3, 6, 'C', 'gauche', 'économique', '0.00', 'actif'),
(170, 3, 6, 'D', 'droit', 'économique', '0.00', 'actif'),
(171, 3, 6, 'E', 'droit', 'économique', '0.00', 'actif'),
(172, 3, 6, 'F', 'droit', 'économique', '0.00', 'actif'),
(173, 3, 7, 'A', 'gauche', 'économique', '0.00', 'actif'),
(174, 3, 7, 'B', 'gauche', 'économique', '0.00', 'actif'),
(175, 3, 7, 'C', 'gauche', 'économique', '0.00', 'actif'),
(176, 3, 7, 'D', 'droit', 'économique', '0.00', 'actif'),
(177, 3, 7, 'E', 'droit', 'économique', '0.00', 'actif'),
(178, 3, 7, 'F', 'droit', 'économique', '0.00', 'actif'),
(179, 3, 8, 'A', 'gauche', 'économique', '0.00', 'actif'),
(180, 3, 8, 'B', 'gauche', 'économique', '0.00', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `statut` enum('actif','inactif') NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `nom`, `prenom`, `email`, `telephone`, `password`, `role`, `statut`, `date_creation`) VALUES
(1, 'Salami', 'Awanat', 'awanatsalami.afo@gmail.com', '0700000001', '$2y$10$bWSFnsIG.6dgAd7YVa/YCO.MWs6ZQD8f3sy1gtoXeEWDeV6YtgKru', 'client', 'actif', '2025-10-07 22:26:46'),
(2, 'Folashade', 'Arike', 'arike@gmail.com', '0700000002', 'password123', 'client', 'actif', '2025-10-07 22:26:46'),
(3, 'Radji', 'Sad', 'admin@test.com', '0700000000', 'adminpass2003', 'admin', 'actif', '2025-10-07 22:26:46'),
(4, 'Agbalessi', 'Ruth', 'agbalessifloriane69@gmail.com', '0100000202', '1234567', 'admin', 'actif', '2025-10-11 04:45:37'),
(5, 'Degny', 'Alfred', 'alfred@gmail.com', '0102030407', '1234567', 'client', 'actif', '2025-10-11 05:12:38'),
(6, 'Kouamé', 'Mélina', 'mel@gmail.com', NULL, '1234567', 'client', 'actif', '2025-10-11 05:22:34'),
(7, 'Admin', 'System', 'admin@jetreserve.com', NULL, '$2y$10$nctt3DRye32/G/YQGz/gyujYL6riTfV9./PfWTHcSzCR7b0SMNGPa', 'admin', 'actif', '2025-10-17 23:48:33'),
(8, 'Banga', 'Christ', 'awanatsalami@gmail.com', NULL, '$2y$10$59yObNnnMOfLmgGrmPZxAuFmcBFwCD2g8QDzJ7GMj63pHThiLCeCa', 'client', 'actif', '2025-10-19 16:47:52');

-- --------------------------------------------------------

--
-- Structure de la table `vols`
--

DROP TABLE IF EXISTS `vols`;
CREATE TABLE IF NOT EXISTS `vols` (
  `id_vol` int(11) NOT NULL AUTO_INCREMENT,
  `id_compagnie` int(11) NOT NULL,
  `id_avion` int(11) NOT NULL,
  `depart` varchar(50) NOT NULL,
  `arrivee` varchar(50) NOT NULL,
  `date_depart` datetime NOT NULL,
  `date_arrivee` datetime NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `escales` int(11) DEFAULT '0',
  `classe` enum('économique','affaires','première') DEFAULT 'économique',
  `numero_vol` varchar(10) DEFAULT NULL,
  `places_disponibles` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_vol`),
  KEY `id_compagnie` (`id_compagnie`),
  KEY `id_avion` (`id_avion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `vols`
--

INSERT INTO `vols` (`id_vol`, `id_compagnie`, `id_avion`, `depart`, `arrivee`, `date_depart`, `date_arrivee`, `prix`, `escales`, `classe`, `numero_vol`, `places_disponibles`) VALUES
(1, 1, 1, 'Paris', 'Abidjan', '2025-11-12 08:00:00', '2025-11-15 14:00:00', '450.00', 0, 'économique', 'AF123', 172),
(2, 2, 2, 'Dubaï', 'Abidjan', '2025-11-20 22:00:00', '2025-11-21 06:00:00', '700.00', 1, 'affaires', 'EK456', 394),
(3, 3, 3, 'Istanbul', 'Paris', '2025-12-05 10:00:00', '2025-12-05 14:00:00', '350.00', 0, 'économique', 'TK789', 218);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avions`
--
ALTER TABLE `avions`
  ADD CONSTRAINT `avions_ibfk_1` FOREIGN KEY (`id_compagnie`) REFERENCES `compagnies` (`id_compagnie`) ON DELETE CASCADE;

--
-- Contraintes pour la table `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `emails_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id_reservation`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`id_vol`) REFERENCES `vols` (`id_vol`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservation_sieges`
--
ALTER TABLE `reservation_sieges`
  ADD CONSTRAINT `reservation_sieges_ibfk_1` FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id_reservation`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_sieges_ibfk_2` FOREIGN KEY (`id_siege`) REFERENCES `sieges_avion` (`id_siege`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sieges_avion`
--
ALTER TABLE `sieges_avion`
  ADD CONSTRAINT `sieges_avion_ibfk_1` FOREIGN KEY (`id_avion`) REFERENCES `avions` (`id_avion`) ON DELETE CASCADE;

--
-- Contraintes pour la table `vols`
--
ALTER TABLE `vols`
  ADD CONSTRAINT `vols_ibfk_1` FOREIGN KEY (`id_compagnie`) REFERENCES `compagnies` (`id_compagnie`) ON DELETE CASCADE,
  ADD CONSTRAINT `vols_ibfk_2` FOREIGN KEY (`id_avion`) REFERENCES `avions` (`id_avion`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
