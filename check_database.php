<?php
require_once 'database.php';

try {
    // Obtenir la liste des tables
    $stmt = $pdo->query("SHOW TABLES FROM bibliodrive");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables existantes :</h2>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
    
    // Pour chaque table, afficher sa structure
    foreach ($tables as $table) {
        echo "<h3>Structure de la table $table :</h3>";
        $stmt = $pdo->query("SHOW CREATE TABLE bibliodrive.$table");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($createTable);
        echo "</pre>";
    }
    
} catch(PDOException $e) {
    echo "<h2>Erreur :</h2>";
    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>";
}
?>
