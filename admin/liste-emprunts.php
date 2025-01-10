<?php
require_once '../includes/header.php';
requireAdmin();

// Traiter le retour d'un livre
if (isset($_POST['retourner']) && isset($_POST['nolivre']) && isset($_POST['noutilisateur']) && isset($_POST['dateemprunt'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE emprunter 
            SET dateretoureffectif = CURRENT_DATE 
            WHERE nolivre = ? 
            AND noutilisateur = ? 
            AND dateemprunt = ?
            AND dateretoureffectif IS NULL
        ");
        
        if ($stmt->execute([
            $_POST['nolivre'],
            $_POST['noutilisateur'],
            $_POST['dateemprunt']
        ])) {
            $_SESSION['success_message'] = "Le livre a été marqué comme retourné.";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors du retour du livre : " . $e->getMessage();
    }
}

// Récupérer tous les emprunts
$query = "
    SELECT e.*, 
           l.titre as livre_titre,
           CONCAT(a.prenom, ' ', a.nom) as auteur_nom,
           CONCAT(u.prenom, ' ', u.nom) as utilisateur_nom,
           u.mel as utilisateur_email,
           DATEDIFF(CURRENT_DATE, e.dateemprunt) as jours_empruntes,
           CASE 
               WHEN e.dateretoureffectif IS NULL THEN DATEDIFF(e.dateretourprevu, CURRENT_DATE)
               ELSE 0
           END as jours_restants
    FROM emprunter e
    JOIN livre l ON e.nolivre = l.nolivre
    JOIN utilisateur u ON e.noutilisateur = u.noutilisateur
    LEFT JOIN auteur a ON l.noauteur = a.noauteur
    ORDER BY e.dateemprunt DESC";

try {
    $emprunts = $pdo->query($query)->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des emprunts : " . $e->getMessage();
    $emprunts = [];
}
?>

<div class="container py-5">
    <h1 class="mb-4">Gestion des emprunts</h1>

    <?php displayMessages(); ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Auteur</th>
                            <th>Emprunteur</th>
                            <th>Date d'emprunt</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emprunts as $emprunt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emprunt['livre_titre']); ?></td>
                                <td><?php echo htmlspecialchars($emprunt['auteur_nom']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($emprunt['utilisateur_nom']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($emprunt['utilisateur_email']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($emprunt['dateemprunt'])); ?></td>
                                <td>
                                    <?php if ($emprunt['dateretoureffectif']): ?>
                                        <span class="badge bg-success">Retourné le <?php echo date('d/m/Y', strtotime($emprunt['dateretoureffectif'])); ?></span>
                                    <?php else: ?>
                                        <?php if ($emprunt['jours_restants'] < 0): ?>
                                            <span class="badge bg-danger">En retard de <?php echo abs($emprunt['jours_restants']); ?> jours</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary"><?php echo $emprunt['jours_restants']; ?> jours restants</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$emprunt['dateretoureffectif']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="nolivre" value="<?php echo $emprunt['nolivre']; ?>">
                                            <input type="hidden" name="noutilisateur" value="<?php echo $emprunt['noutilisateur']; ?>">
                                            <input type="hidden" name="dateemprunt" value="<?php echo $emprunt['dateemprunt']; ?>">
                                            <button type="submit" name="retourner" class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>Marquer comme retourné
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

<?php require_once '../includes/footer.php'; ?>
