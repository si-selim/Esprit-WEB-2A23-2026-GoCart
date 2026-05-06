-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 06 mai 2026 à 15:01
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
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `ID_produit` int(11) NOT NULL,
  `ID_stand` int(11) DEFAULT NULL,
  `nom_produit` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `prix_produit` decimal(10,2) DEFAULT NULL,
  `qte_stock` int(11) DEFAULT NULL,
  `en_out_stock` tinyint(1) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`ID_produit`, `ID_stand`, `nom_produit`, `type`, `prix_produit`, `qte_stock`, `en_out_stock`, `image`) VALUES
(7, 6, 'orange', 'boisont', 5896.00, 858, 0, NULL),
(18, 6, 'eau', 'boissan', 58999.00, 555, 1, 'prod_69e80437c059c.png'),
(26, 6, 'test', 'boisson', 8.00, 7, 1, NULL),
(28, 6, 'cafe glace', 'boissan', 203.00, 2, 1, 'prod_69efa67875736.png'),
(29, 6, 'lai', 'nuttrition', 5552.00, 55, 1, NULL),
(31, 6, 'test', 'nuttrition', 575.00, 57, 1, 'prod_69efa7f49d7a0.png'),
(32, 6, 'cafe glace', 'nuttrition', 2.00, 53, 1, 'prod_69efad86cbaad.png'),
(33, 6, 'cafe', 'nuttrition', 5.00, 1, 1, NULL),
(35, 6, '3asir', 'jk', 557.00, 81, 1, NULL),
(36, 14, 'jus', 'boissan', 545.00, 55, 1, NULL),
(37, 19, 'roooo', 'boisont', 45.00, 5, 1, 'prod_69f1f7ab2b331.jpg'),
(40, 16, 'apla', 'boisont', 57.00, 45, 1, NULL),
(41, 28, 'jus', 'nuttrition', 236.00, 2, 1, 'prod_69f3bb3425326.png'),
(42, 19, 'test', 'boisson', 50.00, 20, 1, 'prod_69f89c4974986.png'),
(43, 19, 'test', 'boisson', 50.00, 20, 1, 'prod_69fa3391b39fd.png');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`ID_produit`),
  ADD KEY `ID_stand` (`ID_stand`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `ID_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`ID_stand`) REFERENCES `stand` (`ID_stand`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
