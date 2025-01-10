<?php
require_once '../includes/header.php';
requireAdmin();

// Vérifier si la colonne etat existe
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM utilisateur LIKE 'etat'");
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        echo '<div class="alert alert-warning">
            <strong>Attention :</strong> La base de données nécessite une mise à jour. 
            <a href="update_database.php" class="btn btn-primary btn-sm ms-3">Mettre à jour maintenant</a>
        </div>';
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la vérification de la structure de la base de données : " . $e->getMessage();
}

// Gérer le changement de rôle
if (isset($_POST['changer_role']) && isset($_POST['noutilisateur']) && isset($_POST['role'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM utilisateur WHERE noutilisateur = ?");
        $stmt->execute([$_POST['noutilisateur']]);
        $user = $stmt->fetch();

        if ($user['role'] === 'admin' && $_SESSION['noutilisateur'] != $_POST['noutilisateur']) {
            $_SESSION['error_message'] = "Vous ne pouvez pas modifier le rôle d'un autre administrateur.";
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateur SET role = ? WHERE noutilisateur = ?");
            if ($stmt->execute([$_POST['role'], $_POST['noutilisateur']])) {
                $_SESSION['success_message'] = "Le rôle de l'utilisateur a été mis à jour.";
            }
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la modification du rôle : " . $e->getMessage();
    }
}

// Gérer la désactivation/réactivation d'un compte
if (isset($_POST['toggle_status']) && isset($_POST['noutilisateur'])) {
    try {
        $stmt = $pdo->prepare("SELECT role, etat FROM utilisateur WHERE noutilisateur = ?");
        $stmt->execute([$_POST['noutilisateur']]);
        $user = $stmt->fetch();

        if ($user['role'] === 'admin' && $_SESSION['noutilisateur'] != $_POST['noutilisateur']) {
            $_SESSION['error_message'] = "Vous ne pouvez pas désactiver le compte d'un autre administrateur.";
        } else {
            $nouvel_etat = $user['etat'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE utilisateur SET etat = ? WHERE noutilisateur = ?");
            if ($stmt->execute([$nouvel_etat, $_POST['noutilisateur']])) {
                $_SESSION['success_message'] = "Le statut du compte a été mis à jour.";
            }
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la modification du statut : " . $e->getMessage();
    }
}

// Récupérer les statistiques des utilisateurs
$query = "
    SELECT u.*,
           COUNT(DISTINCT CASE WHEN e.dateretoureffectif IS NULL THEN e.nolivre END) as nb_emprunts_actuels,
           (SELECT COUNT(*) FROM emprunter e2 WHERE e2.noutilisateur = u.noutilisateur) as nb_emprunts_total
    FROM utilisateur u
    LEFT JOIN emprunter e ON u.noutilisateur = e.noutilisateur AND e.dateretoureffectif IS NULL
    GROUP BY u.noutilisateur
    ORDER BY u.noutilisateur DESC";

try {
    $utilisateurs = $pdo->query($query)->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
    $utilisateurs = [];
}
?>

<div class="container-fluid py-4">
    <!-- En-tête avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-users me-2 text-primary"></i>
                                Gestion des utilisateurs
                            </h1>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-3">
                                <div class="text-end">
                                    <div class="small text-muted">Total utilisateurs</div>
                                    <div class="h4 mb-0"><?php echo count($utilisateurs); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Emprunts actifs</div>
                                    <div class="h4 mb-0">
                                        <?php 
                                        echo array_sum(array_column($utilisateurs, 'nb_emprunts_actuels')); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php displayMessages(); ?>

    <!-- Liste des utilisateurs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Liste des utilisateurs
                    </h5>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Rôle</th>
                            <th class="py-3">Statut</th>
                            <th class="py-3">Emprunts</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $utilisateur): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3">
                                            <?php 
                                            $initials = strtoupper(substr($utilisateur['prenom'], 0, 1) . substr($utilisateur['nom'], 0, 1));
                                            echo $initials;
                                            ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                <?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?>
                                            </div>
                                            <small class="text-muted">
                                                #<?php echo $utilisateur['noutilisateur']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <a href="mailto:<?php echo htmlspecialchars($utilisateur['mel']); ?>" 
                                       class="text-decoration-none">
                                        <?php echo htmlspecialchars($utilisateur['mel']); ?>
                                    </a>
                                </td>
                                <td class="py-3">
                                    <span class="badge <?php echo $utilisateur['role'] === 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                        <?php echo $utilisateur['role'] === 'admin' ? 'Administrateur' : 'Lecteur'; ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="badge <?php echo $utilisateur['etat'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $utilisateur['etat'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="fw-semibold">
                                                <?php echo $utilisateur['nb_emprunts_actuels']; ?> 
                                                <span class="text-muted">en cours</span>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $utilisateur['nb_emprunts_total']; ?> au total
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="voir-emprunts.php?id=<?php echo $utilisateur['noutilisateur']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Voir les emprunts">
                                            <i class="fas fa-book-reader"></i>
                                        </a>
                                        
                                        <?php if ($_SESSION['noutilisateur'] != $utilisateur['noutilisateur']): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="noutilisateur" 
                                                       value="<?php echo $utilisateur['noutilisateur']; ?>">
                                                
                                                <?php if ($utilisateur['role'] !== 'admin'): ?>
                                                    <input type="hidden" name="role" 
                                                           value="<?php echo $utilisateur['role'] === 'user' ? 'admin' : 'user'; ?>">
                                                    <button type="submit" 
                                                            name="changer_role" 
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="<?php echo $utilisateur['role'] === 'user' ? 'Promouvoir administrateur' : 'Rétrograder utilisateur'; ?>">
                                                        <i class="fas <?php echo $utilisateur['role'] === 'user' ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <button type="submit" 
                                                        name="toggle_status" 
                                                        class="btn btn-sm <?php echo $utilisateur['etat'] ? 'btn-outline-danger' : 'btn-outline-success'; ?>"
                                                        title="<?php echo $utilisateur['etat'] ? 'Désactiver' : 'Activer'; ?> le compte">
                                                    <i class="fas <?php echo $utilisateur['etat'] ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
    background-color: transparent;
    border-bottom-width: 1px;
    box-shadow: inset 0 0 0 9999px transparent;
}

.table > tbody > tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.btn-sm {
    padding: 0.4rem 0.6rem;
    font-size: 0.785rem;
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.card {
    --bs-card-border-color: rgba(0, 0, 0, 0.05);
}

.bg-light {
    background-color: rgba(0, 0, 0, 0.02) !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>
