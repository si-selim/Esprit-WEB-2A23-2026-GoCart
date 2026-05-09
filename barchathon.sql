-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 09 mai 2026 à 19:10
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
  `datecommande` datetime NOT NULL,
  `statut` varchar(100) NOT NULL DEFAULT 'En cours',
  `montanttotale` float NOT NULL,
  `modePaiement` varchar(100) NOT NULL,
  `idorganisateur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`idcommande`, `idutilisateur`, `idstand`, `datecommande`, `statut`, `montanttotale`, `modePaiement`, `idorganisateur`) VALUES
(14, 10, 1, '2026-05-04 13:04:02', 'confirmé', 5896, 'stripe', 2),
(15, 11, 1, '2026-05-06 14:17:35', 'non valide', 5896, 'en attente', 2),
(16, 11, 1, '2026-05-04 14:28:54', 'confirmé', 5896, 'stripe', 2),
(17, 11, 1, '2026-05-06 16:11:50', 'non valide', 1000, 'en attente', 2),
(18, 11, 1, '2026-05-06 23:39:06', 'non valide', 1500, 'en attente', 2),
(20, 10, 1, '2026-05-07 00:52:34', 'non valide', 700, 'en attente', 2),
(21, 10, 32, '2026-05-09 16:51:35', 'confirmé', 17, 'stripe', 0),
(22, 10, 32, '2026-05-09 16:51:57', 'confirmé', 60, 'stripe', 0);

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
(205, 'Ahmed', 1, 'S', 149, '#ff0000', 'qr_205.png'),
(206, 'fff', 2, 'M', 167, '#750f5a', NULL),
(207, 'fff', 3, 'S', 168, '#0f766e', NULL),
(208, 'kk', 4, 'S', 169, '#0f766e', NULL),
(209, 'mmm', 5, 'M', 170, '#0f766e', NULL),
(210, 'ggg', 6, 'S', 172, '#0f766e', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `favorites`
--

CREATE TABLE `favorites` (
  `id_favorite` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `favorites`
--

INSERT INTO `favorites` (`id_favorite`, `id_user`, `id_produit`) VALUES
(15, 10, 2),
(14, 10, 5);

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL,
  `nb_personnes` int(11) NOT NULL,
  `mode_de_paiement` varchar(25) NOT NULL,
  `date_inscription` datetime NOT NULL,
  `date_paiement` datetime NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_parcours` int(11) NOT NULL,
  `statut_paiement` varchar(20) DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscription`
--

INSERT INTO `inscription` (`id_inscription`, `nb_personnes`, `mode_de_paiement`, `date_inscription`, `date_paiement`, `id_user`, `id_parcours`, `statut_paiement`) VALUES
(168, 1, 'cash', '2026-05-08 20:23:25', '2026-05-08 20:23:25', 3, 49, 'unpaid'),
(169, 1, 'cash', '2026-05-08 21:07:47', '2026-05-08 21:07:47', 3, 49, 'unpaid'),
(170, 1, 'cash', '2026-05-08 21:11:37', '2026-05-08 21:11:37', 3, 52, 'unpaid');

-- --------------------------------------------------------

--
-- Structure de la table `lignecommande`
--

CREATE TABLE `lignecommande` (
  `idligne` int(11) NOT NULL,
  `idcommande` int(11) NOT NULL,
  `idproduit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prixunitaire` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lignecommande`
--

INSERT INTO `lignecommande` (`idligne`, `idcommande`, `idproduit`, `quantite`, `prixunitaire`) VALUES
(1, 1, 1, 5, 20),
(2, 1, 2, 2, 10),
(4, 2, 5, 5, 5),
(5, 21, 45, 1, 6),
(6, 21, 46, 1, 6),
(7, 21, 47, 1, 5),
(8, 22, 46, 10, 6);

--
-- Déclencheurs `lignecommande`
--
DELIMITER $$
CREATE TRIGGER `update_montant_after_insert` AFTER INSERT ON `lignecommande` FOR EACH ROW BEGIN
    UPDATE commande
    SET montanttotale = (
        SELECT SUM(quantite * prixunitaire)
        FROM lignecommande
        WHERE idcommande = NEW.idcommande
    )
    WHERE idcommande = NEW.idcommande;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `marathon`
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
-- Déchargement des données de la table `marathon`
--

