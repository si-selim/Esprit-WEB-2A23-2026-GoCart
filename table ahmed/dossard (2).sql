-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 06 mai 2026 à 21:04
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
-- Base de données : `projetwebinscription+dossard`
--

-- --------------------------------------------------------

--
-- Structure de la table `dossard`
--

CREATE TABLE `dossard` (
  `id_dossard` int(11) NOT NULL,
  `nom` varchar(25) NOT NULL,
  `numero` int(11) NOT NULL,
  `taille` varchar(4) NOT NULL,
  `id_inscription` int(11) NOT NULL,
  `couleur` varchar(50) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `dossard`
--

INSERT INTO `dossard` (`id_dossard`, `nom`, `numero`, `taille`, `id_inscription`, `couleur`, `qr_code`) VALUES
(180, 'ahmed', 1, 'S', 149, '#d21e1e', 'qr_180.png'),
(181, 'ahmed', 2, 'S', 149, '#d21414', 'qr_181.png'),
(182, 'rayen', 3, 'M', 150, '#dfb3b3', 'qr_182.png'),
(183, 'rayen', 4, 'L', 150, '#4680be', 'qr_183.png');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `dossard`
--
ALTER TABLE `dossard`
  ADD PRIMARY KEY (`id_dossard`),
  ADD KEY `fk_dossard_inscription` (`id_inscription`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `dossard`
--
ALTER TABLE `dossard`
  MODIFY `id_dossard` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `dossard`
--
ALTER TABLE `dossard`
  ADD CONSTRAINT `fk_dossard_inscription` FOREIGN KEY (`id_inscription`) REFERENCES `inscription` (`id_inscription`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_idinscription` FOREIGN KEY (`id_inscription`) REFERENCES `inscription` (`id_inscription`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
