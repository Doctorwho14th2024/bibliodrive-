<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

function isLoggedIn() {
    return isset($_SESSION['mel']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
        header('Location: /biblio/connexion.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error_message'] = "Accès réservé aux administrateurs.";
        header('Location: /biblio/index.php');
        exit();
    }
}

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Veuillez remplir tous les champs.";
        header('Location: /biblio/connexion.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE mel = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['motpasse'])) {
        $_SESSION['mel'] = $user['mel'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['noutilisateur'] = $user['noutilisateur'];
        $_SESSION['success_message'] = "Bienvenue " . $user['prenom'] . " " . $user['nom'] . " !";
        header('Location: /biblio/index.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Email ou mot de passe incorrect.";
        header('Location: /biblio/connexion.php');
        exit();
    }
}

// Traitement de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /biblio/index.php');
    exit();
}

// Fonction pour afficher les messages
function displayMessages() {
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['error_message']);
    }
    
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success_message']);
    }
}

// Fonction pour récupérer les informations de l'utilisateur connecté
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE mel = ?");
    $stmt->execute([$_SESSION['mel']]);
    return $stmt->fetch();
}

// Affichage des informations de l'utilisateur connecté
if (isLoggedIn()) {
    $user = getCurrentUser();
    ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?>
            </h5>
            <div class="d-grid gap-2">
                <a href="mon-compte.php" class="btn btn-primary">
                    <i class="fas fa-user-cog me-2"></i>Mon profil
                </a>
                <a href="?logout" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a>
            </div>
        </div>
    </div>
    <?php
}

displayMessages();
?>
