<?php
try {
    $host = 'localhost';
    $dbname = 'bibliodrive';
    $username = 'root';
    $password = '';
    
    // Options  pour une meilleure gestion des erreurs
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

} catch(PDOException $e) {
    // En cas d'erreur, on vérifie si la base de données existe
    if($e->getCode() == 1049) {
        // La base de données n'existe pas, on la crée
        try {
            $tempPdo = new PDO("mysql:host=$host", $username, $password);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Reconnexion avec la nouvelle base de données
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
            
            // Création des tables si elles n'existent pas
            require_once __DIR__ . '/../create_tables.php';
            
        } catch(PDOException $e2) {
            die("Erreur de connexion : " . $e2->getMessage());
        }
    } else {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
?>
