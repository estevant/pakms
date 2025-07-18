-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 17 juil. 2025 à 22:06
-- Version du serveur : 5.7.24
-- Version de PHP : 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pa2`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_alerts`
--

CREATE TABLE `admin_alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text,
  `mot_incrimine` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `admin_alerts`
--

INSERT INTO `admin_alerts` (`id`, `user_id`, `description`, `mot_incrimine`, `created_at`) VALUES
(1, 17, 'piscine de 60 m2. on peut s\'amuser dans ma piscine et vous nude', 'nude', '2025-07-13 23:51:23');

-- --------------------------------------------------------

--
-- Structure de la table `box_assignments`
--

CREATE TABLE `box_assignments` (
  `id` int(11) NOT NULL,
  `box_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `status` enum('Déposé','Retiré','Réservé') NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `box_assignments`
--

INSERT INTO `box_assignments` (`id`, `box_id`, `request_id`, `status`, `datetime`) VALUES
(1, 21, 1, 'Réservé', '2025-06-18 10:15:00'),
(2, 22, 2, 'Déposé', '2025-06-19 14:30:00'),
(3, 23, 3, 'Retiré', '2025-06-19 16:45:00'),
(4, 24, 4, 'Réservé', '2025-06-19 09:00:00'),
(5, 25, 5, 'Déposé', '2026-07-15 11:20:00'),
(6, 26, 6, 'Retiré', '2025-06-19 17:10:00'),
(7, 27, 7, 'Déposé', '2025-06-19 12:00:00'),
(8, 28, 8, 'Réservé', '2025-07-06 08:45:00'),
(9, 29, 9, 'Déposé', '2025-07-06 15:30:00'),
(10, 30, 11, 'Retiré', '2025-07-08 09:15:00'),
(11, 31, 12, 'Déposé', '2025-07-08 16:00:00'),
(13, 23, 51, 'Réservé', '2025-07-16 19:51:18'),
(14, 41, 54, 'Réservé', '2025-07-17 16:06:28');

-- --------------------------------------------------------

--
-- Structure de la table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','active','rejected','expired','future') NOT NULL DEFAULT 'pending',
  `terms` text,
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `contracts`
--

