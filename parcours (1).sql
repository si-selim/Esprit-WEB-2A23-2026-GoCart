-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 02:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barchathon`
--

-- --------------------------------------------------------

--
-- Table structure for table `parcours`
--

CREATE TABLE `parcours` (
  `id_parcours` int(11) NOT NULL,
  `nom_parcours` varchar(100) DEFAULT NULL,
  `point_depart` varchar(100) DEFAULT NULL,
  `point_arrivee` varchar(100) DEFAULT NULL,
  `distance` decimal(5,2) DEFAULT NULL,
  `difficulte` varchar(20) DEFAULT NULL,
  `id_marathon` int(11) DEFAULT NULL,
  `heure_depart` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcours`
--

INSERT INTO `parcours` (`id_parcours`, `nom_parcours`, `point_depart`, `point_arrivee`, `distance`, `difficulte`, `id_marathon`, `heure_depart`) VALUES
(48, 'Parcours Yasmine Run', 'Hammamet Yasmine', 'Grombalia', 28.66, 'difficile', 40, '16:00:00'),
(49, 'Parcours Marina Run', 'Marina Hammamet', 'Bou Argoub', 25.55, 'moyen', 40, '11:00:00'),
(50, 'Parcours Corniche Run', 'Corniche de Nabeul', 'Maâmoura', 9.79, 'facile', 40, '05:00:00'),
(51, 'Parcours Urbain Tunis-Ariana ', 'Avenue Habib Bourguiba', 'Ariana Ville', 20.50, 'moyen', 41, '14:00:00'),
(52, 'Parocurs Tunis-Ariana ', 'Corniche La Marsa', 'Rue El Khansa, Cité Taeib M\'hiri', 8.48, 'facile', 41, '10:00:00'),
(53, 'hhh', 'Nabeul', 'Dar Chaâbane El Fehri', 6.98, 'facile', 40, '22:22:00'),
(54, 'ddd', 'Nabeul Centre', 'Hammamet Nord', 12.72, 'facile', 40, '02:59:00'),
(55, 'hhhh', 'Manzel Temim', 'Beni Khiar', 25.84, 'difficile', 40, '04:04:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `parcours`
--
ALTER TABLE `parcours`
  ADD PRIMARY KEY (`id_parcours`),
  ADD KEY `fk_marathon` (`id_marathon`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `parcours`
--
ALTER TABLE `parcours`
  MODIFY `id_parcours` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `parcours`
--
ALTER TABLE `parcours`
  ADD CONSTRAINT `fk_marathon` FOREIGN KEY (`id_marathon`) REFERENCES `marathon` (`id_marathon`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
