CREATE DATABASE IF NOT EXISTS `kze29701` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `kze29701`;

SET FOREIGN_KEY_CHECKS = 0;

-- Table : groupe

DROP TABLE IF EXISTS groupe;
CREATE TABLE `groupe` (
  `id_group` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text,
  `token` varchar(8) NOT NULL,
  `statut` int(1) NOT NULL DEFAULT '0',
  `taille` int(1) NOT NULL,
  `next_prelevement` date NULL,
  PRIMARY KEY (id_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `offer`
--
DROP TABLE IF EXISTS offer;
CREATE TABLE IF NOT EXISTS `offer` (
  `id_offer` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` text,
  `hangs` text,
  `price` int(2) NOT NULL,
  `image` text,
  `id_stripe` text,
  PRIMARY KEY (id_offer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `offer` (`id_offer`, `name`, `description`, `hangs`, `price`, `image`, `id_stripe`) VALUES
(1, 'Netflix', NULL, NULL, 15, 'choice-netflix.png', 'prod_EbFEkSfCCtPwci'),
(2, 'Spotify', NULL, NULL, 15, 'spotify.png', 'prod_EbFJimvGrCREHS'),
(3, 'Deezer', NULL, NULL, 15, 'deezer.png', 'prod_EbFLjUmIiI1oCQ'),
(4, 'Prime', NULL, NULL, 15, 'prime.png', 'prod_EbFEkSfCCtPwci');

-- --------------------------------------------------------

--
-- Structure de la table `offer_group`
--

DROP TABLE IF EXISTS offer_group;
CREATE TABLE `offer_group` (
  `id_offer_group` int(11) NOT NULL AUTO_INCREMENT,
  `id_offer` int(11) NOT NULL,
  `id_group` int(11) NOT NULL,
  INDEX IX_offer_group_id_offer(id_offer),
  INDEX IX_offer_group_id_group(id_group),

  PRIMARY KEY (id_offer_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contraintes pour la table `offer_group`
--
ALTER TABLE `offer_group`
  ADD CONSTRAINT `fk_id_group2` FOREIGN KEY (`id_group`) REFERENCES `groupe` (`id_group`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Structure de la table `plan`
--

DROP TABLE IF EXISTS plan;
CREATE TABLE IF NOT EXISTS `plan` (
  `id_plan` int(11) NOT NULL AUTO_INCREMENT,
  `id_stripe` varchar(11) NOT NULL,
  `id_offer` int(11) NOT NULL,
  `taille` int(1) NOT NULL,
  PRIMARY KEY (id_plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `plan` (`id_plan`, `id_stripe`, `id_offer`, `taille`) VALUES
(1, 'ntflx2p', 1, 2),
(2, 'ntflx3p', 1, 3),
(5, 'ntflx4p', 1, 4),
(6, 'sptf2p', 2, 2),
(7, 'sptf3p', 2, 3),
(8, 'sptf4p', 2, 4),
(9, 'dz2p', 3, 2),
(10, 'dz3p', 3, 3),
(11, 'dz4p', 3, 4),
(12, 'prm2p', 4, 2),
(13, 'prm3p', 4, 3),
(14, 'prm4p', 4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS user;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `surname` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `email` text,
  `password` text NOT NULL,
  `postal_code` int(5) DEFAULT NULL,
  `phone_number` int(9) DEFAULT NULL,
  `id_stripe` varchar(60) DEFAULT NULL,
  `admin` int(1) DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Structure de la table `user_group`
--

DROP TABLE IF EXISTS user_group;
CREATE TABLE `user_group` (
  `id_user_group` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_group` int(11) NOT NULL,
  `role` int(1) NOT NULL,
  PRIMARY KEY (id_user_group),
  INDEX IX_id_user(id_user),
  INDEX IX_id_group(id_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contraintes pour la table `user_group`
--
ALTER TABLE `user_group`
  ADD CONSTRAINT `fk_id_group` FOREIGN KEY (`id_group`) REFERENCES `groupe` (`id_group`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_id_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
