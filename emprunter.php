<?php
require_once 'includes/header.php';
requireLogin();

// Vérifier si un ID de livre est fourni
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Aucun livre spécifié.";
    header('Location: catalogue.php');
    exit();
}

$nolivre = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($nolivre === false) {
    $_SESSION['error_message'] = "ID de livre invalide.";
    header('Location: catalogue.php');
    exit();
}

try {
    // Vérifier si le livre existe et n'est pas déjà emprunté
    $stmt = $pdo->prepare("
        SELECT l.*, a.nom as auteur_nom, a.prenom as auteur_prenom,
        (SELECT COUNT(*) FROM emprunter e 
         WHERE e.nolivre = l.nolivre 
         AND e.dateretoureffectif IS NULL) as est_emprunte
        FROM livre l
        LEFT JOIN auteur a ON l.noauteur = a.noauteur
        WHERE l.nolivre = ?
    ");
    $stmt->execute([$nolivre]);
    $livre = $stmt->fetch();

    if (!$livre) {
        $_SESSION['error_message'] = "Livre non trouvé.";
        header('Location: catalogue.php');
        exit();
    }

    if ($livre['est_emprunte'] > 0) {
        $_SESSION['error_message'] = "Ce livre est déjà emprunté.";
        header('Location: catalogue.php');
        exit();
    }

    // Vérifier si l'utilisateur n'a pas déjà 3 emprunts en cours
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as nb_emprunts 
        FROM emprunter 
        WHERE noutilisateur = ? 
        AND dateretoureffectif IS NULL
    ");
    $stmt->execute([$_SESSION['noutilisateur']]);
    $nb_emprunts = $stmt->fetch()['nb_emprunts'];

    if ($nb_emprunts >= 3) {
        $_SESSION['error_message'] = "Vous avez déjà atteint la limite de 3 emprunts simultanés.";
        header('Location: catalogue.php');
        exit();
    }

    // Si formulaire soumis, procéder à l'emprunt
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Calculer la date de retour prévue (14 jours)
        $dateretourprevu = date('Y-m-d', strtotime('+14 days'));

        $stmt = $pdo->prepare("
            INSERT INTO emprunter (noutilisateur, nolivre, dateemprunt, dateretourprevu) 
            VALUES (?, ?, NOW(), ?)
        ");

        if ($stmt->execute([$_SESSION['noutilisateur'], $nolivre, $dateretourprevu])) {
            $_SESSION['success_message'] = "Livre emprunté avec succès ! Date de retour prévue : " . date('d/m/Y', strtotime($dateretourprevu));
            header('Location: mes-emprunts.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'emprunt du livre.";
        }
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de l'emprunt : " . $e->getMessage();
    header('Location: catalogue.php');
    exit();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Emprunter un livre</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if ($livre['image']): ?>
                                <img src="<?php echo htmlspecialchars($livre['image']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="Couverture">
                            <?php else: ?>
                                <div class="bg-light rounded p-4 text-center">
                                    <i class="fas fa-book fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h5>
                            <p class="text-muted">
                                par <?php echo htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']); ?>
                            </p>
                            <?php if ($livre['resume']): ?>
                                <p class="card-text text-justify" style="white-space: pre-wrap;"><?php echo htmlspecialchars($livre['resume']); ?></p>
                            <?php endif; ?>
                            <p class="card-text">
                                <small class="text-muted">
                                    Année de parution : <?php echo htmlspecialchars($livre['anneeparution']); ?>
                                </small>
                            </p>
                            <form method="post" class="mt-4">
                                <p class="text-muted mb-3">
                                    En empruntant ce livre, vous vous engagez à le retourner dans un délai de 14 jours.
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-book-reader me-2"></i>Confirmer l'emprunt
                                </button>
                                <a href="catalogue.php" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Retour au catalogue
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
