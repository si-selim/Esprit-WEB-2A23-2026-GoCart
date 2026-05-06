-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 06 mai 2026 à 15:02
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

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
-- Structure de la table `stand`
--

CREATE TABLE `stand` (
  `ID_stand` int(11) NOT NULL,
  `nom_stand` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ID_parcours` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stand`
--

INSERT INTO `stand` (`ID_stand`, `nom_stand`, `position`, `description`, `ID_parcours`) VALUES
(6, 'azizzzzz', 'manouba', 'hdmdlh7 ya rabi ouuuuh rte7t', 6),
(13, 'roua kadri', 'manzah', 'hihubnjh', 8),
(14, 'roua kadri', 'manzah5', 'hhhhhhhhhhh', 55),
(16, 'gazouz', 'ariana', 'iiiiiiiiih', 3),
(19, 'cacao', 'sidi thabet', 'jjjjjjjjjjj', 3),
(20, 'mokh', 'oued lil', 'edefr efef', 5),
(21, 'souhaa', 'raoued', 'dfeff efefefv', 9),
(23, 'mokh', 'tunis', 'jjhinkn', 8),
(25, 'rouriiiiiiiii', 'manouba', 'knkkk', 3),
(26, 'kaki', 'oued lil', 'n;jlknol', 2),
(28, 'cafe', 'ariana', 'kuhiivyhujy', 3),
(30, 'JUS', 'Oued Ellil', 'refrenche', 3),
(31, 'Stand nutrition', 'beja', 'nutrition', 4),
(32, 'energie', 'france', 'très bien', 3);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `stand`
--
ALTER TABLE `stand`
  ADD PRIMARY KEY (`ID_stand`),
  ADD KEY `ID_parcours` (`ID_parcours`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `stand`
--
ALTER TABLE `stand`
  MODIFY `ID_stand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
