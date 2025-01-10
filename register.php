<?php
session_start();
require_once 'database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mel = trim($_POST['email'] ?? '');
    $motpasse = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($nom) || empty($prenom) || empty($mel) || empty($motpasse) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($mel, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif ($motpasse !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($motpasse) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier si l'email existe déjà
        $query = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE mel = ?");
        $query->execute([$mel]);
        if ($query->fetchColumn() > 0) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            // Hasher le mot de passe
            $hashed_password = password_hash($motpasse, PASSWORD_DEFAULT);
            
            // Insérer le nouvel utilisateur
            $query = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, mel, motpasse, dateinscription) VALUES (?, ?, ?, ?, CURRENT_DATE)");
            try {
                $query->execute([$nom, $prenom, $mel, $hashed_password]);
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                
                // Rediriger vers la page de connexion après 2 secondes
                header("refresh:2;url=login.php");
            } catch (PDOException $e) {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Inscription</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="post" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>

                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">S'inscrire</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
