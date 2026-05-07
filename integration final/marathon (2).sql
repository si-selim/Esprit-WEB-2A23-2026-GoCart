-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 06:42 AM
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
-- Table structure for table `marathon`
--

CREATE TABLE `marathon` (
  `id_marathon` int(11) NOT NULL,
  `nom_marathon` varchar(100) DEFAULT NULL,
  `image_marathon` varchar(255) DEFAULT NULL,
  `organisateur_marathon` varchar(100) DEFAULT NULL,
  `region_marathon` varchar(100) DEFAULT NULL,
  `date_marathon` date DEFAULT NULL,
  `nb_places_dispo` int(11) DEFAULT NULL,
  `prix_marathon` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marathon`
--

INSERT INTO `marathon` (`id_marathon`, `nom_marathon`, `image_marathon`, `organisateur_marathon`, `region_marathon`, `date_marathon`, `nb_places_dispo`, `prix_marathon`) VALUES
(40, 'Marathon de la Côte', 'images/uploads/1777899026_69f89612b6b92.png', 'Organisateur Demo', 'Nabeul', '2026-05-08', 100, 5),
(41, 'Marathon du Défi', 'images/uploads/1778128547_69fc16a3ea2ea.png', 'Organisateur Demo', 'Tunis-Ariana', '2026-05-09', 200, 6),
(43, 'Marathon de Carthage', 'images/uploads/1778128504_69fc16783730f.png', 'Organisateur Demo', 'Tunis', '2026-05-15', 90, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `marathon`
--
ALTER TABLE `marathon`
  ADD PRIMARY KEY (`id_marathon`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `marathon`
--
ALTER TABLE `marathon`
  MODIFY `id_marathon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
