<?php
require_once 'includes/header.php';
requireLogin();

// Récupérer les paramètres de recherche
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$categorie = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';

try {
    // Requête de base
    $sql = "SELECT l.*, a.nom as auteur_nom, a.prenom as auteur_prenom 
            FROM livre l 
            LEFT JOIN auteur a ON l.noauteur = a.noauteur 
            WHERE 1=1";
    $params = [];

    // Recherche par titre ou résumé
    if (!empty($q)) {
        $sql .= " AND (LOWER(l.titre) LIKE LOWER(?) OR LOWER(l.resume) LIKE LOWER(?))";
        $searchTerm = "%$q%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Recherche par catégorie
    if (!empty($categorie)) {
        $sql .= " AND LOWER(l.categorie) = LOWER(?)";
        $params[] = $categorie;
    }

    // Ordre par date d'ajout
    $sql .= " ORDER BY l.dateajout DESC";

    // Debug
    echo "<!-- SQL: $sql -->";
    echo "<!-- Params: " . implode(", ", $params) . " -->";

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
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <!-- Recherche principale -->
                <div class="col-md-8">
                    <label for="q" class="form-label">Rechercher</label>
                    <input type="text" name="q" id="q" class="form-control" 
                           placeholder="Rechercher par titre..." 
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>

                <!-- Catégorie -->
                <div class="col-md-4">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select class="form-select" id="categorie" name="categorie">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php echo $categorie === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Résultats -->
    <?php if (empty($resultats)): ?>
        <div class="alert alert-info">
            Aucun livre ne correspond à votre recherche.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($resultats as $livre): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($livre['image']): ?>
                            <img src="<?php echo htmlspecialchars($livre['image']); ?>" 
                                 class="card-img-top" alt="Couverture">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h5>
                            <p class="card-text">
                                <?php if ($livre['auteur_prenom'] || $livre['auteur_nom']): ?>
                                    Par <?php echo htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']); ?><br>
                                <?php endif; ?>
                                Catégorie : <?php echo htmlspecialchars($livre['categorie']); ?>
                            </p>
                            <?php if ($livre['resume']): ?>
                                <p class="card-text">
                                    <?php 
                                    $resume = htmlspecialchars($livre['resume']);
                                    echo strlen($resume) > 150 ? substr($resume, 0, 150) . '...' : $resume;
                                    ?>
                                </p>
                            <?php endif; ?>
                            <a href="livre.php?id=<?php echo $livre['nolivre']; ?>" 
                               class="btn btn-primary">Voir plus</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
