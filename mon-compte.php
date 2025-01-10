<?php
session_start();
require_once 'database.php';
require_once 'includes/authentification.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Récupérer les informations de l'utilisateur
$user = getCurrentUser();
$success = '';
$error = '';

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($nom) || empty($prenom)) {
        $error = "Le nom et le prénom sont obligatoires.";
    } else {
        try {
            // Mise à jour du profil
            $query = $pdo->prepare("UPDATE utilisateur SET nom = ?, prenom = ? WHERE mel = ?");
            $query->execute([$nom, $prenom, $_SESSION['user_id']]);

            // Si l'utilisateur souhaite changer son mot de passe
            if (!empty($current_password)) {
                if (password_verify($current_password, $user['motpasse'])) {
                    if (empty($new_password) || empty($confirm_password)) {
                        $error = "Veuillez remplir tous les champs du mot de passe.";
                    } elseif ($new_password !== $confirm_password) {
                        $error = "Les nouveaux mots de passe ne correspondent pas.";
                    } elseif (strlen($new_password) < 6) {
                        $error = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $query = $pdo->prepare("UPDATE utilisateur SET motpasse = ? WHERE mel = ?");
                        $query->execute([$hashed_password, $_SESSION['user_id']]);
                        $success = "Profil et mot de passe mis à jour avec succès !";
                    }
                } else {
                    $error = "Le mot de passe actuel est incorrect.";
                }
            } else {
                $success = "Profil mis à jour avec succès !";
            }

            // Rafraîchir les informations de l'utilisateur
            $user = getCurrentUser();
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];

        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de la mise à jour du profil.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Mon Profil</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="post" action="" class="needs-validation" novalidate>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($user['mel']); ?>" disabled>
                            <div class="form-text">L'adresse email ne peut pas être modifiée.</div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-4">Changer le mot de passe</h4>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Mettre à jour le profil</button>
                            <a href="index.php" class="btn btn-outline-secondary">Retour à l'accueil</a>
                        </div>
                    </form>

                    <?php if ($user['role'] === 'admin'): ?>
                    <div class="mt-4 p-4 bg-light rounded">
                        <h4 class="mb-3">Options d'administrateur</h4>
                        <a href="admin/dashboard.php" class="btn btn-dark">Accéder au tableau de bord admin</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
