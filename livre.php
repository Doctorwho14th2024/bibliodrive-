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
    // Récupérer les informations du livre avec son statut d'emprunt
    $stmt = $pdo->prepare("
        SELECT l.*, 
               a.nom as auteur_nom, 
               a.prenom as auteur_prenom,
               (SELECT COUNT(*) FROM emprunter e 
                WHERE e.nolivre = l.nolivre 
                AND e.dateretoureffectif IS NULL) as est_emprunte,
               (SELECT dateretourprevu FROM emprunter e 
                WHERE e.nolivre = l.nolivre 
                AND e.dateretoureffectif IS NULL 
                ORDER BY e.dateemprunt DESC 
                LIMIT 1) as date_retour_prevue
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

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération du livre : " . $e->getMessage();
    header('Location: catalogue.php');
    exit();
}

// Récupérer l'historique des emprunts de ce livre
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.nom as emprunteur_nom, u.prenom as emprunteur_prenom
        FROM emprunter e
        JOIN utilisateur u ON e.noutilisateur = u.noutilisateur
        WHERE e.nolivre = ?
        ORDER BY e.dateemprunt DESC
        LIMIT 5
    ");
    $stmt->execute([$nolivre]);
    $historique_emprunts = $stmt->fetchAll();
} catch (PDOException $e) {
    $historique_emprunts = [];
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Détails du livre -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4 mb-md-0">
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
                            <h1 class="h2 mb-3"><?php echo htmlspecialchars($livre['titre']); ?></h1>
                            <p class="text-muted mb-3">
                                par <?php echo htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']); ?>
                            </p>
                            <?php if ($livre['resume']): ?>
                                <h5 class="mb-2">Résumé</h5>
                                <p class="card-text text-justify" style="white-space: pre-wrap;"><?php echo htmlspecialchars($livre['resume']); ?></p>
                            <?php endif; ?>
                            <div class="mt-4">
                                <p class="mb-2">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Année de parution : <?php echo htmlspecialchars($livre['anneeparution']); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-clock me-2"></i>
                                    Ajouté le : <?php echo date('d/m/Y', strtotime($livre['dateajout'])); ?>
                                </p>
                                <div class="mt-4">
                                    <?php if ($livre['est_emprunte'] > 0): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            Ce livre est actuellement emprunté
                                            <?php if ($livre['date_retour_prevue']): ?>
                                                <br>Retour prévu le : <?php echo date('d/m/Y', strtotime($livre['date_retour_prevue'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <a href="emprunter.php?id=<?php echo $livre['nolivre']; ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-book-reader me-2"></i>Emprunter ce livre
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des emprunts -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Historique des emprunts
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($historique_emprunts)): ?>
                        <p class="text-muted mb-0">Aucun emprunt enregistré pour ce livre.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($historique_emprunts as $emprunt): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker <?php echo $emprunt['dateretoureffectif'] ? 'bg-success' : 'bg-primary'; ?>"></div>
                                    <div class="timeline-content">
                                        <p class="mb-1">
                                            <strong>
                                                <?php echo htmlspecialchars($emprunt['emprunteur_prenom'] . ' ' . $emprunt['emprunteur_nom']); ?>
                                            </strong>
                                        </p>
                                        <p class="text-muted mb-0">
                                            Du <?php echo date('d/m/Y', strtotime($emprunt['dateemprunt'])); ?>
                                            <?php if ($emprunt['dateretoureffectif']): ?>
                                                au <?php echo date('d/m/Y', strtotime($emprunt['dateretoureffectif'])); ?>
                                            <?php else: ?>
                                                <br><span class="badge bg-primary">En cours</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: -24px;
    top: 12px;
    width: 1px;
    height: calc(100% - 12px);
    background-color: #dee2e6;
}

.timeline-content {
    padding-left: 10px;
}
</style>

<?php require_once 'includes/footer.php'; ?>
