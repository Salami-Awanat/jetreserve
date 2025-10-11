-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 11 oct. 2025 à 17:05
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
-- Structure de la table `compagnies`
--

DROP TABLE IF EXISTS `compagnies`;
CREATE TABLE IF NOT EXISTS `compagnies` (
  `id_compagnie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_compagnie` varchar(100) NOT NULL,
  `code_compagnie` varchar(10) NOT NULL,
  PRIMARY KEY (`id_compagnie`),
  UNIQUE KEY `code_compagnie` (`code_compagnie`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `compagnies`
--

INSERT INTO `compagnies` (`id_compagnie`, `nom_compagnie`, `code_compagnie`) VALUES
(1, 'Air France', 'AF'),
(2, 'Emirates', 'EK'),
(3, 'Turkish Airlines', 'TK');

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
  PRIMARY KEY (`id_reservation`),
  KEY `id_user` (`id_user`),
  KEY `id_vol` (`id_vol`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id_reservation`, `id_user`, `id_vol`, `statut`, `date_reservation`) VALUES
(1, 1, 1, 'confirmé', '2025-10-07 22:26:46'),
(2, 2, 2, 'en attente', '2025-10-07 22:26:46');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `nom`, `prenom`, `email`, `telephone`, `password`, `role`, `statut`, `date_creation`) VALUES
(1, 'Salami', 'Awanat', 'awanatsalami.afo@gmail.com', '0700000001', 'password2005', 'client', 'actif', '2025-10-07 22:26:46'),
(2, 'Folashade', 'Arike', 'arike@gmail.com', '0700000002', 'password123', 'client', 'actif', '2025-10-07 22:26:46'),
(3, 'Radji', 'Sad', 'admin@test.com', '0700000000', 'adminpass2003', 'admin', 'actif', '2025-10-07 22:26:46'),
(4, 'Agbalessi', 'Ruth', 'agbalessifloriane69@gmail.com', '0100000202', '1234567', 'admin', 'actif', '2025-10-11 04:45:37'),
(5, 'Degny', 'Alfred', 'alfred@gmail.com', '0102030407', '1234567', 'client', 'actif', '2025-10-11 05:12:38'),
(6, 'Kouamé', 'Mélina', 'mel@gmail.com', NULL, '1234567', 'client', 'actif', '2025-10-11 05:22:34');

-- --------------------------------------------------------

--
-- Structure de la table `vols`
--

DROP TABLE IF EXISTS `vols`;
CREATE TABLE IF NOT EXISTS `vols` (
  `id_vol` int(11) NOT NULL AUTO_INCREMENT,
  `id_compagnie` int(11) NOT NULL,
  `depart` varchar(50) NOT NULL,
  `arrivee` varchar(50) NOT NULL,
  `date_depart` datetime NOT NULL,
  `date_arrivee` datetime NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `escales` int(11) DEFAULT '0',
  `classe` enum('économique','affaires','première') DEFAULT 'économique',
  PRIMARY KEY (`id_vol`),
  KEY `id_compagnie` (`id_compagnie`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `vols`
--

INSERT INTO `vols` (`id_vol`, `id_compagnie`, `depart`, `arrivee`, `date_depart`, `date_arrivee`, `prix`, `escales`, `classe`) VALUES
(1, 1, 'Paris', 'Abidjan', '2025-11-12 08:00:00', '2025-11-15 14:00:00', '450.00', 0, 'économique'),
(2, 2, 'Dubaï', 'Abidjan', '2025-11-20 22:00:00', '2025-11-21 06:00:00', '700.00', 1, 'affaires'),
(3, 3, 'Istanbul', 'Paris', '2025-12-05 10:00:00', '2025-12-05 14:00:00', '350.00', 0, 'économique');

--
-- Contraintes pour les tables déchargées
--

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
-- Contraintes pour la table `vols`
--
ALTER TABLE `vols`
  ADD CONSTRAINT `vols_ibfk_1` FOREIGN KEY (`id_compagnie`) REFERENCES `compagnies` (`id_compagnie`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
