<?php
require_once 'includes/header.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validation
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";

    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE mel = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Cette adresse email est déjà utilisée";
        }
    }

    // Si pas d'erreurs, créer le compte
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, mel, motpasse, role) VALUES (?, ?, ?, ?, 'user')");
            
            if ($stmt->execute([$nom, $prenom, $email, $hashed_password])) {
                $_SESSION['success_message'] = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                header('Location: connexion.php');
                exit();
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la création du compte : " . $e->getMessage();
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">Inscription</h1>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom" required 
                                   value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required
                                   value="<?php echo isset($prenom) ? htmlspecialchars($prenom) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="8">
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Créer mon compte</button>
                            <a href="connexion.php" class="btn btn-link">Déjà inscrit ? Connectez-vous</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
