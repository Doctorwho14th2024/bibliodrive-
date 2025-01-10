<?php
session_start();
require_once 'database.php';
require_once 'includes/authentification.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$success = '';
$error = '';

// Supprimer un livre du panier
if (isset($_POST['supprimer']) && isset($_POST['nolivre'])) {
    $nolivre = (int)$_POST['nolivre'];
    $key = array_search($nolivre, $_SESSION['panier']);
    if ($key !== false) {
        unset($_SESSION['panier'][$key]);
        $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexer le tableau
        $success = "Livre retiré du panier";
    }
}

// Valider l'emprunt
if (isset($_POST['emprunter']) && !empty($_SESSION['panier'])) {
    try {
        $pdo->beginTransaction();
        
        // Récupérer le noutilisateur à partir de l'email
        $stmt = $pdo->prepare("SELECT noutilisateur FROM utilisateur WHERE mel = ?");
        $stmt->execute([$_SESSION['mel']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("Utilisateur non trouvé.");
        }
        
        $date_emprunt = date('Y-m-d H:i:s'); // Format datetime complet
        $noutilisateur = $user['noutilisateur'];
        
        // Vérifier si l'utilisateur n'a pas déjà emprunté ces livres
        $stmt = $pdo->prepare("SELECT nolivre FROM emprunter WHERE noutilisateur = ? AND dateretoureffectif IS NULL");
        $stmt->execute([$noutilisateur]);
        $emprunts_actuels = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $livres_deja_empruntes = array_intersect($emprunts_actuels, $_SESSION['panier']);
        
        if (!empty($livres_deja_empruntes)) {
            throw new Exception("Vous avez déjà emprunté certains de ces livres.");
        }
        
        // Vérifier si l'emprunt existe déjà
        $stmt = $pdo->prepare("INSERT INTO emprunter (noutilisateur, nolivre, dateemprunt) 
                              SELECT ?, ?, ? 
                              WHERE NOT EXISTS (
                                  SELECT 1 FROM emprunter 
                                  WHERE noutilisateur = ? 
                                  AND nolivre = ? 
                                  AND dateemprunt = ?
                              )");
        
        foreach ($_SESSION['panier'] as $nolivre) {
            $stmt->execute([
                $noutilisateur, 
                $nolivre, 
                $date_emprunt,
                $noutilisateur,
                $nolivre,
                $date_emprunt
            ]);
        }
        
        $pdo->commit();
        $_SESSION['panier'] = []; // Vider le panier
        $success = "Vos livres ont été empruntés avec succès !";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Récupérer les informations des livres dans le panier
$livres = [];
if (!empty($_SESSION['panier'])) {
    $placeholders = str_repeat('?,', count($_SESSION['panier']) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT l.*, a.nom as nom_auteur, a.prenom as prenom_auteur 
        FROM livre l 
        JOIN auteur a ON l.noauteur = a.noauteur 
        WHERE l.nolivre IN ($placeholders)
    ");
    $stmt->execute($_SESSION['panier']);
    $livres = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">Mon Panier</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($livres)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                            <p class="lead text-muted">Votre panier est vide</p>
                            <a href="catalogue.php" class="btn btn-primary">
                                <i class="fas fa-book me-2"></i>Parcourir le catalogue
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group mb-4">
                            <?php foreach ($livres as $livre): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($livre['titre']); ?></h5>
                                            <p class="mb-1 text-muted">
                                                Par <?php echo htmlspecialchars($livre['prenom_auteur'] . ' ' . $livre['nom_auteur']); ?>
                                            </p>
                                            <?php if (isset($livre['isbn13'])): ?>
                                                <small>ISBN: <?php echo htmlspecialchars($livre['isbn13']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <form method="post" class="ms-3">
                                            <input type="hidden" name="nolivre" value="<?php echo $livre['nolivre']; ?>">
                                            <button type="submit" name="supprimer" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <form method="post" class="d-flex gap-2">
                            <a href="catalogue.php" class="btn btn-outline-secondary flex-grow-1">
                                <i class="fas fa-arrow-left me-2"></i>Continuer mes recherches
                            </a>
                            <button type="submit" name="emprunter" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-check me-2"></i>Valider l'emprunt
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
