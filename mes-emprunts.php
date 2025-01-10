<?php
session_start();
require_once 'database.php';
require_once 'includes/authentification.php';
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$success = '';
$error = '';

// Gérer le retour d'un livre
if (isset($_POST['retourner']) && isset($_POST['nolivre'])) {
    try {
        $nolivre = (int)$_POST['nolivre'];
        $user_mel = $_SESSION['noutilisateur'];
        
        $stmt = $pdo->prepare("
            UPDATE emprunter 
            SET dateretoureffectif = CURRENT_DATE 
            WHERE noutilisateur = ? AND nolivre = ? AND dateretoureffectif IS NULL
        ");
        
        if ($stmt->execute([$user_mel, $nolivre])) {
            if ($stmt->rowCount() > 0) {
                $success = "Le livre a été retourné avec succès !";
            } else {
                $error = "Impossible de retourner ce livre. Il n'est peut-être pas emprunté.";
            }
        } else {
            $error = "Une erreur est survenue lors du retour du livre.";
        }
    } catch (PDOException $e) {
        $error = "Erreur lors du retour du livre : " . $e->getMessage();
    }
}

// Récupérer les emprunts en cours
$stmt = $pdo->prepare("
    SELECT e.*, 
           l.titre, 
           a.nom as auteur_nom, 
           a.prenom as auteur_prenom,
           DATEDIFF(CURRENT_DATE, e.dateemprunt) as jours_empruntes,
           DATEDIFF(e.dateretourprevu, CURRENT_DATE) as jours_restants
    FROM emprunter e
    JOIN livre l ON e.nolivre = l.nolivre
    LEFT JOIN auteur a ON l.noauteur = a.noauteur
    WHERE e.noutilisateur = ? AND e.dateretoureffectif IS NULL
    ORDER BY e.dateemprunt DESC
");
$stmt->execute([$_SESSION['noutilisateur']]);
$empruntsEnCours = $stmt->fetchAll();

// Récupérer l'historique des emprunts
$stmt = $pdo->prepare("
    SELECT e.*, 
           l.titre, 
           a.nom as auteur_nom, 
           a.prenom as auteur_prenom,
           DATEDIFF(e.dateretoureffectif, e.dateemprunt) as duree_emprunt
    FROM emprunter e
    JOIN livre l ON e.nolivre = l.nolivre
    LEFT JOIN auteur a ON l.noauteur = a.noauteur
    WHERE e.noutilisateur = ? AND e.dateretoureffectif IS NOT NULL
    ORDER BY e.dateemprunt DESC
");
$stmt->execute([$_SESSION['noutilisateur']]);
$historiqueEmprunts = $stmt->fetchAll();
?>

<div class="container py-5">
    <h1 class="mb-4">Mes emprunts</h1>

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

    <!-- Emprunts en cours -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Emprunts en cours</h2>
        </div>
        <div class="card-body">
            <?php if (empty($empruntsEnCours)): ?>
                <p class="text-muted mb-0">Vous n'avez aucun emprunt en cours.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Auteur</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour prévue</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empruntsEnCours as $emprunt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emprunt['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($emprunt['auteur_prenom'] . ' ' . $emprunt['auteur_nom']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($emprunt['dateemprunt'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($emprunt['dateretourprevu'])); ?></td>
                                    <td>
                                        <?php if ($emprunt['jours_restants'] < 0): ?>
                                            <span class="badge bg-danger">En retard de <?php echo abs($emprunt['jours_restants']); ?> jours</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $emprunt['jours_restants']; ?> jours restants</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="nolivre" value="<?php echo $emprunt['nolivre']; ?>">
                                            <button type="submit" name="retourner" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-undo me-1"></i>Retourner
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historique des emprunts -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h2 class="h5 mb-0">Historique des emprunts</h2>
        </div>
        <div class="card-body">
            <?php if (empty($historiqueEmprunts)): ?>
                <p class="text-muted mb-0">Vous n'avez aucun historique d'emprunt.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Auteur</th>
                                <th>Emprunté le</th>
                                <th>Retourné le</th>
                                <th>Durée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historiqueEmprunts as $emprunt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emprunt['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($emprunt['auteur_prenom'] . ' ' . $emprunt['auteur_nom']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($emprunt['dateemprunt'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($emprunt['dateretoureffectif'])); ?></td>
                                    <td><?php echo $emprunt['duree_emprunt']; ?> jours</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
