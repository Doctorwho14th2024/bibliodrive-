<?php
require_once 'includes/database.php';

try {
    // Ajouter la colonne image si elle n'existe pas
    $pdo->exec("ALTER TABLE livre ADD COLUMN IF NOT EXISTS image VARCHAR(255)");
    echo "Colonne image ajoutée avec succès !";
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
