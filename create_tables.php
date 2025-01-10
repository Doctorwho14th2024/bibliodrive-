<?php
require_once 'includes/database.php';

try {
    // Désactiver les contraintes de clé étrangère
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Supprimer les tables si elles existent
    $tables = ['emprunter', 'livre', 'utilisateur', 'auteur'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    
    // 1. Créer la table auteur
    $sql_auteur = "CREATE TABLE `auteur` (
        `noauteur` int(11) NOT NULL AUTO_INCREMENT,
        `nom` varchar(40) NOT NULL,
        `prenom` varchar(40) NOT NULL,
        PRIMARY KEY (`noauteur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_auteur);
    
    // 2. Créer la table livre
    $sql_livre = "CREATE TABLE `livre` (
        `nolivre` int(11) NOT NULL AUTO_INCREMENT,
        `noauteur` int(11) NOT NULL,
        `titre` varchar(128) NOT NULL,
        `anneeparution` int(11) NOT NULL,
        `resume` text,
        `dateajout` date DEFAULT CURRENT_DATE,
        `image` VARCHAR(255),
        PRIMARY KEY (`nolivre`),
        KEY `fk_livre_auteur` (`noauteur`),
        CONSTRAINT `fk_livre_auteur` FOREIGN KEY (`noauteur`) REFERENCES `auteur` (`noauteur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_livre);
    
    // 3. Créer la table utilisateur
    $sql_utilisateur = "CREATE TABLE `utilisateur` (
        `noutilisateur` INT AUTO_INCREMENT PRIMARY KEY,
        `nom` VARCHAR(50) NOT NULL,
        `prenom` VARCHAR(50) NOT NULL,
        `mel` VARCHAR(100) UNIQUE NOT NULL,
        `motpasse` VARCHAR(255) NOT NULL,
        `role` ENUM('user', 'admin') DEFAULT 'user',
        `etat` TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_utilisateur);
    
    // 4. Créer la table emprunter
    $sql_emprunter = "CREATE TABLE `emprunter` (
        `noutilisateur` INT,
        `nolivre` INT,
        `dateemprunt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `dateretourprevu` DATE NOT NULL,
        `dateretoureffectif` DATE,
        FOREIGN KEY (noutilisateur) REFERENCES utilisateur(noutilisateur),
        FOREIGN KEY (nolivre) REFERENCES livre(nolivre),
        PRIMARY KEY (noutilisateur, nolivre, dateemprunt)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_emprunter);
    
    // Réactiver les contraintes de clé étrangère
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Créer le compte admin
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Supprimer l'ancien admin s'il existe
    $pdo->exec("DELETE FROM utilisateur WHERE mel = 'admin@bibliotech.fr'");
    
    // Créer le nouvel admin
    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, mel, motpasse, role, etat) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'System', 'admin@bibliotech.fr', $admin_password, 'admin', 1]);

    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<h2>Base de données initialisée avec succès !</h2>";
    echo "<p>Compte administrateur créé :</p>";
    echo "<p>Email : admin@bibliotech.fr</p>";
    echo "<p>Mot de passe : admin123</p>";
    echo "<p><a href='connexion.php'>Se connecter</a></p>";
    echo "</div>";

} catch(PDOException $e) {
    die("Erreur lors de la création des tables : " . $e->getMessage());
}
?>
