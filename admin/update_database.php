<?php
require_once '../includes/database.php';
require_once '../includes/header.php';
requireAdmin();

try {
    // Vérifier si la colonne existe déjà
    $stmt = $pdo->prepare("SHOW COLUMNS FROM utilisateur LIKE 'etat'");
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        // Ajouter la colonne etat
        $pdo->exec("ALTER TABLE utilisateur ADD COLUMN etat TINYINT(1) NOT NULL DEFAULT 1");
        echo "<div class='alert alert-success'>La colonne 'etat' a été ajoutée avec succès.</div>";
    } else {
        echo "<div class='alert alert-info'>La colonne 'etat' existe déjà.</div>";
    }

    // Rediriger vers la liste des utilisateurs après 3 secondes
    header("refresh:3;url=liste-utilisateurs.php");
    
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur lors de la mise à jour de la base de données : " . $e->getMessage() . "</div>";
}

require_once '../includes/footer.php';
?>
