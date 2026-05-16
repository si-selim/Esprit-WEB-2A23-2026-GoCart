-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 16 mai 2026 à 05:20
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
  `idorganisateur` int(11) DEFAULT NULL,
  `remise` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`idcommande`, `idutilisateur`, `idstand`, `datecommande`, `statut`, `montanttotale`, `modePaiement`, `idorganisateur`, `remise`) VALUES
(32, 10, 32, '2026-05-14 03:13:22', 'confirmé', 27, 'stripe', 2, 10),
(33, 10, 32, '2026-05-14 03:14:18', 'confirmé', 20, 'stripe', 2, 0),
(34, 10, 32, '2026-05-16 04:01:13', 'confirmé', 16.5, 'stripe', 2, 0);

-- --------------------------------------------------------

--
-- Structure de la table `courses`
--

CREATE TABLE `courses` (
  `id_course` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_parcours` int(11) DEFAULT NULL,
  `distance_parcourue` float DEFAULT NULL,
  `temps_realise` float DEFAULT NULL,
  `vitesse_moyenne` float DEFAULT NULL,
  `nombre_pas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(14, 10, 5),
(17, 10, 47);

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
  `statut_paiement` varchar(20) DEFAULT 'unpaid',
  `email_24h_sent` tinyint(4) DEFAULT 0,
  `email_1h_sent` tinyint(4) DEFAULT 0,
  `statut_course` varchar(50) DEFAULT 'inscrit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscription`
--

INSERT INTO `inscription` (`id_inscription`, `nb_personnes`, `mode_de_paiement`, `date_inscription`, `date_paiement`, `id_user`, `id_parcours`, `statut_paiement`, `email_24h_sent`, `email_1h_sent`, `statut_course`) VALUES
(189, 1, 'cash', '2026-05-16 04:16:16', '2026-05-16 04:16:16', 10, 48, 'unpaid', 0, 0, 'inscrit');

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
(30, 32, 45, 5, 5.4),
(31, 33, 47, 4, 5),
(32, 34, 50, 1, 3),
(33, 34, 52, 1, 2.5),
(34, 34, 48, 1, 5),
(35, 34, 45, 1, 6);

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
(40, 'Marathon de la Côte', 'images/uploads/1777899026_69f89612b6b92.png', 'Organisateur Demo', 'Nabeul', '2026-05-16', 99, 5);

-- --------------------------------------------------------

--
-- Structure de la table `objectif`
--

CREATE TABLE `objectif` (
  `id_objectif` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type_objectif` varchar(50) NOT NULL,
  `target_value` int(11) NOT NULL,
  `recompense` varchar(255) NOT NULL,
  `description_recompense` text DEFAULT NULL,
  `etat` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `objectif`
--

INSERT INTO `objectif` (`id_objectif`, `titre`, `description`, `type_objectif`, `target_value`, `recompense`, `description_recompense`, `etat`) VALUES
(1, 'Débutant des Marathons', 'Participez à 5 marathons pour prouver votre engagement sportif !', 'marathons', 5, 'Inscription gratuite', 'L\'inscription à votre prochain marathon sera totalement offerte.', 1),
(2, 'Acheteur Fidèle', 'Passez au moins 5 commandes sur notre boutique BarchaThon.', 'commandes', 5, 'Remise de 10%', 'Bénéficiez de 10% de remise sur votre prochaine commande de produits ou équipements.', 1),
(3, 'Fidélité Quotidienne', 'Connectez-vous à la plateforme 15 jours de suite.', 'logins', 15, 'Survêtement Sportif', 'Gagnez un survêtement sportif officiel BarchaThon !', 1),
(4, 'Champion des Marathons', 'L\'objectif ultime : participer à 10 marathons !', 'marathons', 10, 'Pack Matériel Sportif', 'Recevez un pack VIP contenant des équipements de sport professionnels.', 1),
(5, 'Super Acheteur', 'Atteignez le palier des 10 commandes sur la plateforme.', 'commandes', 10, 'Bon d\'achat de 50 TND', 'Bénéficiez d\'un bon d\'achat de 50 TND valable sur tous les produits des stands lors de votre prochain marathon.', 1);

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
(50, 'Parcours Corniche Run', 'Corniche de Nabeul', 'Hammamet Yasmine', 9.79, 'facile', 40, '13:05:00');

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
(45, 32, 'chocolat noir', 'nutrition', 6.00, 36, 1, 'prod_69ff574e70cb1.jpg'),
(46, 32, 'banane', 'nutrition', 6.00, 0, 0, 'prod_69ff57677d33f.jpg'),
(47, 32, 'monster', 'boisant', 5.00, 17, 1, 'prod_69ff57837e6ed.jpg'),
(48, 32, 'jus fraise', 'boisson', 5.00, 99, 1, 'prod_6a07323deedcd.jpg'),
(49, 32, 'jus orange', 'boisson', 5.00, 100, 1, 'prod_6a07325337583.jpg'),
(50, 32, 'kiwi', 'fruit', 3.00, 19, 1, 'prod_6a0732657d97c.jpg'),
(51, 32, 'croissant', 'nutrition', 2.50, 20, 1, 'prod_6a0732802b880.jpg'),
(52, 32, 'expresso', 'coffee', 2.50, 99, 1, 'prod_6a0732a490bd3.jpg'),
(53, 32, 'coffee latee', 'coffee', 4.00, 100, 1, 'prod_6a0732c87ca0a.jpg'),
(54, 32, 'jus banane', 'boisson', 8.00, 100, 1, 'prod_6a0732e51a48e.jpg');

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
(32, 'ldd', '5', 'ddddddd', 48);

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
  `last_logout_at` datetime DEFAULT NULL,
  `nbre_commande` int(11) NOT NULL DEFAULT 0,
  `nbre_inscription` int(11) NOT NULL DEFAULT 0,
  `last_login_date` date DEFAULT NULL,
  `consecutive_logins` int(11) DEFAULT 0,
  `pending_discount` int(11) DEFAULT 0,
  `xp` int(11) DEFAULT 0,
  `solde_achat` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `nom_complet`, `nom_user`, `mot_de_passe`, `age`, `poids`, `taille`, `email`, `pays`, `ville`, `tel`, `occupation`, `profile_picture`, `role`, `status`, `last_active_at`, `sexe`, `verified`, `verification_token`, `face_descriptor`, `google_id`, `reset_token`, `reset_token_expires`, `last_logout_at`, `nbre_commande`, `nbre_inscription`, `last_login_date`, `consecutive_logins`, `pending_discount`, `xp`, `solde_achat`) VALUES
(1, 'Administrateur', 'admin', '123456', NULL, NULL, NULL, 'admin@barchathon.tn', NULL, NULL, NULL, NULL, NULL, 'admin', 'active', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 0, 0, 0),
(2, 'Organisateur Demo', 'organisateur', '$2y$10$OTWqaCplEDuU4T6oE2.Bgu9SJnb6OdeHmGbzwtPXcGvmUKGg1DFPi', NULL, NULL, NULL, 'orga@barchathon.tn', NULL, NULL, NULL, NULL, NULL, 'organisateur', 'active', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-05-16', 3, 0, 0, 0),
(3, 'Participant Demo', 'participant', '$2y$10$m/QCqoT/.uOK3cwuCOjC0uRuVPLDHqedWbYQg.0ESc1jgDYclhJR.', 50, 80, 180, 'souuuheee@gmail.com', NULL, NULL, NULL, NULL, NULL, 'participant', 'active', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-05-15', 1, 0, 0, 0),
(4, 'Anas', 'Touil', '$2y$10$2RY.NZKr4QnnGDmaG5hpH.LN9BTKByJfv0QNmh.RZ60JYAumMQU5a', 50, 50, 180, 'Anas.Touil@esprit.tn', 'Tunisia', 'Kairouan', '28306227', NULL, 'pp_69ecea58040fe5.13698447.webp', 'participant', 'active', '2026-05-04 15:06:23', 'homme', 0, '0531299e349b484507f78ec4b49093cccaa316140acba16a13cc5ae030656aff', NULL, NULL, '6252c440bf77242dd3b448b4aac9a290757d0eada68f3e325d8ba30bd5652ddb', '2026-04-24 22:01:51', '2026-05-04 14:00:59', 0, 0, NULL, 0, 0, 0, 0),
(6, 'BarchaThon', 'barchathon', '$2y$10$yKoWPjtL1dGBuHLOAVht6.cHvh19EcDjmbBmUjl3wBDi7fweROrqq', NULL, NULL, NULL, 'barchathon@gmail.com', NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocJHvxaJHMMh-ZhqdXEa9Bva7eNes77tt7mU6bDpqU8my5vliA=s96-c', 'participant', 'active', NULL, NULL, 1, NULL, NULL, '104889734528514256034', NULL, NULL, NULL, 0, 0, NULL, 0, 0, 0, 0),
(8, 'eee', 'eee', '$2y$10$mPxukPubG0HKxebXCOLHlOO2mFsUESyO./4IbwkcRcJZkCO745IeC', 20, 50, 170, 'touilanas00@gmail.com', 'Tunisia', 'Kairouan', '28306227', NULL, 'pp_69ef6083e0fdc1.87649496.webp', 'admin', 'active', '2026-05-04 15:04:56', 'homme', 1, NULL, '[-0.11707562208175659,0.07686099410057068,0.03598225861787796,-0.059945330023765564,-0.0576687715947628,-0.05551685392856598,0.022898292168974876,-0.10492634773254395,0.13207502663135529,-0.06920177489519119,0.31058526039123535,-0.06346745789051056,-0.20420685410499573,0.025148022919893265,-0.015554527752101421,0.16296333074569702,-0.21611127257347107,-0.07141030579805374,-0.1392584890127182,-0.05579861253499985,0.09314735978841782,0.05220949649810791,-0.08126748353242874,0.09251997619867325,-0.18360662460327148,-0.26499536633491516,-0.08404802531003952,-0.06395968794822693,0.015562111511826515,-0.043044064193964005,0.00002606001180538442,0.024065392091870308,-0.1223096251487732,-0.04887368902564049,0.07169043272733688,0.04771106690168381,-0.010680371895432472,-0.030258264392614365,0.14668428897857666,-0.03373243659734726,-0.20358559489250183,0.09192854166030884,0.14703933894634247,0.26937517523765564,0.1929420381784439,0.12908846139907837,0.01691819541156292,-0.02741529792547226,0.059564534574747086,-0.24464914202690125,0.0597320981323719,0.06313689053058624,0.20632395148277283,0.03712041303515434,0.09115691483020782,-0.21600893139839172,-0.038351912051439285,0.15272225439548492,-0.16788937151432037,0.10920592397451401,0.10304819047451019,-0.11551257222890854,0.053360238671302795,-0.04433484002947807,0.20586548745632172,0.07035011798143387,-0.16819970309734344,-0.03213374316692352,0.16301415860652924,-0.13880525529384613,-0.07469744235277176,0.00037771163624711335,-0.11864971369504929,-0.22324834764003754,-0.3186737298965454,0.042445093393325806,0.44277724623680115,0.1550922989845276,-0.17816688120365143,0.03135990351438522,0.028557883575558662,0.06832193583250046,0.09774437546730042,0.04753177613019943,-0.09575550258159637,-0.07238412648439407,-0.09901831299066544,0.03088715299963951,0.2240755707025528,0.02586377039551735,-0.0058655948378145695,0.24282656610012054,-0.042721524834632874,-0.046774256974458694,-0.0060861920937895775,0.009601480327546597,-0.07536105811595917,-0.01812046952545643,-0.040323592722415924,0.044576242566108704,-0.0004355185083113611,-0.08269504457712173,0.05258813127875328,0.12868012487888336,-0.20511722564697266,0.11873748898506165,-0.04482165351510048,0.02635609731078148,-0.02196710743010044,0.12492652237415314,-0.09004215151071548,-0.0006383003783412278,0.14271491765975952,-0.24737292528152466,0.17755812406539917,0.19638948142528534,0.005071531515568495,0.12749172747135162,0.05294708535075188,0.04635413736104965,0.03362489864230156,0.03728245943784714,-0.20493033528327942,-0.08721716701984406,0.06642085313796997,-0.11461347341537476,0.17440195381641388,0.0764831081032753]', '117094415717229109263', NULL, NULL, '2026-05-04 15:02:35', 0, 0, '2026-05-16', 3, 0, 0, 0),
(9, 'Ywk Ert', 'ywkert', '$2y$10$/Ua/1ol1Po.tkPjIHcx7TONhg.x3Wj.zdB1aps1XbZFYBej.rGMNa', NULL, NULL, NULL, 'ywkert@gmail.com', NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIe1Uz0UahDXpwMIbTzP6l3U85C1juA85FL9iXgpuCg3jcb1g=s96-c', 'participant', 'active', NULL, NULL, 1, NULL, NULL, '100060275676340533419', NULL, NULL, NULL, 0, 0, NULL, 0, 0, 0, 0),
(10, 'MOKH', 'mokh', '$2y$10$VExRAiIjrNRDMVHAc8LUf.ZpokMbQzKmUNsKx4MPwmuML0tuTvnv2', 25, 70, 175, 'sasougmati@gmail.com', 'Tunisia', 'Tunisie', '98200136', 'Etudiant', NULL, 'participant', 'active', NULL, 'homme', 0, 'ab5ad19536a46abd863c0783fc1c17a44c565b0cbcce4f629245beea77239b23', NULL, NULL, NULL, NULL, NULL, 3, 1, '2026-05-16', 3, 0, 310, 33.5);

-- --------------------------------------------------------

--
-- Structure de la table `user_objectif_claim`
--

CREATE TABLE `user_objectif_claim` (
  `id_claim` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_objectif` int(11) NOT NULL,
  `date_claim` datetime DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT 'reclamé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Index pour la table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id_course`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_parcours` (`id_parcours`);

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
-- Index pour la table `objectif`
--
ALTER TABLE `objectif`
  ADD PRIMARY KEY (`id_objectif`);

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
-- Index pour la table `user_objectif_claim`
--
ALTER TABLE `user_objectif_claim`
  ADD PRIMARY KEY (`id_claim`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_objectif` (`id_objectif`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `idcommande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `courses`
--
ALTER TABLE `courses`
  MODIFY `id_course` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `dossard`
--
ALTER TABLE `dossard`
  MODIFY `id_dossard` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224;

--
-- AUTO_INCREMENT pour la table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id_favorite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT pour la table `lignecommande`
--
ALTER TABLE `lignecommande`
  MODIFY `idligne` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `marathon`
--
ALTER TABLE `marathon`
  MODIFY `id_marathon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pour la table `objectif`
--
ALTER TABLE `objectif`
  MODIFY `id_objectif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `parcours`
--
ALTER TABLE `parcours`
  MODIFY `id_parcours` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `ID_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

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
  MODIFY `ID_stand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `user_objectif_claim`
--
ALTER TABLE `user_objectif_claim`
  MODIFY `id_claim` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`id_parcours`) REFERENCES `parcours` (`id_parcours`);

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

--
-- Contraintes pour la table `user_objectif_claim`
--
ALTER TABLE `user_objectif_claim`
  ADD CONSTRAINT `user_objectif_claim_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_objectif_claim_ibfk_2` FOREIGN KEY (`id_objectif`) REFERENCES `objectif` (`id_objectif`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
