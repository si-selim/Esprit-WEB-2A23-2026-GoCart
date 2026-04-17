-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 17 avr. 2026 à 14:44
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
  `en_out_stock` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`ID_produit`, `ID_stand`, `nom_produit`, `type`, `prix_produit`, `qte_stock`, `en_out_stock`) VALUES
(3, 8, 'apla', 'boisont', 5896.00, 858, 0),
(4, 8, 'apla', 'boisont', 5896.00, 858, 0),
(5, 10, 'coca', 'boisont', 5896.00, 858, 0),
(6, 1, 'FANTTA', 'boisont', 5896.00, 858, 0),
(7, 6, 'orange', 'boisont', 5896.00, 858, 0),
(8, 6, 'orange', 'boisont', 5896.00, 858, 0),
(9, 10, 'orange', 'boisont', 5896.00, 858, 0),
(10, 10, 'orange', 'boisont', 5896.00, 858, 0),
(11, 9, 'orange', 'boisont', 5896.00, 858, 0),
(12, 9, 'oronginaaaa', 'boisont', 5896.00, 858, 1);

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
  MODIFY `ID_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
