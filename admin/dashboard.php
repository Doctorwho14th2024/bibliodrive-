<?php
require_once '../includes/header.php';
requireAdmin(); // S'assure que seuls les administrateurs peuvent accéder à cette page

// Récupérer les statistiques
$stats = [];
try {
    // Nombre total de livres
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM livre");
    $stats['total_livres'] = $stmt->fetch()['total'];

    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateur WHERE role = 'user'");
    $stats['total_utilisateurs'] = $stmt->fetch()['total'];

    // Nombre total d'emprunts en cours
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM emprunter WHERE dateretoureffectif IS NULL");
    $stats['emprunts_en_cours'] = $stmt->fetch()['total'];

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des statistiques : " . $e->getMessage();
}
?>

<div class="container py-4">
    <h1 class="mb-4">Tableau de bord administrateur</h1>

    <?php displayMessages(); ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total des livres</h5>
                    <p class="card-text display-4"><?php echo $stats['total_livres']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Utilisateurs inscrits</h5>
                    <p class="card-text display-4"><?php echo $stats['total_utilisateurs']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Emprunts en cours</h5>
                    <p class="card-text display-4"><?php echo $stats['emprunts_en_cours']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Gestion des livres</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="ajouter-livre.php" class="btn btn-primary">Ajouter un nouveau livre</a>
                        <a href="liste-livres.php" class="btn btn-outline-primary">Gérer les livres</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Gestion des utilisateurs</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="liste-utilisateurs.php" class="btn btn-success">Gérer les utilisateurs</a>
                        <a href="liste-emprunts.php" class="btn btn-outline-success">Voir les emprunts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
