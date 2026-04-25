-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 25 avr. 2026 à 14:29
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
-- Base de données : `barchathon`
--

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `idcommande` int(11) NOT NULL,
  `idutilisateur` int(11) NOT NULL,
  `idstand` int(11) NOT NULL,
  `datecommande` date NOT NULL,
  `statut` varchar(100) NOT NULL DEFAULT 'En cours',
  `montanttotale` float NOT NULL,
  `modePaiement` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`idcommande`, `idutilisateur`, `idstand`, `datecommande`, `statut`, `montanttotale`, `modePaiement`) VALUES
(2, 3, 5, '2026-04-16', 'validée', 25, ''),
(3, 1, 10, '2026-04-30', 'validée', 0, ''),
(4, 4, 2, '2026-05-07', 'En cours', 190, ''),
(5, 1, 1, '2026-04-13', 'non valide', 304.6, ''),
(6, 1, 1, '2026-04-13', 'en cours', 224.9, ''),
(7, 1, 1, '2026-04-20', 'en cours', 104.4, ''),
(9, 1, 1, '2026-04-25', 'en cours', 10, ''),
(10, 1, 1, '2026-04-25', 'en cours', 2.5, ''),
(11, 1, 1, '2026-04-25', 'en cours', 100.5, 'd17'),
(12, 4, 1, '2026-04-25', 'en cours', 5898.5, 'paypal');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`idcommande`),
  ADD KEY `idx_commande_user` (`idutilisateur`),
  ADD KEY `idx_commande_stand` (`idstand`),
  ADD KEY `idx_commande_statut` (`statut`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `idcommande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
