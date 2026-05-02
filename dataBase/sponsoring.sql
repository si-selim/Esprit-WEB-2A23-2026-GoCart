-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 02 mai 2026 à 18:28
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
-- Structure de la table `sponsoring`
--

CREATE TABLE `sponsoring` (
  `idSponsoring` int(11) NOT NULL,
  `nomSponsoring` varchar(255) NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `montant` float NOT NULL,
  `etat` varchar(255) NOT NULL,
  `idSponsor` int(11) NOT NULL,
  `idMarathon` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sponsoring`
--

INSERT INTO `sponsoring` (`idSponsoring`, `nomSponsoring`, `dateDebut`, `dateFin`, `montant`, `etat`, `idSponsor`, `idMarathon`) VALUES
(7, 'pdfvvvvbbbbb', '2026-04-17', '2026-04-23', 155, 'Actif', 15, 1),
(11, 'Decathlon', '2026-04-02', '2026-04-17', 750, 'Terminé', 15, 1),
(12, 'contra', '2026-04-17', '2026-04-26', 100, 'Actif', 15, 1),
(14, 'Decathlonffff', '2026-04-10', '2026-04-19', 100, 'Actif', 15, 1),
(15, 'Decathlon', '2026-04-10', '2026-04-25', 1000000, 'Actif', 19, 1),
(18, 'rgd', '2026-04-01', '2026-04-25', 10000, 'Actif', 19, 1),
(19, 'ziwziw', '2026-04-21', '2026-04-29', 100, 'Actif', 19, 1),
(24, 'abcdgtrgrgrtg', '2026-04-01', '2026-04-25', 100, 'Actif', 15, 1),
(25, 'abcd', '2026-04-01', '2026-04-07', 100, 'Actif', 15, 1),
(29, 'courir pour sauver les abeilles', '2026-04-30', '2026-05-29', 2000, 'Actif', 24, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `sponsoring`
--
ALTER TABLE `sponsoring`
  ADD PRIMARY KEY (`idSponsoring`),
  ADD KEY `fk_sponsoring_sponsor` (`idSponsor`),
  ADD KEY `fk_sponsoring_marathon` (`idMarathon`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `sponsoring`
--
ALTER TABLE `sponsoring`
  MODIFY `idSponsoring` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `sponsoring`
--
ALTER TABLE `sponsoring`
  ADD CONSTRAINT `fk_sponsor` FOREIGN KEY (`idSponsor`) REFERENCES `sponsor` (`idSponsor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sponsoring_marathon` FOREIGN KEY (`idMarathon`) REFERENCES `marathon` (`id_marathon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sponsoring_sponsor` FOREIGN KEY (`idSponsor`) REFERENCES `sponsor` (`idSponsor`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