INSERT INTO `marathon` (`id_marathon`, `nom_marathon`, `image_marathon`, `organisateur_marathon`, `region_marathon`, `date_marathon`, `nb_places_dispo`, `prix_marathon`) VALUES
(40, 'Marathon de la Côte', 'images/uploads/1777899026_69f89612b6b92.png', 'Organisateur Demo', 'Nabeul', '2026-05-08', 100, 5),
(41, 'Marathon du Défi', 'images/uploads/1778147741_69fc619db50bf.png', 'Organisateur Demo', 'Tunis-Ariana', '2026-05-09', 200, 6),
(43, 'Marathon de Carthage', 'images/uploads/1778147773_69fc61bdf29bc.png', 'Organisateur Demo', 'Tunis', '2026-05-15', 90, 3);

-- --------------------------------------------------------

--
-- Structure de la table `parcours`
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
-- Déchargement des données de la table `parcours`
--

INSERT INTO `parcours` (`id_parcours`, `nom_parcours`, `point_depart`, `point_arrivee`, `distance`, `difficulte`, `id_marathon`, `heure_depart`) VALUES
(48, 'Parcours Yasmine Run', 'Hammamet Yasmine', 'Grombalia', 28.66, 'difficile', 40, '16:00:00'),
(49, 'Parcours Marina Run', 'Marina Hammamet', 'Bou Argoub', 25.55, 'moyen', 40, '11:00:00'),
(50, 'Parcours Corniche Run', 'Corniche de Nabeul', 'Maâmoura', 9.79, 'facile', 40, '05:00:00'),
(51, 'Parcours Urbain Tunis-Ariana ', 'Avenue Habib Bourguiba', 'Ariana Ville', 20.50, 'moyen', 41, '14:00:00'),
(52, 'Parocurs Tunis-Ariana ', 'Corniche La Marsa', 'Rue El Khansa, Cité Taeib M\'hiri', 8.48, 'facile', 41, '10:00:00'),
(58, 'parcours1', 'Avenue Habib Bourguiba', 'Sidi Bou Saïd', 9.59, 'facile', 43, '15:00:00'),
(59, 'parcours2', 'La Marsa', 'Carthage', 13.92, 'moyen', 43, '10:00:00'),
(60, 'parcours3', 'Bardo', 'Les Berges du Lac', 32.26, 'difficile', 43, '05:00:00'),
(61, 'T-A', 'Bardo', 'Les Berges du Lac', 32.26, 'difficile', 41, '06:00:00');

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
(38, 19, 'orange', 'boisont', 45.00, 5, 1, 'prod_69f20bd0086f8.png'),
(40, 16, 'apla', 'boisont', 57.00, 45, 1, NULL),
(41, 28, 'jus', 'nuttrition', 236.00, 2, 1, 'prod_69f3bb3425326.png'),
(42, 30, 'cddd', 'ddd', 55.00, 555, 1, 'prod_69f7db1d7b40e.png'),
(44, 33, 'RTR', 'VHJV', 56.00, 5, 1, ''),
(45, 32, 'chocolat noir', 'nutrition', 6.00, 49, 1, 'prod_69ff574e70cb1.jpg'),
(46, 32, 'banane', 'nutrition', 6.00, 9, 1, 'prod_69ff57677d33f.jpg'),
(47, 32, 'monster', 'boisant', 5.00, 29, 1, 'prod_69ff57837e6ed.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `sponsor`
--

CREATE TABLE `sponsor` (
  `idSponsor` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pageWeb` varchar(255) NOT NULL,
  `idUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sponsor`
--

INSERT INTO `sponsor` (`idSponsor`, `nom`, `type`, `adresse`, `contact`, `email`, `pageWeb`, `idUser`) VALUES
(28, 'Med Mokhtar Ben khaled', 'Association', 'Ariana', '13214564', 'loulou.arfaoui72@gmail.com', '', 2);

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
(18, '3asir', 'bejaa', 'kkkkkkkkkkkkkkkk', 2),
(19, 'cacao', 'sidi thabet', 'jjjjjjjjjjj', 3),
(20, 'mokh', 'oued lil', 'edefr efef', 5),
(21, 'souhaa', 'raoued', 'dfeff efefefv', 9),
(23, 'mokh', 'tunis', 'jjhinkn', 8),
(25, 'rouriiiiiiiii', 'manouba', 'knkkk', 3),
(26, 'kaki', 'oued lil', 'n;jlknol', 2),
(27, 'jus', 'oued lil', 'jbjhkjhn', 4),
(28, 'cafe', 'ariana', 'kuhiivyhujy', 3),
(30, 'dddddddddddd', 'ddd', 'ddddcdec', 39),
(32, 'ldd', '5', 'ddddddd', 48),
(33, '2UYH', '5', 'TDDDTRRR', 51);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `nom_user` varchar(50) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `poids` float DEFAULT NULL,
  `taille` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','participant','organisateur') NOT NULL DEFAULT 'participant',
  `status` enum('active','banned') NOT NULL DEFAULT 'active',
  `last_active_at` datetime DEFAULT NULL,
  `sexe` enum('homme','femme','autre') DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `face_descriptor` text DEFAULT NULL,
  `google_id` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_logout_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `nom_complet`, `nom_user`, `mot_de_passe`, `age`, `poids`, `taille`, `email`, `pays`, `ville`, `tel`, `occupation`, `profile_picture`, `role`, `status`, `last_active_at`, `sexe`, `verified`, `verification_token`, `face_descriptor`, `google_id`, `reset_token`, `reset_token_expires`, `last_logout_at`) VALUES
(1, 'Administrateur', 'admin', 'admin', NULL, NULL, NULL, 'admin@barchathon.tn', NULL, NULL, NULL, NULL, NULL, 'admin', 'active', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Organisateur Demo', 'organisateur', '$2y$10$OTWqaCplEDuU4T6oE2.Bgu9SJnb6OdeHmGbzwtPXcGvmUKGg1DFPi', NULL, NULL, NULL, 'orga@barchathon.tn', NULL, NULL, NULL, NULL, NULL, 'organisateur', 'active', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Participant Demo', 'participant', '$2y$10$m/QCqoT/.uOK3cwuCOjC0uRuVPLDHqedWbYQg.0ESc1jgDYclhJR.', 50, 80, 180, 'participant@barchathon.tn', NULL, NULL, NULL, NULL, NULL, 'participant', 'active', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Anas', 'Touil', '$2y$10$2RY.NZKr4QnnGDmaG5hpH.LN9BTKByJfv0QNmh.RZ60JYAumMQU5a', 50, 50, 180, 'Anas.Touil@esprit.tn', 'Tunisia', 'Kairouan', '28306227', NULL, 'pp_69ecea58040fe5.13698447.webp', 'participant', 'active', '2026-05-04 15:06:23', 'homme', 0, '0531299e349b484507f78ec4b49093cccaa316140acba16a13cc5ae030656aff', NULL, NULL, '6252c440bf77242dd3b448b4aac9a290757d0eada68f3e325d8ba30bd5652ddb', '2026-04-24 22:01:51', '2026-05-04 14:00:59'),
(6, 'BarchaThon', 'barchathon', '$2y$10$yKoWPjtL1dGBuHLOAVht6.cHvh19EcDjmbBmUjl3wBDi7fweROrqq', NULL, NULL, NULL, 'barchathon@gmail.com', NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocJHvxaJHMMh-ZhqdXEa9Bva7eNes77tt7mU6bDpqU8my5vliA=s96-c', 'participant', 'active', NULL, NULL, 1, NULL, NULL, '104889734528514256034', NULL, NULL, NULL),
(7, 'www', 'www', '$2y$10$cLMgqqLAhSJAwgfbRzqPxeF81/sBsgr.5U0wwWNr4SLEktVCCaqcK', 20, 20, 100, 'sd@gmail.com', 'tn', 'tn', '43546465', NULL, NULL, 'organisateur', 'active', NULL, 'homme', 0, '42e7b25e5da0c04dd6a14d7a7382b60ed75774169286b5bf41b598f9c4f4e0f5', NULL, NULL, NULL, NULL, NULL),
(8, 'eee', 'eee', '$2y$10$mPxukPubG0HKxebXCOLHlOO2mFsUESyO./4IbwkcRcJZkCO745IeC', 20, 50, 170, 'touilanas00@gmail.com', 'Tunisia', 'Kairouan', '28306227', NULL, 'pp_69ef6083e0fdc1.87649496.webp', 'admin', 'active', '2026-05-04 15:04:56', 'homme', 1, NULL, '[-0.11707562208175659,0.07686099410057068,0.03598225861787796,-0.059945330023765564,-0.0576687715947628,-0.05551685392856598,0.022898292168974876,-0.10492634773254395,0.13207502663135529,-0.06920177489519119,0.31058526039123535,-0.06346745789051056,-0.20420685410499573,0.025148022919893265,-0.015554527752101421,0.16296333074569702,-0.21611127257347107,-0.07141030579805374,-0.1392584890127182,-0.05579861253499985,0.09314735978841782,0.05220949649810791,-0.08126748353242874,0.09251997619867325,-0.18360662460327148,-0.26499536633491516,-0.08404802531003952,-0.06395968794822693,0.015562111511826515,-0.043044064193964005,0.00002606001180538442,0.024065392091870308,-0.1223096251487732,-0.04887368902564049,0.07169043272733688,0.04771106690168381,-0.010680371895432472,-0.030258264392614365,0.14668428897857666,-0.03373243659734726,-0.20358559489250183,0.09192854166030884,0.14703933894634247,0.26937517523765564,0.1929420381784439,0.12908846139907837,0.01691819541156292,-0.02741529792547226,0.059564534574747086,-0.24464914202690125,0.0597320981323719,0.06313689053058624,0.20632395148277283,0.03712041303515434,0.09115691483020782,-0.21600893139839172,-0.038351912051439285,0.15272225439548492,-0.16788937151432037,0.10920592397451401,0.10304819047451019,-0.11551257222890854,0.053360238671302795,-0.04433484002947807,0.20586548745632172,0.07035011798143387,-0.16819970309734344,-0.03213374316692352,0.16301415860652924,-0.13880525529384613,-0.07469744235277176,0.00037771163624711335,-0.11864971369504929,-0.22324834764003754,-0.3186737298965454,0.042445093393325806,0.44277724623680115,0.1550922989845276,-0.17816688120365143,0.03135990351438522,0.028557883575558662,0.06832193583250046,0.09774437546730042,0.04753177613019943,-0.09575550258159637,-0.07238412648439407,-0.09901831299066544,0.03088715299963951,0.2240755707025528,0.02586377039551735,-0.0058655948378145695,0.24282656610012054,-0.042721524834632874,-0.046774256974458694,-0.0060861920937895775,0.009601480327546597,-0.07536105811595917,-0.01812046952545643,-0.040323592722415924,0.044576242566108704,-0.0004355185083113611,-0.08269504457712173,0.05258813127875328,0.12868012487888336,-0.20511722564697266,0.11873748898506165,-0.04482165351510048,0.02635609731078148,-0.02196710743010044,0.12492652237415314,-0.09004215151071548,-0.0006383003783412278,0.14271491765975952,-0.24737292528152466,0.17755812406539917,0.19638948142528534,0.005071531515568495,0.12749172747135162,0.05294708535075188,0.04635413736104965,0.03362489864230156,0.03728245943784714,-0.20493033528327942,-0.08721716701984406,0.06642085313796997,-0.11461347341537476,0.17440195381641388,0.0764831081032753]', '117094415717229109263', NULL, NULL, '2026-05-04 15:02:35'),
(9, 'Ywk Ert', 'ywkert', '$2y$10$/Ua/1ol1Po.tkPjIHcx7TONhg.x3Wj.zdB1aps1XbZFYBej.rGMNa', NULL, NULL, NULL, 'ywkert@gmail.com', NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIe1Uz0UahDXpwMIbTzP6l3U85C1juA85FL9iXgpuCg3jcb1g=s96-c', 'participant', 'active', NULL, NULL, 1, NULL, NULL, '100060275676340533419', NULL, NULL, NULL),
(10, 'MOKH', 'mokh', '$2y$10$VExRAiIjrNRDMVHAc8LUf.ZpokMbQzKmUNsKx4MPwmuML0tuTvnv2', 25, 70, 175, 'loulou.arfaoui72@gmail.com', 'Tunisia', 'Tunisie', '98200136', 'Etudiant', NULL, 'participant', 'active', NULL, 'homme', 0, 'ab5ad19536a46abd863c0783fc1c17a44c565b0cbcce4f629245beea77239b23', NULL, NULL, NULL, NULL, NULL);

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
  ADD KEY `idx_commande_statut` (`statut`),
  ADD KEY `idx_commande_idorganisateur` (`idorganisateur`);

--
-- Index pour la table `dossard`
--
ALTER TABLE `dossard`
  ADD PRIMARY KEY (`id_dossard`),
  ADD KEY `fk_dossard_inscription` (`id_inscription`);

--
-- Index pour la table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id_favorite`),
  ADD UNIQUE KEY `user_product` (`id_user`,`id_produit`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id_inscription`),
  ADD KEY `fk_user` (`id_user`),
  ADD KEY `fk_idparcours` (`id_parcours`);

--
-- Index pour la table `lignecommande`
--
ALTER TABLE `lignecommande`
  ADD PRIMARY KEY (`idligne`),
  ADD KEY `fk_commande` (`idcommande`);

--
-- Index pour la table `marathon`
--
ALTER TABLE `marathon`
  ADD PRIMARY KEY (`id_marathon`);

--
-- Index pour la table `parcours`
--
ALTER TABLE `parcours`
  ADD PRIMARY KEY (`id_parcours`),
  ADD KEY `fk_marathon` (`id_marathon`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`ID_produit`),
  ADD KEY `ID_stand` (`ID_stand`);

--
-- Index pour la table `sponsor`
--
ALTER TABLE `sponsor`
  ADD PRIMARY KEY (`idSponsor`),
  ADD KEY `idUser` (`idUser`);

--
-- Index pour la table `sponsoring`
--
ALTER TABLE `sponsoring`
  ADD PRIMARY KEY (`idSponsoring`),
  ADD KEY `fk_sponsoring_sponsor` (`idSponsor`),
  ADD KEY `fk_sponsoring_marathon` (`idMarathon`);

--
-- Index pour la table `stand`
--
ALTER TABLE `stand`
  ADD PRIMARY KEY (`ID_stand`),
  ADD KEY `ID_parcours` (`ID_parcours`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uk_nom_user` (`nom_user`),
  ADD KEY `idx_user_last_active_at` (`last_active_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `idcommande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `dossard`
--
ALTER TABLE `dossard`
  MODIFY `id_dossard` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT pour la table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id_favorite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT pour la table `lignecommande`
--
ALTER TABLE `lignecommande`
  MODIFY `idligne` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `marathon`
--
ALTER TABLE `marathon`
  MODIFY `id_marathon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `parcours`
--
ALTER TABLE `parcours`
  MODIFY `id_parcours` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `ID_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT pour la table `sponsor`
--
ALTER TABLE `sponsor`
  MODIFY `idSponsor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `sponsoring`
--
ALTER TABLE `sponsoring`
  MODIFY `idSponsoring` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `stand`
--
ALTER TABLE `stand`
  MODIFY `ID_stand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `parcours`
--
ALTER TABLE `parcours`
  ADD CONSTRAINT `fk_marathon` FOREIGN KEY (`id_marathon`) REFERENCES `marathon` (`id_marathon`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`ID_stand`) REFERENCES `stand` (`ID_stand`);

--
-- Contraintes pour la table `sponsor`
--
ALTER TABLE `sponsor`
  ADD CONSTRAINT `fk_sponsor_user` FOREIGN KEY (`idUser`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

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
