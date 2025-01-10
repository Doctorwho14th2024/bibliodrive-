<?php
require_once '../includes/header.php';
requireAdmin(); // S'assure que seuls les administrateurs peuvent accéder

// Redirection vers le dashboard
header('Location: dashboard.php');
exit();
?>
