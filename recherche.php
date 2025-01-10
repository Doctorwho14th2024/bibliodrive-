<?php
require_once 'includes/header.php';
requireLogin();

// Récupérer les paramètres de recherche
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$categorie = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';
$annee_min = isset($_GET['annee_min']) ? filter_var($_GET['annee_min'], FILTER_VALIDATE_INT) : null;
$annee_max = isset($_GET['annee_max']) ? filter_var($_GET['annee_max'], FILTER_VALIDATE_INT) : null;
$disponibilite = isset($_GET['disponibilite']) ? $_GET['disponibilite'] : 'tous';

try {
    // Construction de la requête de base
    $sql = "
        SELECT l.*, 
               a.nom as auteur_nom, 
               a.prenom as auteur_prenom,
               (SELECT COUNT(*) FROM emprunter e 
                WHERE e.nolivre = l.nolivre 
                AND e.dateretoureffectif IS NULL) as est_emprunte
        FROM livre l
        LEFT JOIN auteur a ON l.noauteur = a.noauteur
        WHERE 1=1
    ";
    $params = [];

    // Ajouter les conditions de recherche
    if (!empty($q)) {
        $sql .= " AND (
            l.titre LIKE ? 
            OR l.resume LIKE ? 
            OR CONCAT(a.prenom, ' ', a.nom) LIKE ?
        )";
        $searchTerm = "%$q%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }

    if (!empty($categorie)) {
        $sql .= " AND l.categorie = ?";
        $params[] = $categorie;
    }

    if ($annee_min !== null) {
        $sql .= " AND l.anneeparution >= ?";
        $params[] = $annee_min;
    }

    if ($annee_max !== null) {
        $sql .= " AND l.anneeparution <= ?";
        $params[] = $annee_max;
    }

    if ($disponibilite === 'disponible') {
        $sql .= " AND (SELECT COUNT(*) FROM emprunter e 
                      WHERE e.nolivre = l.nolivre 
                      AND e.dateretoureffectif IS NULL) = 0";
    } elseif ($disponibilite === 'emprunte') {
        $sql .= " AND (SELECT COUNT(*) FROM emprunter e 
                      WHERE e.nolivre = l.nolivre 
                      AND e.dateretoureffectif IS NULL) > 0";
    }

    $sql .= " ORDER BY l.dateajout DESC";

    // Exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll();

    // Récupérer les catégories pour le filtre
    $stmt_categories = $pdo->query("SELECT DISTINCT categorie FROM livre WHERE categorie IS NOT NULL ORDER BY categorie");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la recherche : " . $e->getMessage();
    $resultats = [];
    $categories = [];
}
?>

<div class="container py-5">
    <!-- Formulaire de recherche -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <!-- Barre de recherche principale -->
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               name="q" 
                               class="form-control form-control-lg" 
                               placeholder="Rechercher par titre, auteur ou résumé..."
                               value="<?php echo htmlspecialchars($q); ?>">
                    </div>
                </div>

                <!-- Filtres avancés -->
                <div class="col-md-3">
                    <select name="categorie" class="form-select">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php echo $categorie === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="number" 
                           name="annee_min" 
                           class="form-control" 
                           placeholder="Année min"
                           value="<?php echo $annee_min ?? ''; ?>">
                </div>

                <div class="col-md-2">
                    <input type="number" 
                           name="annee_max" 
                           class="form-control" 
                           placeholder="Année max"
                           value="<?php echo $annee_max ?? ''; ?>">
                </div>

                <div class="col-md-3">
                    <select name="disponibilite" class="form-select">
                        <option value="tous" <?php echo $disponibilite === 'tous' ? 'selected' : ''; ?>>
                            Tous les livres
                        </option>
                        <option value="disponible" <?php echo $disponibilite === 'disponible' ? 'selected' : ''; ?>>
                            Disponibles uniquement
                        </option>
                        <option value="emprunte" <?php echo $disponibilite === 'emprunte' ? 'selected' : ''; ?>>
                            Empruntés uniquement
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Résultats de recherche -->
    <div class="mb-4">
        <h4>
            <?php 
            $nb_resultats = count($resultats);
            if (!empty($q)) {
                echo "Résultats pour \"" . htmlspecialchars($q) . "\" ";
            }
            echo "($nb_resultats " . ($nb_resultats > 1 ? "livres trouvés)" : "livre trouvé)");
            ?>
        </h4>
    </div>

    <?php if (empty($resultats)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Aucun livre ne correspond à votre recherche.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($resultats as $livre): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <a href="livre.php?id=<?php echo $livre['nolivre']; ?>" 
                           class="text-decoration-none">
                            <?php if ($livre['image']): ?>
                                <img src="<?php echo htmlspecialchars($livre['image']); ?>" 
                                     class="card-img-top" 
                                     alt="Couverture"
                                     style="height: 300px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 300px;">
                                    <i class="fas fa-book fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </a>

                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="livre.php?id=<?php echo $livre['nolivre']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($livre['titre']); ?>
                                </a>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                <?php echo htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']); ?>
                            </h6>
                            <?php if ($livre['resume']): ?>
                                <p class="card-text">
                                    <?php 
                                    $resume = htmlspecialchars($livre['resume']);
                                    echo strlen($resume) > 150 ? substr($resume, 0, 150) . '...' : $resume;
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-transparent border-0 p-3">
                            <div class="d-grid gap-2">
                                <?php if ($livre['est_emprunte'] > 0): ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-clock me-2"></i>Indisponible
                                    </button>
                                <?php else: ?>
                                    <a href="emprunter.php?id=<?php echo $livre['nolivre']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-book-reader me-2"></i>Emprunter
                                    </a>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-2">
                                    <a href="livre.php?id=<?php echo $livre['nolivre']; ?>" 
                                       class="btn btn-outline-primary flex-grow-1">
                                        <i class="fas fa-info-circle me-2"></i>Détails
                                    </a>
                                    
                                    <form method="post" action="catalogue.php" class="flex-grow-1">
                                        <input type="hidden" name="nolivre" value="<?php echo $livre['nolivre']; ?>">
                                        <?php if (in_array($livre['nolivre'], $_SESSION['panier'] ?? [])): ?>
                                            <button type="submit" 
                                                    name="retirer_panier" 
                                                    class="btn btn-outline-danger w-100">
                                                <i class="fas fa-times me-2"></i>Retirer
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" 
                                                    name="ajouter_panier" 
                                                    class="btn btn-outline-success w-100"
                                                    <?php echo $livre['est_emprunte'] > 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-bookmark me-2"></i>Réserver
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease-in-out;
}
.hover-card:hover {
    transform: translateY(-5px);
}
</style>

<?php require_once 'includes/footer.php'; ?>
