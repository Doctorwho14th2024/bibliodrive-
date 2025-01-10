<?php
// Créer les dossiers nécessaires
$directories = [
    'assets',
    'assets/images',
    'assets/images/covers'
];

$base_path = __DIR__;
foreach ($directories as $dir) {
    $path = $base_path . '/' . $dir;
    if (!file_exists($path)) {
        if (mkdir($path, 0777, true)) {
            echo "Dossier créé : " . $path . "<br>";
        } else {
            echo "Erreur lors de la création du dossier : " . $path . "<br>";
        }
    } else {
        echo "Le dossier existe déjà : " . $path . "<br>";
    }
}

// Définir les permissions appropriées
$covers_path = $base_path . '/assets/images/covers';
chmod($covers_path, 0777);

echo "<br>Configuration terminée !";
?>
