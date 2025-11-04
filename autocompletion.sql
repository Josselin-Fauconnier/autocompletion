-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 04 nov. 2025 à 07:53
-- Version du serveur : 8.4.3
-- Version de PHP : 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `autocompletion`
--

-- --------------------------------------------------------

--
-- Structure de la table `animaux`
--

CREATE TABLE `animaux` (
  `id` int UNSIGNED NOT NULL,
  `nom_fr` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_latin` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` enum('mammifere','reptile','poisson','oiseau','insecte') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `animaux`
--

INSERT INTO `animaux` (`id`, `nom_fr`, `nom_latin`, `categorie`) VALUES
(1, 'Loup gris', 'Canis lupus', 'mammifere'),
(2, 'Chat domestique', 'Felis catus', 'mammifere'),
(3, 'Dauphin commun', 'Delphinus delphis', 'mammifere'),
(4, 'Éléphant d’Afrique', 'Loxodonta africana', 'mammifere'),
(5, 'Renard roux', 'Vulpes vulpes', 'mammifere'),
(6, 'Crocodile du Nil', 'Crocodylus niloticus', 'reptile'),
(7, 'Iguane vert', 'Iguana iguana', 'reptile'),
(8, 'Python royal', 'Python regius', 'reptile'),
(9, 'Tortue d’Hermann', 'Testudo hermanni', 'reptile'),
(10, 'Caméléon panthère', 'Furcifer pardalis', 'reptile'),
(11, 'Saumon atlantique', 'Salmo salar', 'poisson'),
(12, 'Thon rouge', 'Thunnus thynnus', 'poisson'),
(13, 'Poisson-clown', 'Amphiprion ocellaris', 'poisson'),
(14, 'Carpe commune', 'Cyprinus carpio', 'poisson'),
(15, 'Requin blanc', 'Carcharodon carcharias', 'poisson'),
(16, 'Aigle royal', 'Aquila chrysaetos', 'oiseau'),
(17, 'Moineau domestique', 'Passer domesticus', 'oiseau'),
(18, 'Pingouin torda', 'Alca torda', 'oiseau'),
(19, 'Canari sauvage', 'Serinus canaria', 'oiseau'),
(20, 'Héron cendré', 'Ardea cinerea', 'oiseau'),
(21, 'Abeille domestique', 'Apis mellifera', 'insecte'),
(22, 'Fourmi noire', 'Lasius niger', 'insecte'),
(23, 'Papillon monarque', 'Danaus plexippus', 'insecte'),
(24, 'Mante religieuse', 'Mantis religiosa', 'insecte'),
(25, 'Scarabée rhinocéros', 'Oryctes nasicornis', 'insecte');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `animaux`
--
ALTER TABLE `animaux`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nom` (`nom_fr`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `animaux`
--
ALTER TABLE `animaux`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