INSERT INTO `contracts` (`contract_id`, `user_id`, `start_date`, `end_date`, `status`, `terms`, `pdf_path`) VALUES
(1, 14, '2024-01-15', '2025-01-14', 'active', 'Contrat de service standard', 'contracts/contrat_14.pdf'),
(2, 15, '2024-02-01', '2024-08-01', 'expired', 'Accord temporaire', 'contracts/contrat_15.pdf'),
(3, 16, '2024-03-10', '2024-09-10', 'rejected', 'Demande de contrat refusée', 'contracts/contrat_16.pdf'),
(4, 17, '2024-04-05', '2025-04-04', 'rejected', 'En attente de signature', 'contracts/contrat_17.pdf'),
(5, 18, '2024-05-20', '2024-11-19', 'future', 'Contrat à venir', 'contracts/contrat_18.pdf'),
(6, 19, '2024-06-01', '2025-06-01', 'active', 'Renouvellement annuel', 'contracts/contrat_19.pdf'),
(7, 20, '2024-07-15', '2025-07-14', 'active', 'Contrat VIP', 'contracts/contrat_20.pdf'),
(9, 22, '2024-09-10', '2025-03-09', 'active', 'En cours d\'examen', 'contracts/contrat_22.pdf'),
(10, 23, '2024-10-05', '2025-10-04', 'future', 'Contrat futur', 'contracts/contrat_23.pdf'),
(11, 24, '2024-11-20', '2025-11-19', 'rejected', 'Conditions générales', 'contracts/contrat_24.pdf'),
(12, 25, '2024-12-01', '2025-12-01', 'expired', 'Accord planifié', NULL),
(13, 26, '2025-01-10', '2026-01-09', 'future', 'Contrat en attente', NULL),
(14, 27, '2025-02-14', '2026-02-13', 'active', 'Contrat actif', NULL),
(15, 28, '2025-03-18', '2026-03-17', 'active', 'Validation en cours', NULL),
(16, 29, '2025-04-22', '2026-04-21', 'future', 'Accord futur', NULL),
(17, 30, '2025-05-26', '2026-05-25', 'active', 'Contrat premium', NULL),
(19, 32, '2025-07-10', '2026-07-09', 'future', 'Contrat exclusif', NULL),
(20, 49, '2025-07-17', '2026-07-17', 'active', NULL, NULL),
(21, 50, '2025-07-17', '2025-07-18', 'pending', NULL, NULL),
(22, 51, '2025-07-17', '2025-07-17', 'pending', NULL, NULL),
(25, 54, '2025-07-17', '2025-09-17', 'pending', NULL, NULL),
(26, 55, '2025-07-17', '2025-08-18', 'pending', 'TEST', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `deliveryassignment`
--

CREATE TABLE `deliveryassignment` (
  `assignment_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `deliverer_id` int(11) NOT NULL,
  `pickup_address` varchar(255) DEFAULT NULL,
  `pickup_postal_code` varchar(10) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_postal_code` varchar(10) DEFAULT NULL,
  `notes` text,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `status` enum('En attente','Acceptée','En cours','Livrée','Annulée') DEFAULT 'En attente',
  `validation_code` varchar(10) DEFAULT NULL,
  `step` enum('Acceptée','Pick-up fait','En transit','Box dépôt','Box retrait','Livrée','Annulée') DEFAULT 'Acceptée',
  `handoff_status` enum('Aucun','En attente','Accepté','Refusé') DEFAULT 'Aucun',
  `current_drop_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `deliveryassignment`
--

INSERT INTO `deliveryassignment` (`assignment_id`, `request_id`, `deliverer_id`, `pickup_address`, `pickup_postal_code`, `delivery_address`, `delivery_postal_code`, `notes`, `start_datetime`, `end_datetime`, `status`, `validation_code`, `step`, `handoff_status`, `current_drop_location`) VALUES
(1, 8, 12, 'Villejuif', '94800', 'Chelles', '77500', NULL, '2025-07-06 13:50:51', '2025-07-06 15:33:51', 'Livrée', NULL, 'Acceptée', 'Aucun', NULL),
(2, 9, 12, 'Villejuif', '94800', 'Chelles', '77500', NULL, '2025-07-06 15:36:49', '2025-07-06 16:11:59', 'Livrée', NULL, 'Acceptée', 'Aucun', NULL),
(3, 6, 12, 'Villejuif', '94800', 'Chelles', '77500', NULL, '2025-07-08 12:42:28', NULL, 'Acceptée', NULL, 'Acceptée', 'Aucun', NULL),
(4, 15, 26, '12 avenue Victor Hugo', '75016', '5 quai Victor Augagneur', '69003', 'Fragile', '2025-07-20 09:00:00', '2025-07-20 12:00:00', 'Acceptée', 'VAL001', 'En transit', 'Aucun', NULL),
(5, 16, 27, '3 rue de la République', '13001', '20 allées Jean Jaurès', '31000', 'Garder au sec', '2025-07-22 14:00:00', NULL, 'En attente', 'VAL002', 'Acceptée', 'Aucun', NULL),
(6, 17, 28, '1 place du Général', '59000', '10 cours Intendance', '33000', 'Manipuler avec soin', '2025-07-25 08:30:00', '2025-07-25 13:30:00', 'Livrée', 'VAL003', 'Livrée', 'Aucun', NULL),
(7, 51, 13, 'Chelles', '77500', 'Paris', '75012', NULL, '2025-07-16 20:19:55', '2025-07-16 20:20:34', 'Livrée', NULL, 'Acceptée', 'Aucun', NULL),
(8, 52, 44, 'Chelles', '77500', 'Paris', '75013', NULL, '2025-07-16 21:11:13', NULL, 'Acceptée', NULL, 'Acceptée', 'Accepté', 'BOX #64'),
(10, 14, 44, 'Meaux', '77100', 'Chelles', '77500', NULL, '2025-07-17 18:46:02', NULL, 'Acceptée', NULL, 'Acceptée', 'Aucun', NULL),
(11, 57, 44, 'Lille', '59800', 'Paris', '75015', NULL, '2025-07-17 18:51:56', NULL, 'Acceptée', NULL, 'Acceptée', 'Aucun', NULL),
(13, 56, 44, 'Coulommiers', '77120', 'Paris', '75012', NULL, '2025-07-17 21:46:58', NULL, 'Acceptée', NULL, 'Acceptée', 'Aucun', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `deliveryroutes`
--

CREATE TABLE `deliveryroutes` (
  `route_id` int(11) NOT NULL,
  `deliverer_id` int(11) NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `departure_postal_code` varchar(10) DEFAULT NULL,
  `destination_city` varchar(100) NOT NULL,
  `destination_postal_code` varchar(10) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `heure_depart` time DEFAULT NULL,
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `deliveryroutes`
--

INSERT INTO `deliveryroutes` (`route_id`, `deliverer_id`, `departure_city`, `departure_postal_code`, `destination_city`, `destination_postal_code`, `departure_date`, `heure_depart`, `notes`, `created_at`) VALUES
(1, 13, 'Chelles', '77500', 'Paris', '75012', '2025-07-19', '08:00:00', NULL, '2025-07-16 21:58:26');

-- --------------------------------------------------------

--
-- Structure de la table `deliveryroutewaypoints`
--

CREATE TABLE `deliveryroutewaypoints` (
  `waypoint_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `delivery_handoffs`
--

CREATE TABLE `delivery_handoffs` (
  `handoff_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `proposer_id` int(11) NOT NULL,
  `acceptor_id` int(11) DEFAULT NULL,
  `status` enum('En attente','Accepté','Refusé') NOT NULL DEFAULT 'En attente',
  `type` enum('BOX','ADRESSE') NOT NULL,
  `box_id` int(11) DEFAULT NULL,
  `new_address` varchar(255) DEFAULT NULL,
  `new_lat` decimal(10,7) DEFAULT NULL,
  `new_lon` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `delivery_handoffs`
--

INSERT INTO `delivery_handoffs` (`handoff_id`, `assignment_id`, `proposer_id`, `acceptor_id`, `status`, `type`, `box_id`, `new_address`, `new_lat`, `new_lon`, `created_at`, `accepted_at`) VALUES
(1, 1, 26, 27, 'En attente', 'BOX', 2, NULL, NULL, NULL, '2025-07-16 10:59:09', NULL),
(2, 2, 27, NULL, 'En attente', 'ADRESSE', NULL, '22 rue Victor Hugo', '48.8560000', '2.3000000', '2025-07-16 10:59:09', NULL),
(3, 3, 28, 26, 'Accepté', 'BOX', 4, NULL, NULL, NULL, '2025-07-16 10:59:09', NULL),
(4, 8, 44, 13, 'Accepté', 'BOX', 23, NULL, NULL, NULL, '2025-07-16 21:14:31', '2025-07-16 19:14:34'),
(5, 8, 44, 13, 'Accepté', 'BOX', 42, NULL, NULL, NULL, '2025-07-16 21:18:32', '2025-07-16 19:18:40'),
(6, 8, 44, 13, 'Accepté', 'BOX', 42, NULL, NULL, NULL, '2025-07-16 21:28:33', '2025-07-16 19:28:36'),
(7, 8, 44, 13, 'Accepté', 'BOX', 55, NULL, NULL, NULL, '2025-07-16 21:46:36', '2025-07-16 19:46:38'),
(8, 8, 44, 13, 'Accepté', 'BOX', 64, NULL, NULL, NULL, '2025-07-16 21:51:01', '2025-07-16 19:51:03');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `issue_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `invoice`
--

INSERT INTO `invoice` (`invoice_id`, `user_id`, `invoice_number`, `issue_date`, `total_amount`, `payment_id`, `pdf_path`) VALUES
(1, 13, 'INV-20250717-0057', '2025-07-17 19:54:09', '1000.00', 'pi_3RlxsY2ezKHvQm2M0lX3cioi', 'invoices/INV-20250717-0057.pdf'),
(3, 13, 'INV-20250717-0057', '2025-07-17 20:00:18', '1000.00', 'pi_3Rlx2m2ezKHvQm2M0NE9CnXq', 'invoices/INV-20250717-0057.pdf'),
(4, 49, 'INV-20250717-0061', '2025-07-17 20:03:41', '595.00', 'pi_3Rly1n2ezKHvQm2M0gApzbB4', 'invoices/INV-20250717-0061.pdf'),
(5, 13, 'INV-20250717-0057', '2025-07-17 20:04:43', '1000.00', 'pi_3Rly2m2ezKHvQm2M1KGxUFdM', 'invoices/INV-20250717-0057.pdf'),
(6, 13, 'INV-20250717-0057', '2025-07-17 20:06:24', '1000.00', 'pi_3Rlx7x2ezKHvQm2M0cqVec3v', 'invoices/INV-20250717-0057.pdf'),
(7, 13, 'INV-20250717-0057', '2025-07-17 20:08:48', '1000.00', 'pi_3RlxBY2ezKHvQm2M1YihpLFZ', 'invoices/INV-20250717-0057.pdf'),
(8, 13, 'INV-20250717-0057', '2025-07-17 20:09:37', '1000.00', 'pi_3Rly7W2ezKHvQm2M0lyzPq0O', 'invoices/INV-20250717-0057.pdf'),
(9, 13, 'INV-SRV-20250717-3008', '2025-07-17 20:30:05', '750.00', 'pi_3RlyRI2ezKHvQm2M026ls27M', 'invoices/INV-SRV-20250717-3008.pdf'),
(10, 13, 'INV-20250717-0057', '2025-07-17 20:30:20', '1000.00', 'pi_3RlxWe2ezKHvQm2M0TUgFo0O', 'invoices/INV-20250717-0057.pdf'),
(11, 49, 'INV-20250717-0058', '2025-07-17 20:34:18', '18736.00', 'pi_3RlxZs2ezKHvQm2M0IYfPU6f', 'invoices/INV-20250717-0058.pdf'),
(12, 13, 'INV-20250717-0057', '2025-07-17 20:44:23', '1000.00', 'pi_3Rlxj02ezKHvQm2M121N3Mw0', 'invoices/INV-20250717-0057.pdf'),
(13, 13, 'INV-20250717-0057', '2025-07-17 20:47:50', '1000.00', 'pi_3RlxmW2ezKHvQm2M01XIhZLt', 'invoices/INV-20250717-0057.pdf'),
(14, 13, 'INV-SRV-20250717-3012', '2025-07-17 20:48:59', '1998.00', 'pi_3Rlyjc2ezKHvQm2M03ba0ODU', 'invoices/INV-SRV-20250717-3012.pdf'),
(15, 13, 'INV-20250717-0057', '2025-07-17 20:51:35', '1000.00', 'pi_3Rlxpe2ezKHvQm2M0eDwVtwK', 'invoices/INV-20250717-0057.pdf'),
(16, 49, 'INV-20250717-0059', '2025-07-17 20:56:00', '595.00', 'pi_3RlxvI2ezKHvQm2M0C85oXUG', 'invoices/INV-20250717-0059.pdf'),
(17, 49, 'INV-20250717-0060', '2025-07-17 21:01:52', '646.00', 'pi_3Rlxza2ezKHvQm2M0vJPbU5Y', 'invoices/INV-20250717-0060.pdf'),
(18, 13, 'INV-SRV-20250717-3013', '2025-07-17 21:14:49', '3996.00', 'pi_3Rlz8b2ezKHvQm2M0e9DHOUz', 'invoices/INV-SRV-20250717-3013.pdf'),
(19, 48, 'INV-SRV-PROV-20250717-3013', '2025-07-17 21:14:49', '3996.00', 'pi_3Rlz8b2ezKHvQm2M0e9DHOUz', 'invoices/INV-SRV-PROV-20250717-3013.pdf'),
(20, 13, 'INV-SRV-20250717-3014', '2025-07-17 21:44:58', '798.00', 'pi_3Rlzbn2ezKHvQm2M1hi5PT2w', 'invoices/INV-SRV-20250717-3014.pdf'),
(21, 48, 'INV-SRV-PROV-20250717-3014', '2025-07-17 21:44:58', '798.00', 'pi_3Rlzbn2ezKHvQm2M1hi5PT2w', 'invoices/INV-SRV-PROV-20250717-3014.pdf'),
(22, 13, 'INV-20250717-0056', '2025-07-17 21:57:10', '1000.00', 'pi_3Rlznb2ezKHvQm2M1Y1qUwTq', 'invoices/INV-20250717-0056.pdf');

-- --------------------------------------------------------

--
-- Structure de la table `invoicedetail`
--

CREATE TABLE `invoicedetail` (
  `invoice_id` int(11) NOT NULL,
  `payment_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `justificatifs`
--

CREATE TABLE `justificatifs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('CNI','Permis','Carte Grise','Assurance','Autre') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `statut` enum('En attente','Validé','Refusé') NOT NULL DEFAULT 'En attente',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role_request_id` int(11) DEFAULT NULL COMMENT 'ID de la demande de rôle associée'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `justificatifs`
--

INSERT INTO `justificatifs` (`id`, `user_id`, `type`, `description`, `filename`, `statut`, `created_at`, `role_request_id`) VALUES
(1, 13, 'CNI', 'Justificatif pour demande de rôle Deliverer', 'role_justif_686d1b106c21f.jpg', 'Validé', '2025-07-08 15:20:16', 6),
(57, 14, 'CNI', 'Carte nationale d’identité recto-verso', 'justifs/cni_14.pdf', 'Refusé', '2025-07-16 14:44:35', NULL),
(58, 15, 'Permis', 'Permis de conduire B', 'justifs/permis_15.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(59, 16, 'Carte Grise', 'Carte grise véhicule personnel', 'justifs/carte_grise_16.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(60, 17, 'Assurance', 'Attestation d’assurance auto', 'justifs/assurance_17.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(61, 18, 'CNI', NULL, 'justifs/cni_18.jpg', 'Refusé', '2025-07-16 14:44:35', NULL),
(62, 19, 'Permis', 'Permis international', 'justifs/permis_intl_19.pdf', 'En attente', '2025-07-16 14:44:35', NULL),
(63, 20, 'Autre', 'Justificatif de domicile', 'justifs/justif_domicile_20.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(65, 22, 'CNI', 'Passeport français en cours de validité', 'justifs/passeport_22.pdf', 'En attente', '2025-07-16 14:44:35', NULL),
(66, 23, 'Permis', NULL, 'justifs/permis_23.pdf', 'Refusé', '2025-07-16 14:44:35', NULL),
(67, 24, 'Carte Grise', 'Certificat d’immatriculation moto', 'justifs/carte_grise_24.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(68, 25, 'Autre', 'Photo d’identité conforme', 'justifs/photo_id_25.jpg', 'En attente', '2025-07-16 14:44:35', NULL),
(69, 26, 'Assurance', 'Attestation responsabilité civile', 'justifs/rc_26.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(70, 27, 'CNI', NULL, 'justifs/cni_27.pdf', 'Refusé', '2025-07-16 14:44:35', NULL),
(71, 28, 'Permis', 'Permis poids lourd', 'justifs/pl_28.pdf', 'En attente', '2025-07-16 14:44:35', NULL),
(72, 29, 'Carte Grise', NULL, 'justifs/cg_29.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(73, 30, 'Autre', 'Certificat de formation sécurité', 'justifs/certif_secu_30.pdf', 'En attente', '2025-07-16 14:44:35', NULL),
(75, 32, 'CNI', 'Carte d’identité scannée', 'justifs/cni_32.pdf', 'Validé', '2025-07-16 14:44:35', NULL),
(77, 13, 'Carte Grise', NULL, 'justificatifs/13/justif_6878122d4711f_1752699437.png', 'En attente', '2025-07-16 20:57:17', NULL),
(78, 13, 'Carte Grise', NULL, 'justificatifs/13/justif_6878122e90383_1752699438.png', 'En attente', '2025-07-16 20:57:18', NULL),
(79, 13, 'CNI', NULL, 'justificatifs/13/justif_687812515c667_1752699473.png', 'En attente', '2025-07-16 20:57:53', NULL),
(80, 13, 'CNI', NULL, 'justificatifs/13/justif_6878125acbb42_1752699482.pdf', 'En attente', '2025-07-16 20:58:02', NULL),
(81, 13, 'CNI', NULL, 'justificatifs/13/justif_6878135b08cbe_1752699739.pdf', 'Refusé', '2025-07-16 21:02:19', NULL),
(82, 13, 'CNI', NULL, 'justificatifs/13/justif_687813aec8d76_1752699822.pdf', 'Validé', '2025-07-16 21:03:42', NULL),
(83, 44, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_6878154c8bcc0.pdf', 'En attente', '2025-07-16 23:10:36', NULL),
(87, 48, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_687824c80f843.pdf', 'En attente', '2025-07-17 00:16:40', NULL),
(88, 49, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_687906e8ca702.pdf', 'En attente', '2025-07-17 16:21:28', NULL),
(89, 50, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_68790eb57cacb.pdf', 'En attente', '2025-07-17 16:54:45', NULL),
(90, 51, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_687912ce65c5c.pdf', 'En attente', '2025-07-17 17:12:14', NULL),
(93, 54, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_68791a3d7ecdd.pdf', 'En attente', '2025-07-17 17:43:57', NULL),
(94, 55, 'CNI', 'Pièce d\'identité fournie à l\'inscription', 'identite_68791a78c939c.pdf', 'En attente', '2025-07-17 17:44:56', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `request_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assignment_id` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `request_id`, `content`, `sent_at`, `assignment_id`, `metadata`) VALUES
(1, NULL, NULL, 51, '💰 Offre proposée : 50.00 €', '2025-07-16 20:20:28', 7, '{\"negotiation_id\":1,\"proposed_price\":5000,\"sender_id\":13,\"receiver_id\":12}'),
(2, 13, 44, 52, 'salut', '2025-07-16 21:13:33', NULL, NULL),
(3, 44, 13, 52, 'bonjour', '2025-07-16 21:13:39', NULL, NULL),
(4, 44, 13, 52, 'ratio', '2025-07-16 21:13:41', NULL, NULL),
(5, 44, 13, 52, 'bonjour', '2025-07-16 21:13:59', NULL, NULL),
(6, NULL, NULL, 52, '💰 Offre proposée : 10.00 €', '2025-07-16 21:14:21', 8, '{\"negotiation_id\":2,\"proposed_price\":1000,\"sender_id\":13,\"receiver_id\":44}'),
(7, NULL, NULL, 52, 'Le livreur propose un dépôt intermédiaire', '2025-07-16 21:14:31', 8, '{\"handoff_id\":4,\"from\":\"Chelles\",\"to\":\"Box C (Paris)\",\"proposer_id\":44}'),
(8, NULL, NULL, 52, 'Le livreur propose un dépôt intermédiaire', '2025-07-16 21:18:32', 8, '{\"handoff_id\":5,\"from\":\"BOX #23\",\"to\":\"Box A (Paris)\",\"proposer_id\":44}'),
(9, NULL, NULL, 52, 'Le livreur propose un dépôt intermédiaire', '2025-07-16 21:28:33', 8, '{\"handoff_id\":6,\"from\":\"BOX #42\",\"to\":\"Box A (Paris)\",\"proposer_id\":44}'),
(10, NULL, NULL, 52, 'Livraison partielle validée, annonce #53 créée', '2025-07-16 21:28:36', 8, '{\"handoff_id\":6,\"from\":\"Chelles\",\"to\":\"Paris\"}'),
(11, NULL, NULL, 52, 'Le client a accepté le dépôt intermédiaire', '2025-07-16 21:28:36', 8, '{\"handoff_id\":6,\"from\":\"\\u2014\",\"to\":\"Box A (Paris)\"}'),
(12, NULL, NULL, 52, 'Le livreur propose un dépôt intermédiaire', '2025-07-16 21:46:36', 8, '{\"handoff_id\":7,\"from\":\"BOX #42\",\"to\":\"Box D (Lyon)\",\"proposer_id\":44}'),
(13, NULL, NULL, 52, ' Dépôt intermédiaire accepté - Division de la livraison en cours...', '2025-07-16 21:46:38', 8, '{\"handoff_id\":7,\"from\":\"\\u2014\",\"to\":\"Box D (Lyon)\"}'),
(14, NULL, NULL, 52, 'Le livreur propose un dépôt intermédiaire', '2025-07-16 21:51:01', 8, '{\"handoff_id\":8,\"from\":\"BOX #55\",\"to\":\"Box C3 (Marseille)\",\"proposer_id\":44}'),
(15, NULL, NULL, 52, ' Dépôt intermédiaire accepté - Division de la livraison en cours...', '2025-07-16 21:51:03', 8, '{\"handoff_id\":8,\"from\":\"BOX #55\",\"to\":\"Box C3 (Marseille)\"}'),
(17, NULL, NULL, 14, '💰 Offre proposée : 10.00 €', '2025-07-17 18:46:25', 10, '{\"negotiation_id\":4,\"proposed_price\":1000,\"sender_id\":13,\"receiver_id\":44}'),
(18, NULL, NULL, 57, '💰 Offre proposée : 10.00 €', '2025-07-17 18:53:37', 11, '{\"negotiation_id\":5,\"proposed_price\":1000,\"sender_id\":13,\"receiver_id\":44}'),
(19, NULL, NULL, 56, '💰 Offre proposée : 10.00 €', '2025-07-17 21:55:40', 13, '{\"negotiation_id\":6,\"proposed_price\":1000,\"sender_id\":13,\"receiver_id\":44}');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `negotiations`
--

CREATE TABLE `negotiations` (
  `negotiation_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `proposed_price` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `negotiations`
--

INSERT INTO `negotiations` (`negotiation_id`, `request_id`, `sender_id`, `receiver_id`, `proposed_price`, `status`, `created_at`) VALUES
(1, 51, 13, 12, 5000, 'pending', '2025-07-16 20:20:28'),
(2, 52, 13, 44, 1000, 'accepted', '2025-07-16 21:14:21'),
(3, 55, 13, 44, 3400, 'accepted', '2025-07-17 16:45:09'),
(4, 14, 13, 44, 1000, 'accepted', '2025-07-17 18:46:25'),
(5, 57, 13, 44, 1000, 'accepted', '2025-07-17 18:53:37'),
(6, 56, 13, 44, 1000, 'accepted', '2025-07-17 21:55:40');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `titre`, `contenu`, `is_read`, `created_at`) VALUES
(1, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Villejuif et Chelles.', 1, '2025-07-06 13:50:51'),
(2, 12, 'Livraison confirmée', 'Votre livraison #8 a été confirmée par le client.', 0, '2025-07-06 15:33:51'),
(3, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Villejuif et Chelles.', 1, '2025-07-06 15:36:49'),
(4, 12, 'Livraison confirmée', 'Votre livraison #9 a été confirmée par le client.', 0, '2025-07-06 16:11:59'),
(5, 11, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Villejuif et Chelles.', 0, '2025-07-08 12:42:28'),
(6, 12, 'Compte validé', 'Ton profil de livreur a été validé. Tu peux désormais effectuer des livraisons.', 0, '2025-07-16 17:57:26'),
(7, 14, '❌ Justificatif refusé', 'Votre justificatif de type « CNI » a été Refusé.', 0, '2025-07-16 19:10:52'),
(8, 17, '✅ Justificatif validé', 'Votre justificatif de type « Assurance » a été Validé.', 0, '2025-07-16 19:10:56'),
(9, 17, 'Contrat refusé', 'Votre contrat du 2024-04-05 au 2025-04-04 a été refusé.', 0, '2025-07-16 19:12:46'),
(10, 22, 'Contrat approuvé', 'Votre contrat du 2024-09-10 au 2025-03-09 a été approuvé.', 0, '2025-07-16 19:13:31'),
(11, 12, '⭐ Nouvel avis reçu', 'Vous avez reçu un avis 4/5 pour votre livraison.', 0, '2025-07-16 20:18:42'),
(12, 12, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Chelles et Paris.', 0, '2025-07-16 20:19:55'),
(13, 12, '📦  Suivi colis : En cours', 'Votre colis est passé à l’étape : En cours', 0, '2025-07-16 20:20:11'),
(14, 12, 'Livraison effectuée', 'Votre annonce de livraison entre Chelles et Paris a été marquée comme livrée.', 0, '2025-07-16 20:20:34'),
(15, 12, '📦  Suivi colis : Livré', 'Votre colis est passé à l’étape : Livré', 0, '2025-07-16 20:22:45'),
(16, 3, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(17, 9, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(18, 38, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(19, 39, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(20, 40, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(21, 41, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(22, 42, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(23, 43, 'Nouveau justificatif à valider', 'Un nouveau justificatif de type \'CNI\' a été soumis par Demo User.', 0, '2025-07-16 21:03:42'),
(24, 13, '✅ Justificatif validé', 'Votre justificatif de type « CNI » a été Validé.', 0, '2025-07-16 21:06:14'),
(25, 13, '❌ Justificatif refusé', 'Votre justificatif de type « CNI » a été Refusé.', 0, '2025-07-16 21:06:16'),
(26, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Chelles et Paris.', 0, '2025-07-16 21:11:13'),
(27, 44, '💬 Nouveau message', 'Vous avez reçu un message concernant votre annonce n°52.', 0, '2025-07-16 21:13:33'),
(28, 13, '💬 Nouveau message', 'Vous avez reçu un message concernant votre annonce n°52.', 0, '2025-07-16 21:13:39'),
(29, 13, '💬 Nouveau message', 'Vous avez reçu un message concernant votre annonce n°52.', 0, '2025-07-16 21:13:41'),
(30, 13, '💬 Nouveau message', 'Vous avez reçu un message concernant votre annonce n°52.', 0, '2025-07-16 21:13:59'),
(31, 24, 'Nouvelle annonce deuxième tronçon', 'Annonce #53 : livraison de Paris à Paris.', 0, '2025-07-16 21:28:36'),
(32, 13, 'Annonces correspondant à ton trajet', '10 annonces correspondent à ton trajet entre Chelles et Paris.', 0, '2025-07-16 21:58:26'),
(33, 28, 'Contrat approuvé', 'Votre contrat du 2025-03-18 au 2026-03-17 a été approuvé.', 0, '2025-07-17 14:36:51'),
(34, 49, 'Contrat approuvé', 'Votre contrat du 2025-07-17 au 2026-07-17 a été approuvé.', 0, '2025-07-17 14:36:57'),
(35, 13, 'Nouveau trajet disponible', 'Une nouvelle annonce correspond à ton trajet : de Coulommiers à Paris.', 0, '2025-07-17 16:44:24'),
(36, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Coulommiers et Paris.', 0, '2025-07-17 16:44:42'),
(37, 13, 'Livraison annulée', 'Le livreur en charge de votre annonce entre Coulommiers et Paris s\'est désisté.', 0, '2025-07-17 18:44:23'),
(38, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Meaux et Chelles.', 0, '2025-07-17 18:46:02'),
(39, 13, 'Nouveau trajet disponible', 'Une nouvelle annonce correspond à ton trajet : de Lille à Paris.', 0, '2025-07-17 18:51:35'),
(40, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Lille et Paris.', 0, '2025-07-17 18:51:56'),
(41, 13, 'Nouveau trajet disponible', 'Une nouvelle annonce correspond à ton trajet : de Villejuif à Paris.', 0, '2025-07-17 19:56:33'),
(42, 13, 'Nouveau trajet disponible', 'Une nouvelle annonce correspond à ton trajet : de Villejuif à Paris.', 0, '2025-07-17 20:00:48'),
(43, 13, 'Nouveau trajet disponible', 'Une nouvelle annonce correspond à ton trajet : de Villejuif à Paris.', 0, '2025-07-17 20:03:07'),
(44, 49, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Villejuif et Paris.', 0, '2025-07-17 21:46:19'),
(45, 13, 'Livraison acceptée', 'Un livreur a accepté votre annonce entre Coulommiers et Paris.', 0, '2025-07-17 21:46:58'),
(46, 49, 'Livraison annulée', 'Le livreur en charge de votre annonce entre Villejuif et Paris s\'est désisté.', 0, '2025-07-17 21:55:53');

-- --------------------------------------------------------

--
-- Structure de la table `objects`
--

CREATE TABLE `objects` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `quantite` int(11) NOT NULL DEFAULT '1',
  `poids` float DEFAULT NULL,
  `length` double(8,2) DEFAULT NULL,
  `width` double(8,2) DEFAULT NULL,
  `height` double(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `description` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `objects`
--

INSERT INTO `objects` (`id`, `request_id`, `nom`, `quantite`, `poids`, `length`, `width`, `height`, `dimensions`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'test', 1, 1, 1.00, 1.00, 1.00, '', '', '2025-06-18 21:48:44', '2025-06-18 22:08:42'),
(2, 2, 'test', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-06-19 12:00:50', '2025-06-19 12:00:50'),
(3, 4, 'test', 1, 1, 1.00, 1.00, 1.00, '', '', '2025-06-19 13:59:35', '2025-06-19 14:47:49'),
(4, 5, '1', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-06-19 14:57:28', '2025-06-19 14:57:28'),
(5, 6, '1', 1, 1, 1.00, 1.00, 1.00, '', '', '2025-06-19 15:04:17', '2025-06-19 15:20:46'),
(6, 8, 'testqrcode', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-06 15:50:09', '2025-07-06 15:50:09'),
(7, 9, 'testqrcode2', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-06 17:36:38', '2025-07-06 17:36:38'),
(9, 11, 'testphoto', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-08 15:21:08', '2025-07-08 15:21:08'),
(10, 12, 'testphoto', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-08 16:51:20', '2025-07-08 16:51:20'),
(12, 14, 'testphoto', 1, 1, 1.00, 1.00, 1.00, '', '', '2025-07-08 17:06:51', '2025-07-16 22:24:04'),
(13, 15, 'Enceinte bluetooth', 1, 1.2, 20.00, 10.00, 7.00, '20x10x7', '', '2025-07-16 12:58:34', '2025-07-16 12:58:34'),
(14, 16, 'Livre ancien', 2, 0.8, 25.00, 18.00, 4.00, '25x18x4', '', '2025-07-16 12:58:34', '2025-07-16 12:58:34'),
(15, 17, 'Vase en céramique', 1, 2.5, 30.00, 30.00, 15.00, '30x30x15', '', '2025-07-16 12:58:34', '2025-07-16 12:58:34'),
(16, 18, 'T-shirt (lot de 5)', 5, 1, 35.00, 25.00, 5.00, '35x25x5', '', '2025-07-16 12:58:34', '2025-07-16 12:58:34'),
(17, 19, 'Serveur NAS', 1, 7.5, 45.00, 20.00, 15.00, '45x20x15', '', '2025-07-16 12:58:34', '2025-07-16 12:58:34'),
(18, 15, '', 1, NULL, NULL, NULL, NULL, '', '', '2025-07-16 21:09:47', '2025-07-16 21:09:47'),
(20, 50, 'test', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-16 21:49:23', '2025-07-16 21:49:23'),
(21, 51, 'testbox', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-16 21:51:18', '2025-07-16 21:51:18'),
(22, 52, 'photo', 1, 1, 1.00, 1.00, 1.00, '', '', '2025-07-16 22:35:38', '2025-07-16 22:49:30'),
(23, 54, 'test', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-17 18:06:28', '2025-07-17 18:06:28'),
(24, 55, 'test', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-17 18:37:59', '2025-07-17 18:37:59'),
(25, 56, '1', 1, 1, 1.00, 11.00, 1.00, NULL, '', '2025-07-17 18:44:24', '2025-07-17 18:44:24'),
(26, 57, 'test', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-17 20:51:35', '2025-07-17 20:51:35'),
(27, 58, 'test', 5, 2, 2.00, 2.00, 2.00, NULL, '', '2025-07-17 21:34:21', '2025-07-17 21:34:21'),
(28, 59, 'TEST', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-17 21:56:33', '2025-07-17 21:56:33'),
(29, 60, 'TEST', 4, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-17 22:00:48', '2025-07-17 22:00:48'),
(30, 61, 'TEST', 1, 1, 1.00, 1.00, 1.00, NULL, '', '2025-07-17 22:03:07', '2025-07-17 22:03:07');

-- --------------------------------------------------------

--
-- Structure de la table `object_photo`
--

CREATE TABLE `object_photo` (
  `id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `chemin` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `object_photo`
--

INSERT INTO `object_photo` (`id`, `object_id`, `chemin`, `created_at`) VALUES
(2, 12, '68780b618eeba_1752697697.png', '2025-07-16 22:28:17'),
(5, 22, '687810bec4165_1752699070.png', '2025-07-16 22:51:10'),
(6, 26, '6879463789573_1752778295.png', '2025-07-17 20:51:35');

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `payment_id` varchar(255) NOT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `payer_id` int(11) NOT NULL,
  `payee_id` int(11) DEFAULT NULL,
  `payment_type` enum('Livraison','Service','Abonnement') DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('En attente','Payé','Remboursé') DEFAULT 'En attente',
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`payment_id`, `stripe_id`, `payer_id`, `payee_id`, `payment_type`, `related_id`, `amount`, `status`, `payment_date`) VALUES
('pi_3Rlwww2ezKHvQm2M0E19xqqd', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 19:54:44'),
('pi_3Rlx2m2ezKHvQm2M0NE9CnXq', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:00:18'),
('pi_3Rlx7x2ezKHvQm2M0cqVec3v', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:06:24'),
('pi_3RlxBY2ezKHvQm2M1YihpLFZ', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:08:48'),
('pi_3Rlxj02ezKHvQm2M121N3Mw0', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:44:23'),
('pi_3RlxmW2ezKHvQm2M01XIhZLt', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:47:50'),
('pi_3Rlxpe2ezKHvQm2M0eDwVtwK', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:51:35'),
('pi_3RlxsY2ezKHvQm2M0lX3cioi', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 19:54:09'),
('pi_3RlxvI2ezKHvQm2M0C85oXUG', NULL, 49, NULL, 'Livraison', NULL, '595.00', 'Payé', '2025-07-17 20:56:00'),
('pi_3RlxWe2ezKHvQm2M0TUgFo0O', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:30:20'),
('pi_3Rlxza2ezKHvQm2M0vJPbU5Y', NULL, 49, NULL, 'Livraison', NULL, '646.00', 'Payé', '2025-07-17 21:01:52'),
('pi_3RlxZs2ezKHvQm2M0IYfPU6f', NULL, 49, NULL, 'Livraison', NULL, '18736.00', 'Payé', '2025-07-17 20:34:18'),
('pi_3Rly1n2ezKHvQm2M0gApzbB4', NULL, 49, NULL, 'Livraison', NULL, '595.00', 'Payé', '2025-07-17 20:03:41'),
('pi_3Rly2m2ezKHvQm2M1KGxUFdM', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:04:43'),
('pi_3Rly7W2ezKHvQm2M0lyzPq0O', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 20:09:37'),
('pi_3Rlyjc2ezKHvQm2M03ba0ODU', NULL, 13, 48, 'Service', NULL, '1998.00', 'Payé', '2025-07-17 20:48:59'),
('pi_3RlyM12ezKHvQm2M16H7TeTv', NULL, 13, 24, 'Service', NULL, '750.00', 'Payé', '2025-07-17 20:24:36'),
('pi_3RlyRI2ezKHvQm2M026ls27M', NULL, 13, 24, 'Service', NULL, '750.00', 'Payé', '2025-07-17 20:30:04'),
('pi_3Rlz8b2ezKHvQm2M0e9DHOUz', NULL, 13, 48, 'Service', NULL, '3996.00', 'Payé', '2025-07-17 21:14:48'),
('pi_3Rlzbn2ezKHvQm2M1hi5PT2w', NULL, 13, 48, 'Service', NULL, '798.00', 'Payé', '2025-07-17 21:44:58'),
('pi_3Rlznb2ezKHvQm2M1Y1qUwTq', NULL, 13, 44, 'Livraison', NULL, '1000.00', 'Payé', '2025-07-17 21:57:10');

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `proposition_de_prestations`
--

CREATE TABLE `proposition_de_prestations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text,
  `justificatif_id` int(11) DEFAULT NULL,
  `statut` varchar(30) DEFAULT 'En attente',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `proposition_de_prestations`
--

INSERT INTO `proposition_de_prestations` (`id`, `user_id`, `nom`, `description`, `justificatif_id`, `statut`, `created_at`) VALUES
(2, 3, 'Aide ménagère', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(3, 3, 'Bricolage', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(4, 3, 'Coiffure Afro', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(5, 3, 'Cuisine Afro', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(6, 3, 'Garde d\'enfant à domicile', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(7, 3, 'Transport médicalisé', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(8, 3, 'Vétérinaire', NULL, NULL, 'Validé', '2025-06-29 14:27:17'),
(9, 3, 'Bricolage', 'iudhdcj', NULL, 'Refusé', '2025-07-09 19:25:23'),
(10, 3, 'Autre', 'vzbjkdjkdf', NULL, 'Refusé', '2025-07-10 08:41:58'),
(11, 17, 'Nettoyage de piscine', 'TEST', NULL, 'Validé', '2025-07-13 09:09:59'),
(12, 17, 'Coaching sportif', 'femme coach sportif', NULL, 'Refusé', '2025-07-13 09:25:05'),
(13, 17, 'Bricolage', 'test', NULL, 'Refusé', '2025-07-13 09:25:22'),
(14, 17, 'Garde d\'enfant à domicile', 'test', NULL, 'Refusé', '2025-07-13 09:25:53'),
(15, 18, 'Autre', 'je suis pédophile. INtéressez passer pv', NULL, 'En attente', '2025-07-13 19:26:56'),
(16, 19, 'Autre', 'Je suis pédophile', NULL, 'En attente', '2025-07-13 19:33:17'),
(17, 48, 'Livraison Standard', 'test', NULL, 'En attente', '2025-07-16 20:16:40'),
(18, 48, 'Livraison Express', 'test', NULL, 'En attente', '2025-07-16 20:21:20');

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `target_type` enum('annonce','user') NOT NULL,
  `target_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text,
  `status` enum('Ouvert','Résolu','Rejeté') NOT NULL DEFAULT 'Ouvert',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `reports`
--

INSERT INTO `reports` (`report_id`, `reporter_id`, `target_type`, `target_id`, `reason`, `description`, `status`, `created_at`) VALUES
(2, 15, 'user', 26, 'Comportement inapproprié', 'Le livreur a été impoli lors de la prise en charge', 'Ouvert', '2025-07-16 14:20:55'),
(3, 16, 'annonce', 16, 'Colis endommagé', 'La photo ne correspond pas à l’état réel de l’objet', 'Ouvert', '2025-07-16 14:20:55'),
(4, 17, 'user', 27, 'Retard répété', 'Le livreur a annulé plusieurs fois au dernier moment', 'Ouvert', '2025-07-16 14:20:55'),
(5, 18, 'annonce', 17, 'Prix non respecté', 'Le tarif facturé est supérieur à celui annoncé', 'Ouvert', '2025-07-16 14:20:55'),
(6, 19, 'user', 28, 'Publicité abusive', 'L’utilisateur envoie trop de messages non sollicités', 'Ouvert', '2025-07-16 14:20:55'),
(7, 20, 'annonce', 18, 'Informations manquantes', 'L’adresse de destination est incorrecte', 'Ouvert', '2025-07-16 14:20:55'),
(9, 22, 'annonce', 19, 'Annonce dupliquée', 'La même annonce apparaît plusieurs fois dans la liste', 'Ouvert', '2025-07-16 14:20:55'),
(10, 23, 'user', 30, 'Comportement suspect', 'Le prestataire a demandé un paiement en cash', 'Ouvert', '2025-07-16 14:20:55'),
(11, 24, 'user', 31, 'Non respect du contrat', 'Le prestataire n’a pas honoré les termes convenus', 'Ouvert', '2025-07-16 14:20:55'),
(12, 25, 'annonce', 15, 'Annonce expirée', 'L’annonce reste visible alors qu’elle a déjà eu lieu', 'Ouvert', '2025-07-16 14:20:55'),
(13, 26, 'user', 32, 'Spam', 'L’utilisateur envoie des liens publicitaires', 'Ouvert', '2025-07-16 14:20:55'),
(14, 27, 'annonce', 16, 'Date invalide', 'La date de départ est antérieure à la date de création', 'Ouvert', '2025-07-16 14:20:55'),
(15, 28, 'user', 33, 'Fausse identité', 'Le profil semble usurpé', 'Ouvert', '2025-07-16 14:20:55'),
(16, 13, 'user', 12, 'Harcèlement', 'maboule', 'Ouvert', '2025-07-16 22:19:31');

-- --------------------------------------------------------

--
-- Structure de la table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `departure_city` varchar(100) DEFAULT NULL,
  `departure_address` varchar(255) DEFAULT NULL,
  `departure_lat` decimal(10,7) DEFAULT NULL,
  `departure_lon` decimal(10,7) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `departure_code` varchar(10) DEFAULT NULL,
  `destination_city` varchar(100) DEFAULT NULL,
  `destination_address` varchar(255) DEFAULT NULL,
  `destination_lat` decimal(10,7) DEFAULT NULL,
  `destination_lon` decimal(10,7) DEFAULT NULL,
  `destination_code` varchar(10) DEFAULT NULL,
  `poids` double(8,2) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('merchant','client') NOT NULL DEFAULT 'client',
  `is_split` tinyint(1) DEFAULT '0',
  `parent_request_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `longueur` double(8,2) DEFAULT NULL,
  `largeur` double(8,2) DEFAULT NULL,
  `hauteur` double(8,2) DEFAULT NULL,
  `distance` double(8,2) DEFAULT NULL,
  `prix_cents` int(11) DEFAULT NULL,
  `prix_negocie_cents` int(11) DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `requests`
--

INSERT INTO `requests` (`request_id`, `departure_city`, `departure_address`, `departure_lat`, `departure_lon`, `departure_date`, `departure_code`, `destination_city`, `destination_address`, `destination_lat`, `destination_lon`, `destination_code`, `poids`, `user_id`, `type`, `is_split`, `parent_request_id`, `created_at`, `longueur`, `largeur`, `hauteur`, `distance`, `prix_cents`, `prix_negocie_cents`, `is_paid`) VALUES
(1, 'Paris', '242 Rue du Faubourg Saint-Antoine 75012 Paris', '48.8492000', '2.3896000', '2025-06-18', '75012', 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', '75012', NULL, 6, 'client', 0, NULL, '2025-06-18 21:48:44', NULL, NULL, NULL, 38.75, NULL, NULL, 0),
(2, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', '2025-06-19', '94800', 'Paris', 'Box A', '48.8461660', '2.3856570', '75', NULL, 11, 'client', 1, NULL, '2025-06-19 12:00:50', NULL, NULL, NULL, 9.88, NULL, NULL, 0),
(3, 'Paris', 'Box A', NULL, NULL, NULL, '75', 'Paris', '21 Rue Erard 75012 Paris', NULL, NULL, '75012', NULL, 11, 'client', 0, 2, '2025-06-19 10:07:49', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(4, 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', '2025-06-19', '75012', 'Nantes', 'Box Q', '47.2183710', '-1.5536210', '44', NULL, 11, 'client', 1, NULL, '2025-06-19 13:59:35', NULL, NULL, NULL, 1.19, NULL, 5000, 0),
(5, 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '2026-07-15', '77500', 'Paris', 'Rue de Charenton 75012 Paris', '48.8434440', '2.3849590', '75012', NULL, 11, 'client', 0, NULL, '2025-06-19 14:57:28', NULL, NULL, NULL, 33.78, NULL, NULL, 0),
(6, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', '2025-06-19', '94800', 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 11, 'client', 0, NULL, '2025-06-19 15:04:17', NULL, NULL, NULL, 38.75, NULL, NULL, 0),
(7, 'Nantes', 'Box Q', '47.2183710', '-1.5536210', '2025-06-19', '44', 'Chelles', '1 allée du charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 11, 'client', 0, 4, '2025-06-19 13:16:36', NULL, NULL, NULL, 1.19, NULL, NULL, 0),
(8, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', '2025-07-06', '94800', 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 13, 'client', 0, NULL, '2025-07-06 15:50:09', NULL, NULL, NULL, 38.75, NULL, NULL, 0),
(9, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', '2025-07-06', '94800', 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 13, 'client', 0, NULL, '2025-07-06 17:36:38', NULL, NULL, NULL, 38.75, NULL, NULL, 0),
(11, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', '2025-07-08', '94800', 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 13, 'client', 0, NULL, '2025-07-08 15:21:08', NULL, NULL, NULL, 38.75, NULL, NULL, 0),
(12, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', '2025-07-08', '94800', 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 12, 'client', 0, NULL, '2025-07-08 16:51:20', NULL, NULL, NULL, 38.75, NULL, NULL, 0),
(14, 'Meaux', '78 Rue Jean Jaures 77100 Meaux', '48.9609450', '2.8892860', '2025-07-10', '77100', 'Chelles', '1 allée du charron 77500 Chelles', '48.8879530', '2.6212880', '77500', NULL, 13, 'client', 0, NULL, '2025-07-08 17:06:51', NULL, NULL, NULL, 27.14, NULL, 1000, 0),
(15, 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', '2025-07-01', '75012', 'Bordeaux', '2 Rue Pelleport 33800 Bordeaux', '44.8237840', '-0.5583720', '33800', 10.50, 14, 'client', 0, NULL, '2025-07-16 12:47:53', 60.00, 40.00, 20.00, 460.00, 15000, 14000, 1),
(16, 'Lyon', '3 rue de Marseille', '45.7500000', '4.8500000', '2025-07-02', 'DPT002', 'Marseille', '1 place Castellane', '43.2910000', '5.3810000', 'DST002', 5.20, 15, 'client', 0, NULL, '2025-07-16 12:47:53', 50.00, 30.00, 15.00, 450.00, 12000, 11500, 0),
(17, 'Marseille', '20 boulevard Longchamp', '43.3050000', '5.3880000', '2025-07-03', 'DPT003', 'Nice', '14 promenade des Anglais', '43.6950000', '7.2650000', 'DST003', 8.00, 16, 'client', 0, NULL, '2025-07-16 12:47:53', 55.00, 35.00, 25.00, 320.00, 14000, 13500, 1),
(18, 'Toulouse', '8 rue d\'Alsace', '43.6040000', '1.4440000', '2025-07-04', 'DPT004', 'Bordeaux', '3 cours Victor Hugo', '44.8370000', '-0.5790000', 'DST004', 12.00, 17, 'client', 0, NULL, '2025-07-16 12:47:53', 70.00, 40.00, 30.00, 540.00, 18000, 17000, 0),
(19, 'Nice', '7 rue de Béthune', '50.6320000', '3.0610000', '2025-07-05', 'DPT005', 'Paris', '23 avenue Jean Jaurès', '45.7510000', '4.8410000', 'DST005', 3.50, 18, 'client', 0, NULL, '2025-07-16 12:47:53', 40.00, 25.00, 10.00, 930.00, 11000, 10500, 1),
(20, 'Bordeaux', '42 rue Kervégan', '47.2180000', '-1.5530000', '2025-07-06', 'DPT006', 'Lille', '7 rue de Béthune', '50.6320000', '3.0610000', 'DST006', 6.70, 19, 'client', 0, NULL, '2025-07-16 12:47:53', 45.00, 30.00, 18.00, 840.00, 13000, 12500, 0),
(21, 'Lille', '8 rue de Paris', '50.6370000', '3.0630000', '2025-07-07', 'DPT007', 'Nantes', '42 rue Kervégan', '47.2180000', '-1.5530000', 'DST007', 9.00, 20, 'client', 0, NULL, '2025-07-16 12:47:53', 60.00, 35.00, 22.00, 730.00, 16000, 15000, 1),
(23, 'Strasbourg', '5 rue du Général', '48.5830000', '7.7450000', '2025-07-09', 'DPT009', 'Marseille', '20 boulevard Longchamp', '43.3050000', '5.3880000', 'DST009', 7.10, 22, 'client', 0, NULL, '2025-07-16 12:47:53', 65.00, 40.00, 25.00, 920.00, 17000, 16500, 1),
(24, 'Montpellier', '12 quai de la Fontaine', '43.6110000', '3.8760000', '2025-07-10', 'DPT010', 'Bordeaux', '3 cours Victor Hugo', '44.8370000', '-0.5790000', 'DST010', 2.80, 23, 'client', 0, NULL, '2025-07-16 12:47:53', 35.00, 20.00, 10.00, 650.00, 10000, 9500, 0),
(25, 'Paris', '1 rue de Lyon', '48.8470000', '2.3740000', '2025-07-11', 'DPT011', 'Strasbourg', '5 rue du Général', '48.5830000', '7.7450000', 'DST011', 11.20, 24, 'client', 0, NULL, '2025-07-16 12:47:53', 75.00, 50.00, 30.00, 490.00, 19000, 18500, 1),
(26, 'Lyon', '14 place Bellecour', '45.7570000', '4.8320000', '2025-07-12', 'DPT012', 'Nice', '14 promenade des Anglais', '43.6950000', '7.2650000', 'DST012', 5.50, 25, 'client', 0, NULL, '2025-07-16 12:47:53', 55.00, 30.00, 20.00, 470.00, 13000, 12500, 0),
(27, 'Marseille', '8 rue Saint Ferreol', '43.3000000', '5.3800000', '2025-07-13', 'DPT013', 'Lille', '8 rue de Paris', '50.6370000', '3.0630000', 'DST013', 8.80, 26, 'client', 0, NULL, '2025-07-16 12:47:53', 60.00, 35.00, 25.00, 1020.00, 16000, 15500, 1),
(28, 'Toulouse', '5 rue Alsace Lorraine', '43.6040000', '1.4440000', '2025-07-14', 'DPT014', 'Montpellier', '12 quai de la Fontaine', '43.6110000', '3.8760000', 'DST014', 3.30, 27, 'client', 0, NULL, '2025-07-16 12:47:53', 45.00, 25.00, 15.00, 300.00, 11000, 10500, 0),
(29, 'Nice', '10 avenue Malausséna', '43.7000000', '7.2650000', '2025-07-15', 'DPT015', 'Paris', '12 rue de Rivoli', '48.8566130', '2.3522220', 'DST015', 6.00, 28, 'client', 0, NULL, '2025-07-16 12:47:53', 50.00, 30.00, 20.00, 930.00, 14000, 13500, 1),
(30, 'Paris', '10 avenue des Champs', '48.8650000', '2.3120000', '2025-07-01', 'DPT001', 'Lyon', '5 quai de Saône', '45.7578000', '4.8320000', 'DST001', 10.50, 14, 'client', 0, NULL, '2025-07-16 12:48:54', 60.00, 40.00, 20.00, 460.00, 15000, 14000, 1),
(31, 'Lyon', '3 rue de Marseille', '45.7500000', '4.8500000', '2025-07-02', 'DPT002', 'Marseille', '1 place Castellane', '43.2910000', '5.3810000', 'DST002', 5.20, 15, 'client', 0, NULL, '2025-07-16 12:48:54', 50.00, 30.00, 15.00, 450.00, 12000, 11500, 0),
(32, 'Marseille', '20 boulevard Longchamp', '43.3050000', '5.3880000', '2025-07-03', 'DPT003', 'Nice', '14 promenade des Anglais', '43.6950000', '7.2650000', 'DST003', 8.00, 16, 'client', 0, NULL, '2025-07-16 12:48:54', 55.00, 35.00, 25.00, 320.00, 14000, 13500, 1),
(33, 'Toulouse', '8 rue d\'Alsace', '43.6040000', '1.4440000', '2025-07-04', 'DPT004', 'Bordeaux', '3 cours Victor Hugo', '44.8370000', '-0.5790000', 'DST004', 12.00, 17, 'client', 0, NULL, '2025-07-16 12:48:54', 70.00, 40.00, 30.00, 540.00, 18000, 17000, 0),
(34, 'Nice', '7 rue de Béthune', '50.6320000', '3.0610000', '2025-07-05', 'DPT005', 'Paris', '23 avenue Jean Jaurès', '45.7510000', '4.8410000', 'DST005', 3.50, 18, 'client', 0, NULL, '2025-07-16 12:48:54', 40.00, 25.00, 10.00, 930.00, 11000, 10500, 1),
(35, 'Bordeaux', '42 rue Kervégan', '47.2180000', '-1.5530000', '2025-07-06', 'DPT006', 'Lille', '7 rue de Béthune', '50.6320000', '3.0610000', 'DST006', 6.70, 19, 'client', 0, NULL, '2025-07-16 12:48:54', 45.00, 30.00, 18.00, 840.00, 13000, 12500, 0),
(36, 'Lille', '8 rue de Paris', '50.6370000', '3.0630000', '2025-07-07', 'DPT007', 'Nantes', '42 rue Kervégan', '47.2180000', '-1.5530000', 'DST007', 9.00, 20, 'client', 0, NULL, '2025-07-16 12:48:54', 60.00, 35.00, 22.00, 730.00, 16000, 15000, 1),
(38, 'Strasbourg', '5 rue du Général', '48.5830000', '7.7450000', '2025-07-09', 'DPT009', 'Marseille', '20 boulevard Longchamp', '43.3050000', '5.3880000', 'DST009', 7.10, 22, 'client', 0, NULL, '2025-07-16 12:48:54', 65.00, 40.00, 25.00, 920.00, 17000, 16500, 1),
(39, 'Montpellier', '12 quai de la Fontaine', '43.6110000', '3.8760000', '2025-07-10', 'DPT010', 'Bordeaux', '3 cours Victor Hugo', '44.8370000', '-0.5790000', 'DST010', 2.80, 23, 'client', 0, NULL, '2025-07-16 12:48:54', 35.00, 20.00, 10.00, 650.00, 10000, 9500, 0),
(40, 'Paris', '1 rue de Lyon', '48.8470000', '2.3740000', '2025-07-11', 'DPT011', 'Strasbourg', '5 rue du Général', '48.5830000', '7.7450000', 'DST011', 11.20, 24, 'client', 0, NULL, '2025-07-16 12:48:54', 75.00, 50.00, 30.00, 490.00, 19000, 18500, 1),
(41, 'Lyon', '14 place Bellecour', '45.7570000', '4.8320000', '2025-07-12', 'DPT012', 'Nice', '14 promenade des Anglais', '43.6950000', '7.2650000', 'DST012', 5.50, 25, 'client', 0, NULL, '2025-07-16 12:48:54', 55.00, 30.00, 20.00, 470.00, 13000, 12500, 0),
(42, 'Marseille', '8 rue Saint Ferreol', '43.3000000', '5.3800000', '2025-07-13', 'DPT013', 'Lille', '8 rue de Paris', '50.6370000', '3.0630000', 'DST013', 8.80, 26, 'client', 0, NULL, '2025-07-16 12:48:54', 60.00, 35.00, 25.00, 1020.00, 16000, 15500, 1),
(43, 'Toulouse', '5 rue Alsace Lorraine', '43.6040000', '1.4440000', '2025-07-14', 'DPT014', 'Montpellier', '12 quai de la Fontaine', '43.6110000', '3.8760000', 'DST014', 3.30, 27, 'client', 0, NULL, '2025-07-16 12:48:54', 45.00, 25.00, 15.00, 300.00, 11000, 10500, 0),
(44, 'Nice', '10 avenue Malausséna', '43.7000000', '7.2650000', '2025-07-15', 'DPT015', 'Paris', '12 rue de Rivoli', '48.8566130', '2.3522220', 'DST015', 6.00, 28, 'client', 0, NULL, '2025-07-16 12:48:54', 50.00, 30.00, 20.00, 930.00, 14000, 13500, 1),
(45, 'Paris', '12 avenue Victor Hugo', '48.9851000', '2.2699000', '2025-07-20', 'DPT001', 'Lyon', '5 quai Victor Augagneur', '45.7485000', '4.8467000', 'DST001', 12.50, 14, 'client', 0, NULL, '2025-07-16 12:58:23', 50.00, 30.00, 20.00, 470.00, 15000, 14000, 1),
(46, 'Marseille', '3 rue de la République', '43.2965000', '5.3698000', '2025-07-22', 'DPT002', 'Toulouse', '20 allées Jean Jaurès', '43.6044000', '1.4442000', 'DST002', 8.00, 15, 'client', 0, NULL, '2025-07-16 12:58:23', 40.00, 25.00, 15.00, 630.00, 18000, 17000, 0),
(47, 'Lille', '1 place du Général de Gaulle', '50.6376000', '3.0630000', '2025-07-25', 'DPT003', 'Bordeaux', '10 cours de l\'Intendance', '44.8378000', '-0.5792000', 'DST003', 5.30, 16, 'merchant', 0, NULL, '2025-07-16 12:58:23', 30.00, 20.00, 10.00, 830.00, 22000, 20000, 1),
(48, 'Nice', '15 avenue Jean Médecin', '43.7034000', '7.2663000', '2025-07-28', 'DPT004', 'Nantes', '8 quai François Mitterrand', '47.2136000', '-1.5536000', 'DST004', 20.00, 17, 'client', 1, 15, '2025-07-16 12:58:23', 60.00, 40.00, 25.00, 930.00, 30000, 28000, 0),
(50, 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', NULL, '77500', 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', '75012', NULL, 12, 'client', 0, NULL, '2025-07-16 21:49:23', NULL, NULL, NULL, 25.05, NULL, NULL, 0),
(51, 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', NULL, '77500', 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', '75012', NULL, 12, 'client', 0, NULL, '2025-07-16 21:51:18', NULL, NULL, NULL, 25.05, NULL, NULL, 0),
(52, 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', NULL, '77500', 'Paris', 'Box A', '48.8461660', '2.3856570', 'PAR01', NULL, 13, 'client', 1, NULL, '2025-07-16 22:35:38', NULL, NULL, NULL, 32.92, NULL, 1000, 0),
(53, 'Paris', 'Box A', '48.8461660', '2.3856570', NULL, 'PAR01', 'Paris', '21 Quai d\'Austerlitz 75013 Paris', '48.8388840', '2.3717940', '75013', NULL, 13, 'client', 0, 52, '2025-07-16 21:28:36', NULL, NULL, NULL, 32.92, NULL, 1000, 0),
(54, 'Paris', '242 Rue du Faubourg Saint-Antoine 75012 Paris', '48.8492000', '2.3896000', NULL, '75012', 'Mareuil-lès-Meaux', '78 Rue Jean Jaures 77100 Meaux', '48.9609450', '2.8892860', '77100', 1.00, 49, 'merchant', 0, NULL, '2025-07-17 18:06:28', NULL, NULL, NULL, 44.81, 1694, NULL, 0),
(55, 'Meaux', '78 Rue Jean Jaures 77100 Meaux', '48.9609450', '2.8892860', NULL, '77100', 'Chelles', '1 Allée du Charron 77500 Chelles', '48.8879530', '2.6212880', '77500', 1.00, 49, 'merchant', 0, NULL, '2025-07-17 18:37:59', NULL, NULL, NULL, 27.14, 1164, NULL, 0),
(56, 'Coulommiers', '1 Avenue Victor Hugo 77120 Coulommiers', '48.8138480', '3.0798630', NULL, '77120', 'Paris', 'Impasse Erard 75012 Paris', '48.8458240', '2.3842450', '75012', NULL, 13, 'client', 0, NULL, '2025-07-17 18:44:24', NULL, NULL, NULL, 60.30, NULL, 1000, 1),
(57, 'Lille', '94 Rue Nationale 59800 Lille', '50.6355850', '3.0588500', NULL, '59800', 'Paris', '65 Rue Lecourbe 75015 Paris', '48.8435040', '2.3060480', '75015', NULL, 13, 'client', 0, NULL, '2025-07-17 20:51:35', NULL, NULL, NULL, 225.42, NULL, 1000, 1),
(58, 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', NULL, '75012', 'Bordeaux', '45 Rue Pelleport 33800 Bordeaux', '44.8230630', '-0.5605950', '33800', 2.00, 49, 'merchant', 0, NULL, '2025-07-17 21:34:21', NULL, NULL, NULL, 587.88, 18736, NULL, 1),
(59, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', NULL, '94800', 'Paris', '21 Quai d\'Austerlitz 75013 Paris', '48.8388840', '2.3717940', '75013', 1.00, 49, 'merchant', 0, NULL, '2025-07-17 21:56:33', NULL, NULL, NULL, 8.18, 595, NULL, 1),
(60, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', NULL, '94800', 'Paris', '21 Rue Erard 75012 Paris', '48.8461660', '2.3856570', '75012', 1.00, 49, 'merchant', 0, NULL, '2025-07-17 22:00:48', NULL, NULL, NULL, 9.88, 646, NULL, 1),
(61, 'Villejuif', '78 Rue Jean Jaurès 94800 Villejuif', '48.7913320', '2.3665620', NULL, '94800', 'Paris', '21 Quai d\'Austerlitz 75013 Paris', '48.8388840', '2.3717940', '75013', 1.00, 49, 'merchant', 0, NULL, '2025-07-17 22:03:07', NULL, NULL, NULL, 8.18, 595, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `availability_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'en_attente',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `note` tinyint(3) UNSIGNED DEFAULT NULL,
  `commentaire` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `user_id`, `availability_id`, `status`, `is_paid`, `note`, `commentaire`, `created_at`, `updated_at`) VALUES
(4, 5, 4, 'validée', 0, 5, 'Très satisfaite du service', '2025-05-11 21:51:17', '2025-05-11 21:51:17'),
(5, 5, 5, 'validée', 0, 4, 'Travail bien fait, merci', '2025-05-11 21:51:17', '2025-05-11 21:51:17'),
(6, 5, 6, 'validée', 0, 5, 'Ponctuel et très professionnel', '2025-05-11 21:51:17', '2025-05-11 21:51:17'),
(7, 6, 9, 'validée', 0, 5, 'Gaelle est une coiffeuse très professionnel et l\'accueil s\'est super bien passé. Coiffure parfaite.', '2025-05-15 09:41:58', '2025-06-27 13:28:32'),
(8, 6, 11, 'validée', 0, NULL, NULL, '2025-05-15 10:37:14', '2025-05-15 10:37:14'),
(3001, 6, 2001, 'validée', 0, NULL, NULL, '2025-05-15 12:47:24', '2025-05-15 12:47:24'),
(3002, 6, 2002, 'validée', 0, 5, 'Franck est un bon bricoleur', '2025-06-19 11:48:56', '2025-07-09 22:44:35'),
(3003, 13, 1, 'validée', 0, NULL, NULL, '2025-07-17 13:46:19', '2025-07-17 13:46:19'),
(3004, 13, 3, 'validée', 0, NULL, NULL, '2025-07-17 13:46:42', '2025-07-17 13:46:42'),
(3005, 13, 3002, 'validée', 0, 4, 'super top', '2025-07-17 20:15:35', '2025-07-17 20:59:37'),
(3006, 13, 7, 'validée', 1, NULL, NULL, '2025-07-17 20:23:58', '2025-07-17 22:24:36'),
(3008, 13, 8, 'validée', 1, NULL, NULL, '2025-07-17 20:29:35', '2025-07-17 22:30:04'),
(3009, 13, 10, 'validée', 0, NULL, NULL, '2025-07-17 20:32:52', '2025-07-17 20:32:52'),
(3010, 13, 13, 'validée', 0, NULL, NULL, '2025-07-17 20:34:17', '2025-07-17 20:34:17'),
(3011, 13, 20, 'validée', 0, NULL, NULL, '2025-07-17 20:34:48', '2025-07-17 20:34:48'),
(3013, 13, 3003, 'validée', 1, NULL, NULL, '2025-07-17 21:14:26', '2025-07-17 23:14:48'),
(3014, 13, 3004, 'validée', 1, NULL, NULL, '2025-07-17 21:43:47', '2025-07-17 23:44:58');

-- --------------------------------------------------------

--
-- Structure de la table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) UNSIGNED NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `target_deliverer_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reviews`
--

INSERT INTO `reviews` (`review_id`, `reviewer_id`, `target_deliverer_id`, `request_id`, `rating`, `comment`, `created_at`) VALUES
(1, 14, 26, 15, 5, 'Livraison rapide et soignée', '2025-07-16 12:59:16'),
(2, 15, 27, 16, 4, 'Bon service mais retard de 30 min', '2025-07-16 12:59:16'),
(3, 16, 28, 17, 5, 'Très professionnel', '2025-07-16 12:59:16'),
(4, 18, 26, 19, 3, 'Colis légèrement endommagé', '2025-07-16 12:59:16'),
(5, 17, 27, 18, 4, 'Ponctuel et aimable', '2025-07-16 12:59:16'),
(6, 13, 12, 9, 4, 'test', '2025-07-16 22:18:42');

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` enum('Admin','Customer','ServiceProvider','Deliverer','Seller') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(0, 'Admin'),
(1, 'Customer'),
(4, 'ServiceProvider'),
(2, 'Deliverer'),
(3, 'Seller');

-- --------------------------------------------------------

--
-- Structure de la table `role_change_requests`
--

CREATE TABLE `role_change_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_role` enum('Customer','Deliverer','Seller','ServiceProvider') NOT NULL,
  `current_roles` text COMMENT 'JSON des rôles actuels au moment de la demande',
  `status` enum('En attente','Approuvé','Refusé','Annulé') NOT NULL DEFAULT 'En attente',
  `reason` text COMMENT 'Raison de la demande (optionnel)',
  `admin_comment` text COMMENT 'Commentaire de l''admin lors de la validation',
  `requires_verification` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 si des justificatifs sont requis',
  `justificatifs_uploaded` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 si justificatifs uploadés',
  `requested_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL COMMENT 'ID de l''admin qui a traité la demande'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `role_change_requests`
--

INSERT INTO `role_change_requests` (`request_id`, `user_id`, `requested_role`, `current_roles`, `status`, `reason`, `admin_comment`, `requires_verification`, `justificatifs_uploaded`, `requested_at`, `processed_at`, `processed_by`) VALUES
(5, 12, 'Customer', '\"[\\\"Deliverer\\\"]\"', 'Approuvé', NULL, NULL, 0, 0, '2025-07-08 14:25:49', '2025-07-08 12:25:50', NULL),
(6, 13, 'Deliverer', '\"[\\\"Customer\\\"]\"', 'Approuvé', NULL, NULL, 1, 1, '2025-07-08 14:43:40', '2025-07-08 15:24:26', 9);

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

CREATE TABLE `service` (
  `offered_service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type_id` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `details` text,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`offered_service_id`, `user_id`, `service_type_id`, `price`, `details`, `address`) VALUES
(5, 22, 2, '5.99', 'Livraison standard rapide', '12 rue de Paris, Strasbourg'),
(6, 23, 3, '12.99', 'Livraison express en 2h', '8 avenue de Lyon, Montpellier'),
(7, 24, 4, '7.50', 'Livraison écologique à vélo', '5 rue Verte, Paris'),
(8, 48, 5, '3.99', 'Point relais partenaire', 'Centre commercial, Nice'),
(9, 22, 6, '6.50', 'Livraison à domicile', '10 rue du Port, Lille'),
(12, 48, 9, '9.90', 'Livraison zone rurale', 'Ferme du Bois, Bordeaux'),
(14, 23, 11, '8.50', 'Livraison soirée', '7 avenue de la Gare, Paris'),
(16, 48, 13, '19.99', 'Livraison urgence', '2 rue de l\'Urgence, Marseille'),
(17, 22, 2, '5.99', 'Livraison standard', '11 rue de la Paix, Strasbourg'),
(18, 23, 3, '12.99', 'Livraison express', '9 avenue de la Liberté, Montpellier'),
(19, 24, 4, '7.50', 'Livraison écologique', '6 rue Bleue, Paris'),
(20, 48, 5, '3.99', 'Point relais', 'Centre ville, Nice'),
(21, 22, 6, '6.50', 'Livraison à domicile', '14 rue du Lac, Lille'),
(24, 48, 9, '9.99', 'Livraison rurale', 'Ferme du Pré, Bordeaux');

-- --------------------------------------------------------

--
-- Structure de la table `serviceavailability`
--

CREATE TABLE `serviceavailability` (
  `availability_id` int(11) NOT NULL,
  `offered_service_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `serviceavailability`
--

INSERT INTO `serviceavailability` (`availability_id`, `offered_service_id`, `date`, `start_time`, `end_time`) VALUES
(3, 5, '2025-07-22', '10:00:00', '12:00:00'),
(4, 5, '2025-07-23', '15:00:00', '17:00:00'),
(5, 6, '2025-07-24', '08:00:00', '10:00:00'),
(6, 6, '2025-07-25', '13:00:00', '15:00:00'),
(7, 7, '2025-07-26', '09:30:00', '11:30:00'),
(8, 7, '2025-07-27', '16:00:00', '18:00:00'),
(9, 8, '2025-07-28', '10:00:00', '12:00:00'),
(10, 8, '2025-07-29', '14:00:00', '16:00:00'),
(11, 9, '2025-07-30', '09:00:00', '11:00:00'),
(12, 9, '2025-07-31', '15:00:00', '17:00:00'),
(13, 10, '2025-08-01', '08:00:00', '10:00:00'),
(14, 10, '2025-08-02', '13:00:00', '15:00:00'),
(15, 11, '2025-08-03', '09:30:00', '11:30:00'),
(16, 11, '2025-08-04', '16:00:00', '18:00:00'),
(17, 12, '2025-08-05', '10:00:00', '12:00:00'),
(18, 12, '2025-08-06', '14:00:00', '16:00:00'),
(19, 13, '2025-08-07', '09:00:00', '11:00:00'),
(20, 13, '2025-08-08', '15:00:00', '17:00:00'),
(1001, 6, '2024-04-20', '08:00:00', '10:00:00'),
(1002, 3, '2025-05-13', '11:00:00', '12:00:00'),
(1003, 8, '2025-05-16', '11:00:00', '13:00:00'),
(1004, 8, '2025-05-16', '14:00:00', '16:00:00'),
(1005, 8, '2025-05-15', '12:38:00', '12:45:00'),
(2001, 1001, '2024-05-14', '10:00:00', '12:00:00'),
(2002, 7, '2025-06-19', '13:30:00', '14:30:00'),
(2003, 3, '2025-07-10', '09:00:00', '11:00:00'),
(3001, 5, '2024-04-12', '09:00:00', '11:00:00'),
(3002, 8, '2025-07-16', '07:00:00', '09:00:00'),
(3003, 24, '2025-07-17', '19:14:00', '23:14:00'),
(3004, 8, '2025-07-17', '09:00:00', '11:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `servicebooking`
--

CREATE TABLE `servicebooking` (
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('En Attente','Confirmé','Annulé') DEFAULT 'En Attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `servicelocation`
--

CREATE TABLE `servicelocation` (
  `location_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `radius_km` int(11) DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `servicetype`
--

CREATE TABLE `servicetype` (
  `service_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_price_fixed` tinyint(1) NOT NULL DEFAULT '1',
  `fixed_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `servicetype`
--

INSERT INTO `servicetype` (`service_type_id`, `name`, `description`, `is_price_fixed`, `fixed_price`) VALUES
(2, 'Livraison Standard', 'Livraison classique sous 24h', 1, '5.99'),
(3, 'Livraison Express', 'Livraison en 2-4 heures', 1, '12.99'),
(4, 'Livraison Écologique', 'Livraison à vélo ou en véhicule électrique', 1, '7.50'),
(5, 'Point Relais', 'Livraison en point relais partenaire', 1, '3.99'),
(6, 'Livraison à Domicile', 'Livraison directement à votre porte', 1, '6.50'),
(7, 'Service de Montage', 'Montage et installation de meubles', 0, NULL),
(8, 'Service de Nettoyage', 'Nettoyage post-déménagement', 0, NULL),
(9, 'Livraison Rurale', 'Livraison en zone rurale ou éloignée', 1, '9.99'),
(10, 'Service de Déballage', 'Déballage et rangement des objets', 0, NULL),
(11, 'Livraison Soirée', 'Livraison après 18h', 1, '8.50'),
(12, 'Garde-Meubles', 'Stockage temporaire de meubles', 0, NULL),
(13, 'Livraison Urgence', 'Livraison en moins d\'1 heure', 1, '19.99');

-- --------------------------------------------------------

--
-- Structure de la table `storage_boxes`
--

CREATE TABLE `storage_boxes` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `address_street` varchar(150) NOT NULL,
  `address_zip` varchar(10) NOT NULL,
  `address_city` varchar(100) NOT NULL,
  `location_city` varchar(100) NOT NULL,
  `location_code` varchar(10) NOT NULL,
  `capacity_kg` float DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `storage_boxes`
--

INSERT INTO `storage_boxes` (`id`, `label`, `address_street`, `address_zip`, `address_city`, `location_city`, `location_code`, `capacity_kg`, `lat`, `lon`, `created_at`) VALUES
(21, 'Box test', '10 rue de Paris', '75001', 'Paris', 'Paris', '75', 100, '48.8566140', '2.3522219', '2025-05-15 12:45:23'),
(22, 'Box B', '15 avenue Victor Hugo', '69006', 'Lyon', 'Lyon', '69', 200, '45.7640430', '4.8356590', '2025-05-15 12:45:23'),
(23, 'Box C', '8 boulevard Haussmann', '75009', 'Paris', 'Paris', '75', 150, '48.8566140', '2.3522219', '2025-05-15 12:45:23'),
(24, 'Box D', '3 rue Nationale', '59000', 'Lille', 'Lille', '59', 120, '50.6292500', '3.0572560', '2025-05-15 12:45:23'),
(25, 'Box E', '22 cours Jean Jaurès', '38000', 'Grenoble', 'Grenoble', '38', 80, '45.1885290', '5.7245240', '2025-05-15 12:45:23'),
(26, 'Box F', '5 rue Sainte-Catherine', '33000', 'Bordeaux', 'Bordeaux', '33', 90, '44.8377890', '-0.5791800', '2025-05-15 12:45:23'),
(27, 'Box G', '18 avenue de la République', '21000', 'Dijon', 'Dijon', '21', 110, '47.3220470', '5.0414800', '2025-05-15 12:45:23'),
(28, 'Box H', '7 place Bellecour', '69002', 'Lyon', 'Lyon', '69', 130, '45.7640430', '4.8356590', '2025-05-15 12:45:23'),
(29, 'Box I', '9 rue du Faubourg', '67000', 'Strasbourg', 'Strasbourg', '67', 140, '48.5734050', '7.7521110', '2025-05-15 12:45:23'),
(30, 'Box J', '13 allée des Soupirs', '31000', 'Toulouse', 'Toulouse', '31', 100, '43.6046520', '1.4442090', '2025-05-15 12:45:23'),
(31, 'Box K', '25 quai des Chartrons', '33000', 'Bordeaux', 'Bordeaux', '33', 150, '44.8377890', '-0.5791800', '2025-05-15 12:45:23'),
(32, 'Box L', '2 avenue Alsace Lorraine', '80000', 'Amiens', 'Amiens', '80', 70, '49.8940670', '2.2957530', '2025-05-15 12:45:23'),
(33, 'Box M', '11 rue de Metz', '57000', 'Metz', 'Metz', '57', 120, '49.1193090', '6.1757150', '2025-05-15 12:45:23'),
(34, 'Box N', '6 rue Pasteur', '86000', 'Poitiers', 'Poitiers', '86', 60, '46.5802240', '0.3403750', '2025-05-15 12:45:23'),
(35, 'Box O', '44 rue Emile Zola', '72000', 'Le Mans', 'Le Mans', '72', 85, '48.0061100', '0.1995560', '2025-05-15 12:45:23'),
(36, 'Box P', '99 avenue Jean Moulin', '13008', 'Marseille', 'Marseille', '13', 200, '43.2964820', '5.3697800', '2025-05-15 12:45:23'),
(37, 'Box Q', '77 rue Nationale', '44000', 'Nantes', 'Nantes', '44', 95, '47.2183710', '-1.5536210', '2025-05-15 12:45:23'),
(38, 'Box R', '12 avenue du Maréchal Juin', '06000', 'Nice', 'Nice', '06', 105, '43.7101730', '7.2619530', '2025-05-15 12:45:23'),
(39, 'Box S', '4 rue de la République', '34000', 'Montpellier', 'Montpellier', '34', 115, '43.6107690', '3.8767160', '2025-05-15 12:45:23'),
(40, 'Box T', '28 boulevard Carnot', '80000', 'Amiens', 'Amiens', '80', 125, '49.8940670', '2.2957530', '2025-05-15 12:45:23'),
(41, 'Chelles', '1 allée du charron', '77500', 'Chelles', 'Chelles', '77500', 100, '48.8879530', '2.6212880', '2025-06-19 12:52:10'),
(42, 'Box A', '21 rue erard', '75012', 'Paris', 'Paris', 'PAR01', 50, '48.8461660', '2.3856570', '2025-07-16 12:47:53'),
(43, 'Box B', '5 boulevard Voltaire', '75011', 'Paris', 'Paris', 'PAR11', 30, '48.8590000', '2.3720000', '2025-07-16 12:47:53'),
(44, 'Box C', '23 avenue Jean Jaurès', '69007', 'Lyon', 'Lyon', 'LYO07', 40, '45.7510000', '4.8410000', '2025-07-16 12:47:53'),
(45, 'Box D', '10 quai de Saône', '69002', 'Lyon', 'Lyon', 'LYO02', 60, '45.7578000', '4.8320000', '2025-07-16 12:47:53'),
(46, 'Box E', '1 place Castellane', '13006', 'Marseille', 'Marseille', 'MAR06', 35, '43.2910000', '5.3810000', '2025-07-16 12:47:53'),
(47, 'Box F', '8 rue d\'Alsace', '31000', 'Toulouse', 'Toulouse', 'TOU00', 45, '43.6040000', '1.4440000', '2025-07-16 12:47:53'),
(48, 'Box G', '14 promenade des Anglais', '06000', 'Nice', 'Nice', 'NIC00', 25, '43.6950000', '7.2650000', '2025-07-16 12:47:53'),
(49, 'Box H', '3 cours Victor Hugo', '33000', 'Bordeaux', 'Bordeaux', 'BOR00', 50, '44.8370000', '-0.5790000', '2025-07-16 12:47:53'),
(50, 'Box I', '7 rue de Béthune', '59000', 'Lille', 'Lille', 'LIL00', 40, '50.6320000', '3.0610000', '2025-07-16 12:47:53'),
(51, 'Box J', '42 rue Kervégan', '44000', 'Nantes', 'Nantes', 'NAN00', 30, '47.2180000', '-1.5530000', '2025-07-16 12:47:53'),
(53, 'Box B', '5 boulevard Voltaire', '75011', 'Paris', 'Paris', 'PAR11', 30, '48.8590000', '2.3720000', '2025-07-16 12:48:32'),
(54, 'Box C', '23 avenue Jean Jaurès', '69007', 'Lyon', 'Lyon', 'LYO07', 40, '45.7510000', '4.8410000', '2025-07-16 12:48:32'),
(55, 'Box D', '10 quai de Saône', '69002', 'Lyon', 'Lyon', 'LYO02', 60, '45.7578000', '4.8320000', '2025-07-16 12:48:32'),
(56, 'Box E', '1 place Castellane', '13006', 'Marseille', 'Marseille', 'MAR06', 35, '43.2910000', '5.3810000', '2025-07-16 12:48:32'),
(57, 'Box F', '8 rue d\'Alsace', '31000', 'Toulouse', 'Toulouse', 'TOU00', 45, '43.6040000', '1.4440000', '2025-07-16 12:48:32'),
(58, 'Box G', '14 promenade des Anglais', '06000', 'Nice', 'Nice', 'NIC00', 25, '43.6950000', '7.2650000', '2025-07-16 12:48:32'),
(59, 'Box H', '3 cours Victor Hugo', '33000', 'Bordeaux', 'Bordeaux', 'BOR00', 50, '44.8370000', '-0.5790000', '2025-07-16 12:48:32'),
(60, 'Box I', '7 rue de Béthune', '59000', 'Lille', 'Lille', 'LIL00', 40, '50.6320000', '3.0610000', '2025-07-16 12:48:32'),
(61, 'Box J', '42 rue Kervégan', '44000', 'Nantes', 'Nantes', 'NAN00', 30, '47.2180000', '-1.5530000', '2025-07-16 12:48:32'),
(62, 'Box A1', '10 rue de Rivoli', '75004', 'Paris', 'Paris', 'PA001', 100, '48.8566000', '2.3522000', '2025-07-16 12:58:15'),
(63, 'Box B2', '25 avenue Jean Jaurès', '69007', 'Lyon', 'Lyon', 'LY002', 150, '45.7420000', '4.8537000', '2025-07-16 12:58:15'),
(64, 'Box C3', '5 boulevard Longchamp', '13004', 'Marseille', 'Marseille', 'MA003', 120, '43.3007000', '5.3850000', '2025-07-16 12:58:15'),
(65, 'Box D4', '12 rue d\'Alsace', '31000', 'Toulouse', 'Toulouse', 'TO004', 80, '43.6045000', '1.4440000', '2025-07-16 12:58:15'),
(66, 'Box E5', '8 promenade des Anglais', '06000', 'Nice', 'Nice', 'NI005', 90, '43.6959000', '7.2713000', '2025-07-16 12:58:15');

-- --------------------------------------------------------

--
-- Structure de la table `subscriptionplan`
--

CREATE TABLE `subscriptionplan` (
  `plan_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `subscription_items`
--

CREATE TABLE `subscription_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_product` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `supportingdocument`
--

CREATE TABLE `supportingdocument` (
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` enum('Carte Identite','Permis De Conduire','Contrat','Facture','Autre') DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `description` text,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `validated` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `tracking_events`
--

CREATE TABLE `tracking_events` (
  `event_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `deliverer_id` int(11) NOT NULL,
  `status` enum('Pris en charge','En cours','Déposé en box','Retiré du box','Livré') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `location_city` varchar(100) DEFAULT NULL,
  `location_code` varchar(10) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tracking_events`
--

INSERT INTO `tracking_events` (`event_id`, `request_id`, `deliverer_id`, `status`, `description`, `location_city`, `location_code`, `created_at`) VALUES
(1, 51, 13, 'En cours', NULL, NULL, NULL, '2025-07-16 20:20:11'),
(2, 51, 13, 'Livré', NULL, NULL, NULL, '2025-07-16 20:22:45');

-- --------------------------------------------------------

--
-- Structure de la table `userrole`
--

CREATE TABLE `userrole` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `userrole`
--

INSERT INTO `userrole` (`user_id`, `role_id`) VALUES
(3, 0),
(9, 0),
(38, 0),
(39, 0),
(40, 0),
(41, 0),
(42, 0),
(43, 0),
(5, 1),
(6, 1),
(8, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(7, 2),
(10, 2),
(12, 2),
(13, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(44, 2),
(1, 3),
(2, 3),
(32, 3),
(34, 3),
(35, 3),
(36, 3),
(37, 3),
(49, 3),
(50, 3),
(51, 3),
(54, 3),
(55, 3),
(22, 4),
(23, 4),
(24, 4),
(48, 4);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `business_address` varchar(255) DEFAULT NULL,
  `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `stripe_id` varchar(255) DEFAULT NULL,
  `nfc_code` char(16) DEFAULT NULL,
  `pm_type` varchar(255) DEFAULT NULL,
  `pm_last_four` varchar(4) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `preferred_city` varchar(100) DEFAULT NULL,
  `is_validated` tinyint(1) DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `sector` varchar(255) DEFAULT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `tutorial_done` tinyint(1) NOT NULL DEFAULT '0',
  `qr_code` varchar(255) DEFAULT NULL,
  `onesignal_player_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `profile_picture`, `business_name`, `business_address`, `registration_date`, `stripe_id`, `nfc_code`, `pm_type`, `pm_last_four`, `trial_ends_at`, `preferred_city`, `is_validated`, `description`, `sector`, `banned`, `tutorial_done`, `qr_code`, `onesignal_player_id`) VALUES
(1, 'test', 'test', 'test@test', '$2y$12$JLKe56SQwlJhA5GzdxtulOfI4c8fNAT53r0EPNKCn1FbUbxPDJODK', NULL, NULL, 'test', 'pipi', '2025-05-09 10:42:27', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(2, 'monsieur', 'monsieur', 'monsieur@monsieur.com', '$2y$12$ejzJ68hFCQ9U0TXI159ateckopualcPWcwLdunlFM77J5UkDjZZHm', NULL, NULL, 'pokemon', 'pokemon', '2025-05-15 10:51:43', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(3, 'nathanael', 'Dapiedade', 'Dapiedade@gmail.com', '$2y$12$srBHH9ngBV8YC5obSUxWU.IdKmfJLAwC.tUDW7PqKQaYt6e19JjGO', NULL, 'profile_pictures/ijGRUlwjmtM0vwFBBSyRyvqhmeVcFtc9cJOgrerb.jpg', NULL, NULL, '2025-05-15 10:55:43', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(5, 'client', 'test', 'client@gmail.com', '$2y$12$P364eleCtDUKXGvfolMSJOzM9qrdtYQ22vJXIL8lBDxrYxTGyYXE2', NULL, NULL, NULL, NULL, '2025-05-15 11:04:25', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(6, 'evan', 'evan', 'evan@gmail.com', NULL, NULL, 'profile_pics/1d6w7TIF1aKBoER68UXMrVrLxpaJouqeL0McnqKw.webp', NULL, NULL, '2025-05-15 12:24:22', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(7, 'test', 'livreur', 'livreur@gmail.com', '$2y$12$rWma0G44UK.ib9liJsCl3Owz/.gscSz0IqtIfZWL8qiBNuWY.iq76', NULL, NULL, NULL, NULL, '2025-05-15 12:27:32', NULL, '2d58a2a428235711', NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(8, 'Test', 'Evan', 'testtuto@gmail.com', NULL, NULL, 'profile_pics/sSP1lJUhaQpZMpOAKrQuFInz2mBEHCCc5MlJ1DIV.jpg', NULL, NULL, '2025-06-17 18:40:12', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(9, 'admin', 'admin', 'admin@ecodeli.fr', '$2y$12$3JhfWO.4SxLMbQOaYmU.Pe66Y24KzkfOoKJDWDyiUvRjaFdvZMxkS', NULL, NULL, NULL, NULL, '2025-06-18 20:23:26', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(10, 'livreur', 'livreur2', 'livreur2@gmail.com', '$2y$12$c9fWQs39Z7M9jyiOaLU1rOtkCswwDLzEjUChWx2NLxpKHgoi/cNIm', NULL, NULL, NULL, NULL, '2025-06-18 22:23:43', NULL, '7631f6a30826c7eb', NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(11, 'client2', 'client2', 'client2@gmail.com', '$2y$12$vcXCc0yiJ06x8BW54EzNheUiBax8KwLrpHOv7iK90lAxkPEjrdvGm', NULL, NULL, NULL, NULL, '2025-06-19 11:59:31', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL),
(12, 'test', 'androi', 'testandroid@gmail.com', NULL, NULL, NULL, NULL, NULL, '2025-06-26 12:14:27', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 1, 'ECODELI_919a6610d0c80a4d', NULL),
(13, 'Demo', 'User', 'demo@ecodeli.com', '$2y$12$raizstU7CDDxV.MOvaRhjOr7SXjkC49iLjxqJNOykKzds8HtunRqi', '0123456789', 'profile_pics/profile_687813be04b57_1752699838.png', NULL, NULL, '2025-07-01 22:24:16', NULL, '4b0cab3cf79f93ba', NULL, NULL, NULL, 'Paris', 1, NULL, NULL, 0, 1, 'ECODELI_b6ec634eb6a65f0e', NULL),
(14, 'Alice', 'Martin', 'alice.martin@example.com', 'password123', '0600000014', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Paris', 1, NULL, NULL, 0, 1, 'QR00014', NULL),
(15, 'Gabriel', 'Leroy', 'gabriel.leroy@example.com', 'password123', '0600000015', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Lyon', 0, NULL, NULL, 0, 1, 'QR00015', NULL),
(16, 'Sophie', 'Dubois', 'sophie.dubois@example.com', 'password123', '0600000016', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Marseille', 1, NULL, NULL, 0, 1, 'QR00016', NULL),
(17, 'Adrien', 'Moreau', 'adrien.moreau@example.com', 'password123', '0600000017', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Toulouse', 0, NULL, NULL, 0, 1, 'QR00017', NULL),
(18, 'Chloe', 'Simon', 'chloe.simon@example.com', 'password123', '0600000018', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Nice', 1, NULL, NULL, 0, 1, 'QR00018', NULL),
(19, 'Lucas', 'Laurent', 'lucas.laurent@example.com', 'password123', '0600000019', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Bordeaux', 0, NULL, NULL, 0, 1, 'QR00019', NULL),
(20, 'Elodi', 'Petit', 'elodie.petit@example.com', 'password123', '0600000020', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Lille', 1, NULL, NULL, 0, 1, 'QR00020', NULL),
(22, 'Camill', 'Mercier', 'camille.mercier@example.com', 'password123', '0600000022', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Strasbourg', 1, NULL, NULL, 0, 1, 'QR00022', NULL),
(23, 'Antoine', 'Roux', 'antoine.roux@example.com', 'password123', '0600000023', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Montpellier', 0, NULL, NULL, 0, 1, 'QR00023', NULL),
(24, 'Manon', 'Fontaine', 'manon.fontaine@example.com', 'password123', '0600000024', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Paris', 1, NULL, NULL, 0, 1, 'QR00024', NULL),
(25, 'Theo', 'Dupont', 'theo.dupont@example.com', 'password123', '0600000025', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Lyon', 0, NULL, NULL, 0, 1, 'QR00025', NULL),
(26, 'Mathilde', 'Muller', 'mathilde.muller@example.com', 'password123', '0600000026', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Marseille', 1, NULL, NULL, 0, 1, 'QR00026', NULL),
(27, 'Hugo', 'Garnier', 'hugo.garnier@example.com', 'password123', '0600000027', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Toulouse', 0, NULL, NULL, 0, 1, 'QR00027', NULL),
(28, 'Laura', 'Lambert', 'laura.lambert@example.com', 'password123', '0600000028', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Nice', 1, NULL, NULL, 0, 1, 'QR00028', NULL),
(29, 'Maxime', 'Rousseau', 'maxime.rousseau@example.com', 'password123', '0600000029', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Bordeaux', 0, NULL, NULL, 0, 1, 'QR00029', NULL),
(30, 'Clara', 'Faure', 'clara.faure@example.com', 'password123', '0600000030', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Lille', 1, NULL, NULL, 0, 1, 'QR00030', NULL),
(32, 'Pauline', 'Caron', 'pauline.caron@example.com', 'password123', '0600000032', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Strasbourg', 1, NULL, NULL, 0, 1, 'QR00032', NULL),
(34, 'Marine', 'Girard', 'marine.girard@example.com', 'password123', '0600000034', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Paris', 1, NULL, NULL, 0, 1, 'QR00034', NULL),
(35, 'Alexis', 'Menard', 'alexis.menard@example.com', 'password123', '0600000035', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Lyon', 0, NULL, NULL, 0, 1, 'QR00035', NULL),
(36, 'Emma', 'Dupuis', 'emma.dupuis@example.com', 'password123', '0600000036', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Marseille', 1, NULL, NULL, 0, 1, 'QR00036', NULL),
(37, 'Romain', 'Noel', 'romain.noel@example.com', 'password123', '0600000037', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Toulouse', 0, NULL, NULL, 0, 1, 'QR00037', NULL),
(38, 'Sarah', 'Breton', 'sarah.breton@example.com', 'password123', '0600000038', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Nice', 1, NULL, NULL, 0, 1, 'QR00038', NULL),
(39, 'Oscar', 'Perrot', 'oscar.perrot@example.com', 'password123', '0600000039', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Bordeaux', 0, NULL, NULL, 0, 1, 'QR00039', NULL),
(40, 'Lea', 'Barbier', 'lea.barbier@example.com', 'password123', '0600000040', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Lille', 1, NULL, NULL, 0, 1, 'QR00040', NULL),
(41, 'Benjamin', 'Pascal', 'benjamin.pascal@example.com', 'password123', '0600000041', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Nantes', 0, NULL, NULL, 0, 1, 'QR00041', NULL),
(42, 'Juliette', 'Roche', 'juliette.roche@example.com', 'password123', '0600000042', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Strasbourg', 1, NULL, NULL, 0, 1, 'QR00042', NULL),
(43, 'Simon', 'Bernard', 'simon.bernard@example.com', 'password123', '0600000043', NULL, NULL, NULL, '2025-07-16 12:39:40', NULL, NULL, NULL, NULL, NULL, 'Montpellier', 0, NULL, NULL, 0, 1, 'QR00043', NULL),
(44, 'Test', 'Evan', 'testlivreur@gmail.com', '$2y$12$gWpf0N.rdhDPEdrpYOZ3je39BnipDuQjSHWNMGEfBR72k9xLpQ/9u', NULL, NULL, NULL, NULL, '2025-07-16 23:10:36', NULL, '5a1cde5afaba42be', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 1, NULL, NULL),
(48, 'test', 'test', 'testpresta@gmail.com', '$2y$12$kWcYPfzrcOkJ0G6ZxOU7IuTovcS5x6W2jhW6mKjH1fcqI6AfVQfUm', NULL, 'profile_pics/profile_6878f6ce1028f_1752757966.png', NULL, NULL, '2025-07-17 00:16:40', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 1, NULL, NULL),
(49, 'test', 'test', 'testcomm@gmail.com', '$2y$12$C1qhQkDRu3O6o0U2H9wpwuvwJwFPuWmwf4/o.RqJ4mQGDXq65iNZy', NULL, NULL, 'test', 'test', '2025-07-17 16:21:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(50, 'TEST', 'TEST', 'testcom@gmail.com', '$2y$12$VrF/n1GIDPu2TUf4gtD5uuKXYh6a0q7uFubPqzHL3i1htEaQxfFq6', NULL, NULL, 'test', 'test', '2025-07-17 16:54:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(51, 'TEST', 'TEST', 'testcom1@gmail.com', '$2y$12$ommtc7ib3NqYbegbiMS9Tud.Tm9Wyg6boieauz1VP0LZtW8hH6oWG', NULL, NULL, 'test', 'test', '2025-07-17 17:12:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(54, 'TEST', 'TEST', 'testcom2@gmail.com', '$2y$12$lKo40WsqFSPti2ehziTb0ux/eeX4UJNQnF7FXSOGX03zIyspUW3Ry', NULL, NULL, 'TEST', 'TEST', '2025-07-17 17:43:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(55, 'TEST', 'TEST', 'testcom3@gmail.com', '$2y$12$9M137KZ4rm7Xh0zMQEv7O.kZGQcfkGJBAI/xjBgsAqoTIVZ86UGjS', NULL, NULL, 'test', 'test', '2025-07-17 17:44:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `usersubscription`
--

CREATE TABLE `usersubscription` (
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `wallets`
--

CREATE TABLE `wallets` (
  `user_id` int(11) NOT NULL,
  `balance_cent` int(11) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `wallets`
--

INSERT INTO `wallets` (`user_id`, `balance_cent`, `updated_at`) VALUES
(44, 0, '2025-07-17 19:57:49'),
(48, 698, '2025-07-17 19:45:16');

-- --------------------------------------------------------

--
-- Structure de la table `withdrawal`
--

CREATE TABLE `withdrawal` (
  `withdrawal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount_cent` int(11) NOT NULL,
  `stripe_payout_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `withdrawal`
--

INSERT INTO `withdrawal` (`withdrawal_id`, `user_id`, `amount_cent`, `stripe_payout_id`, `status`, `created_at`) VALUES
(1, 48, 100, NULL, 'pending', '2025-07-17 19:45:16'),
(2, 44, 1000, NULL, 'pending', '2025-07-17 19:57:49');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `box_assignments`
--
ALTER TABLE `box_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `box_id` (`box_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Index pour la table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `deliveryassignment`
--
ALTER TABLE `deliveryassignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `deliverer_id` (`deliverer_id`);

--
-- Index pour la table `deliveryroutes`
--
ALTER TABLE `deliveryroutes`
  ADD PRIMARY KEY (`route_id`),
  ADD KEY `deliverer_id` (`deliverer_id`);

--
-- Index pour la table `deliveryroutewaypoints`
--
ALTER TABLE `deliveryroutewaypoints`
  ADD PRIMARY KEY (`waypoint_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Index pour la table `delivery_handoffs`
--
ALTER TABLE `delivery_handoffs`
  ADD PRIMARY KEY (`handoff_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Index pour la table `invoicedetail`
--
ALTER TABLE `invoicedetail`
  ADD PRIMARY KEY (`invoice_id`,`payment_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Index pour la table `justificatifs`
--
ALTER TABLE `justificatifs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_request_id` (`role_request_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `negotiations`
--
ALTER TABLE `negotiations`
  ADD PRIMARY KEY (`negotiation_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Index pour la table `objects`
--
ALTER TABLE `objects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Index pour la table `object_photo`
--
ALTER TABLE `object_photo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `object_id` (`object_id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `stripe_id` (`stripe_id`),
  ADD KEY `payer_id` (`payer_id`),
  ADD KEY `payee_id` (`payee_id`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `proposition_de_prestations`
--
ALTER TABLE `proposition_de_prestations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Index pour la table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_request_id` (`parent_request_id`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`);

--
-- Index pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_reviews_reviewer` (`reviewer_id`),
  ADD KEY `idx_reviews_target` (`target_deliverer_id`),
  ADD KEY `idx_reviews_request` (`request_id`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Index pour la table `role_change_requests`
--
ALTER TABLE `role_change_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Index pour la table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`offered_service_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_type_id` (`service_type_id`);

--
-- Index pour la table `serviceavailability`
--
ALTER TABLE `serviceavailability`
  ADD PRIMARY KEY (`availability_id`);

--
-- Index pour la table `servicebooking`
--
ALTER TABLE `servicebooking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `servicelocation`
--
ALTER TABLE `servicelocation`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Index pour la table `servicetype`
--
ALTER TABLE `servicetype`
  ADD PRIMARY KEY (`service_type_id`);

--
-- Index pour la table `storage_boxes`
--
ALTER TABLE `storage_boxes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `subscriptionplan`
--
ALTER TABLE `subscriptionplan`
  ADD PRIMARY KEY (`plan_id`);

--
-- Index pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_stripe_id_unique` (`stripe_id`),
  ADD KEY `subscriptions_user_id_stripe_status_index` (`user_id`,`stripe_status`);

--
-- Index pour la table `subscription_items`
--
ALTER TABLE `subscription_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_items_stripe_id_unique` (`stripe_id`),
  ADD KEY `subscription_items_subscription_id_stripe_price_index` (`subscription_id`,`stripe_price`);

--
-- Index pour la table `supportingdocument`
--
ALTER TABLE `supportingdocument`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `tracking_events`
--
ALTER TABLE `tracking_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `deliverer_id` (`deliverer_id`);

--
-- Index pour la table `userrole`
--
ALTER TABLE `userrole`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nfc_code` (`nfc_code`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `users_stripe_id_index` (`stripe_id`);

--
-- Index pour la table `usersubscription`
--
ALTER TABLE `usersubscription`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Index pour la table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- Index pour la table `withdrawal`
--
ALTER TABLE `withdrawal`
  ADD PRIMARY KEY (`withdrawal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `box_assignments`
--
ALTER TABLE `box_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `deliveryassignment`
--
ALTER TABLE `deliveryassignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `deliveryroutes`
--
ALTER TABLE `deliveryroutes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `deliveryroutewaypoints`
--
ALTER TABLE `deliveryroutewaypoints`
  MODIFY `waypoint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `delivery_handoffs`
--
ALTER TABLE `delivery_handoffs`
  MODIFY `handoff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `justificatifs`
--
ALTER TABLE `justificatifs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `negotiations`
--
ALTER TABLE `negotiations`
  MODIFY `negotiation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `objects`
--
ALTER TABLE `objects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `object_photo`
--
ALTER TABLE `object_photo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `proposition_de_prestations`
--
ALTER TABLE `proposition_de_prestations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3015;

--
-- AUTO_INCREMENT pour la table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `role_change_requests`
--
ALTER TABLE `role_change_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `service`
--
ALTER TABLE `service`
  MODIFY `offered_service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `serviceavailability`
--
ALTER TABLE `serviceavailability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3005;

--
-- AUTO_INCREMENT pour la table `servicebooking`
--
ALTER TABLE `servicebooking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `servicelocation`
--
ALTER TABLE `servicelocation`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `servicetype`
--
ALTER TABLE `servicetype`
  MODIFY `service_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `storage_boxes`
--
ALTER TABLE `storage_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `subscriptionplan`
--
ALTER TABLE `subscriptionplan`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `subscription_items`
--
ALTER TABLE `subscription_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `supportingdocument`
--
ALTER TABLE `supportingdocument`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tracking_events`
--
ALTER TABLE `tracking_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `withdrawal`
--
ALTER TABLE `withdrawal`
  MODIFY `withdrawal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `box_assignments`
--
ALTER TABLE `box_assignments`
  ADD CONSTRAINT `fk_box_assign_box` FOREIGN KEY (`box_id`) REFERENCES `storage_boxes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_box_assign_req` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `deliveryassignment`
--
ALTER TABLE `deliveryassignment`
  ADD CONSTRAINT `deliveryassignment_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`),
  ADD CONSTRAINT `deliveryassignment_ibfk_2` FOREIGN KEY (`deliverer_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `deliveryroutes`
--
ALTER TABLE `deliveryroutes`
  ADD CONSTRAINT `fk_routes_user` FOREIGN KEY (`deliverer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `deliveryroutewaypoints`
--
ALTER TABLE `deliveryroutewaypoints`
  ADD CONSTRAINT `fk_waypoints_route` FOREIGN KEY (`route_id`) REFERENCES `deliveryroutes` (`route_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `delivery_handoffs`
--
ALTER TABLE `delivery_handoffs`
  ADD CONSTRAINT `fk_handoffs_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `deliveryassignment` (`assignment_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `invoicedetail`
--
ALTER TABLE `invoicedetail`
  ADD CONSTRAINT `invoicedetail_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`),
  ADD CONSTRAINT `invoicedetail_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`);

--
-- Contraintes pour la table `justificatifs`
--
ALTER TABLE `justificatifs`
  ADD CONSTRAINT `fk_justificatifs_role_request` FOREIGN KEY (`role_request_id`) REFERENCES `role_change_requests` (`request_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_justifs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`),
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`assignment_id`) REFERENCES `deliveryassignment` (`assignment_id`);

--
-- Contraintes pour la table `negotiations`
--
ALTER TABLE `negotiations`
  ADD CONSTRAINT `negotiations_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`),
  ADD CONSTRAINT `negotiations_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `negotiations_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `objects`
--
ALTER TABLE `objects`
  ADD CONSTRAINT `fk_objects_req` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `object_photo`
--
ALTER TABLE `object_photo`
  ADD CONSTRAINT `fk_object_photo_object` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`payer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payee_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_user` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`parent_request_id`) REFERENCES `requests` (`request_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_target_deliverer` FOREIGN KEY (`target_deliverer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `fk_service_type_id` FOREIGN KEY (`service_type_id`) REFERENCES `servicetype` (`service_type_id`),
  ADD CONSTRAINT `fk_service_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `servicebooking`
--
ALTER TABLE `servicebooking`
  ADD CONSTRAINT `servicebooking_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`offered_service_id`),
  ADD CONSTRAINT `servicebooking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `servicelocation`
--
ALTER TABLE `servicelocation`
  ADD CONSTRAINT `servicelocation_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`offered_service_id`);

--
-- Contraintes pour la table `supportingdocument`
--
ALTER TABLE `supportingdocument`
  ADD CONSTRAINT `supportingdocument_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `tracking_events`
--
ALTER TABLE `tracking_events`
  ADD CONSTRAINT `fk_events_deliverer` FOREIGN KEY (`deliverer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_events_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `userrole`
--
ALTER TABLE `userrole`
  ADD CONSTRAINT `userrole_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `userrole_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);

--
-- Contraintes pour la table `usersubscription`
--
ALTER TABLE `usersubscription`
  ADD CONSTRAINT `usersubscription_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `usersubscription_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscriptionplan` (`plan_id`);

--
-- Contraintes pour la table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `withdrawal`
--
ALTER TABLE `withdrawal`
  ADD CONSTRAINT `withdrawal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
