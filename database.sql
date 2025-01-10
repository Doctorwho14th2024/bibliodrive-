-- phpMyAdmin SQL Dump
-- Base de données : `biblio`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Structure de la table `auteur`
CREATE TABLE `auteur` (
  `noauteur` int(11) NOT NULL,
  `nom` varchar(40) NOT NULL,
  `prenom` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `emprunter`
CREATE TABLE `emprunter` (
  `mel` varchar(40) NOT NULL,
  `nolivre` int(11) NOT NULL,
  `dateemprunt` date NOT NULL,
  `dateretour` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `livre`
CREATE TABLE `livre` (
  `nolivre` int(11) NOT NULL,
  `noauteur` int(11) NOT NULL,
  `titre` varchar(128) NOT NULL,
  `isbn13` char(13) NOT NULL,
  `anneeparution` int(11) NOT NULL,
  `resume` text NOT NULL,
  `dateajout` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `utilisateur`
CREATE TABLE `utilisateur` (
  `mel` varchar(40) NOT NULL,
  `motdepasse` varchar(100) NOT NULL,
  `nom` varchar(40) NOT NULL,
  `prenom` varchar(40) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `ville` varchar(40) NOT NULL,
  `codepostal` int(11) NOT NULL,
  `profil` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index et clés
ALTER TABLE `auteur`
  ADD PRIMARY KEY (`noauteur`);

ALTER TABLE `emprunter`
  ADD PRIMARY KEY (`mel`,`nolivre`,`dateemprunt`),
  ADD KEY `fk_emprunter_livre` (`nolivre`);

ALTER TABLE `livre`
  ADD PRIMARY KEY (`nolivre`),
  ADD KEY `fk_livre_auteur` (`noauteur`);

ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`mel`);

-- AUTO_INCREMENT
ALTER TABLE `auteur`
  MODIFY `noauteur` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `livre`
  MODIFY `nolivre` int(11) NOT NULL AUTO_INCREMENT;

-- Contraintes
ALTER TABLE `emprunter`
  ADD CONSTRAINT `fk_emprunter_livre` FOREIGN KEY (`nolivre`) REFERENCES `livre` (`nolivre`),
  ADD CONSTRAINT `fk_emprunter_utilisateur` FOREIGN KEY (`mel`) REFERENCES `utilisateur` (`mel`);

ALTER TABLE `livre`
  ADD CONSTRAINT `fk_livre_auteur` FOREIGN KEY (`noauteur`) REFERENCES `auteur` (`noauteur`);

COMMIT;
