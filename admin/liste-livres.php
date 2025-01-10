<?php
require_once '../includes/header.php';
requireAdmin();

// Suppression d'un livre
if (isset($_POST['supprimer']) && isset($_POST['nolivre'])) {
    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Vérifier si le livre n'est pas actuellement emprunté
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as nb_emprunts 
            FROM emprunter 
            WHERE nolivre = ? AND dateretoureffectif IS NULL
        ");
        $stmt->execute([$_POST['nolivre']]);
        $emprunts = $stmt->fetch();

        if ($emprunts['nb_emprunts'] > 0) {
            $_SESSION['error_message'] = "Impossible de supprimer ce livre car il est actuellement emprunté.";
            $pdo->rollBack();
        } else {
            // Supprimer d'abord tous les enregistrements d'emprunts passés
            $stmt = $pdo->prepare("DELETE FROM emprunter WHERE nolivre = ?");
            $stmt->execute([$_POST['nolivre']]);

            // Récupérer l'auteur du livre
            $stmt = $pdo->prepare("SELECT noauteur FROM livre WHERE nolivre = ?");
            $stmt->execute([$_POST['nolivre']]);
            $livre_info = $stmt->fetch();
            $noauteur = $livre_info['noauteur'];

            // Supprimer l'image associée si elle existe
            $stmt = $pdo->prepare("SELECT image FROM livre WHERE nolivre = ?");
            $stmt->execute([$_POST['nolivre']]);
            $livre = $stmt->fetch();
            
            if ($livre['image'] && file_exists("../" . $livre['image'])) {
                unlink("../" . $livre['image']);
            }

            // Supprimer le livre
            $stmt = $pdo->prepare("DELETE FROM livre WHERE nolivre = ?");
            if ($stmt->execute([$_POST['nolivre']])) {
                // Vérifier si l'auteur a d'autres livres
                $stmt = $pdo->prepare("SELECT COUNT(*) as nb_livres FROM livre WHERE noauteur = ?");
                $stmt->execute([$noauteur]);
                $result = $stmt->fetch();

                // Si l'auteur n'a plus de livres, le supprimer
                if ($result['nb_livres'] == 0) {
                    $stmt = $pdo->prepare("DELETE FROM auteur WHERE noauteur = ?");
                    $stmt->execute([$noauteur]);
                }

                $pdo->commit();
                $_SESSION['success_message'] = "Le livre a été supprimé avec succès.";
            } else {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Erreur lors de la suppression du livre.";
            }
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la suppression du livre : " . $e->getMessage();
    }
}

// Récupérer la liste des livres
$query = "
    SELECT l.*, 
           a.nom as auteur_nom, 
           a.prenom as auteur_prenom,
           (SELECT COUNT(*) FROM emprunter e WHERE e.nolivre = l.nolivre AND e.dateretoureffectif IS NULL) as est_emprunte
    FROM livre l
    LEFT JOIN auteur a ON l.noauteur = a.noauteur
    ORDER BY l.dateajout DESC";

try {
    $livres = $pdo->query($query)->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des livres : " . $e->getMessage();
    $livres = [];
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des livres</h1>
        <a href="ajouter-livre.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Ajouter un livre
        </a>
    </div>

    <?php displayMessages(); ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Couverture</th>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Année</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livres as $livre): ?>
                            <tr>
                                <td style="width: 100px;">
                                    <?php if ($livre['image']): ?>
                                        <img src="/biblio/<?php echo htmlspecialchars($livre['image']); ?>" 
                                             class="img-thumbnail" 
                                             alt="Couverture"
                                             style="max-width: 80px;">
                                    <?php else: ?>
                                        <div class="bg-light rounded p-2 text-center">
                                            <i class="fas fa-book fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($livre['titre']); ?>
                                    <?php if ($livre['resume']): ?>
                                        <button type="button" 
                                                class="btn btn-link btn-sm p-0 ms-2" 
                                                data-bs-toggle="popover" 
                                                data-bs-trigger="hover" 
                                                title="Résumé" 
                                                data-bs-content="<?php echo htmlspecialchars(str_replace('"', '&quot;', $livre['resume'])); ?>"
                                                data-bs-html="true">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']); ?></td>
                                <td><?php echo htmlspecialchars($livre['anneeparution']); ?></td>
                                <td>
                                    <?php if ($livre['est_emprunte'] > 0): ?>
                                        <span class="badge bg-warning text-dark">Emprunté</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="modifier-livre.php?id=<?php echo $livre['nolivre']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($livre['est_emprunte'] == 0): ?>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?');">
                                            <input type="hidden" name="nolivre" value="<?php echo $livre['nolivre']; ?>">
                                            <button type="submit" name="supprimer" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Initialiser les popovers Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
