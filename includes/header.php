<?php
require_once 'database.php';
require_once 'authentification.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - <?= $page_title ?? 'Accueil' ?></title>
    <meta name="description" content="BiblioTech - Votre bibliothèque numérique cyberpunk">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/biblio/assets/styles.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/biblio/">
                <i class="fas fa-book-reader me-2"></i>BiblioTech
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/biblio/">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/biblio/catalogue.php">Catalogue</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/biblio/mes-emprunts.php">Mes Emprunts</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/biblio/admin/">Administration</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['prenom']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/biblio/mon-compte.php">Mon Profil</a></li>
                                <li><a class="dropdown-item" href="/biblio/mes-emprunts.php">Mes Emprunts</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/biblio/?logout">Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/biblio/connexion.php" class="btn btn-outline-light me-2">Connexion</a>
                        <a href="/biblio/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php displayMessages(); ?>
