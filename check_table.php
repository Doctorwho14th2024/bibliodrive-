<?php
require_once 'includes/database.php';

try {
    $result = $pdo->query("DESCRIBE livre");
    echo "<pre>";
    print_r($result->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
