<?php
session_start();
require_once 'database.php';
require_once 'includes/authentification.php';
require_once 'includes/header.php';
requireLogin();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Gérer l'ajout au panier
if (isset($_POST['ajouter_panier']) && isset($_POST['nolivre'])) {
    $nolivre = (int)$_POST['nolivre'];
    if (!in_array($nolivre, $_SESSION['panier'])) {
        $_SESSION['panier'][] = $nolivre;
        $_SESSION['success_message'] = "Livre ajouté au panier !";
    }
}

// Gérer le retrait du panier
if (isset($_POST['retirer_panier']) && isset($_POST['nolivre'])) {
    $nolivre = (int)$_POST['nolivre'];
    $key = array_search($nolivre, $_SESSION['panier']);
    if ($key !== false) {
        unset($_SESSION['panier'][$key]);
        $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexer le tableau
        $_SESSION['success_message'] = "Livre retiré du panier !";
    }
}

// Récupérer les filtres
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$auteur = isset($_GET['auteur']) ? (int)$_GET['auteur'] : 0;
$disponibilite = isset($_GET['disponibilite']) ? $_GET['disponibilite'] : 'tous';

// Construire la requête SQL de base
$sql = "
    SELECT l.*, 
           a.nom as nom_auteur, 
           a.prenom as prenom_auteur,
           (SELECT COUNT(*) FROM emprunter e WHERE e.nolivre = l.nolivre AND e.dateretoureffectif IS NULL) as nb_emprunts
    FROM livre l 
    JOIN auteur a ON l.noauteur = a.noauteur 
    WHERE 1=1
";
$params = [];

// Ajouter les conditions de filtrage
if (!empty($recherche)) {
    $sql .= " AND (l.titre LIKE ? OR l.resume LIKE ? OR CONCAT(a.prenom, ' ', a.nom) LIKE ?)";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}
if ($auteur > 0) {
    $sql .= " AND l.noauteur = ?";
    $params[] = $auteur;
}
if ($disponibilite === 'disponible') {
    $sql .= " AND (SELECT COUNT(*) FROM emprunter e WHERE e.nolivre = l.nolivre AND e.dateretoureffectif IS NULL) = 0";
} elseif ($disponibilite === 'emprunte') {
    $sql .= " AND (SELECT COUNT(*) FROM emprunter e WHERE e.nolivre = l.nolivre AND e.dateretoureffectif IS NULL) > 0";
}

$sql .= " ORDER BY l.dateajout DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $livres = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des livres : " . $e->getMessage();
    $livres = [];
}

// Récupérer la liste des auteurs pour le filtre
try {
    $stmt_auteurs = $pdo->query("SELECT * FROM auteur ORDER BY nom, prenom");
    $auteurs = $stmt_auteurs->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des auteurs : " . $e->getMessage();
    $auteurs = [];
}
?>

<div class="container py-5">
    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               name="recherche" 
                               class="form-control" 
                               placeholder="Rechercher un livre..."
                               value="<?php echo htmlspecialchars($recherche); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="auteur" class="form-select">
                        <option value="0">Tous les auteurs</option>
                        <?php foreach ($auteurs as $a): ?>
                            <option value="<?php echo $a['noauteur']; ?>" 
                                    <?php echo $auteur == $a['noauteur'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['prenom'] . ' ' . $a['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php displayMessages(); ?>

    <!-- Résultats -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($livres as $livre): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <a href="livre.php?id=<?php echo $livre['nolivre']; ?>" class="text-decoration-none">
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
                            <?php echo htmlspecialchars($livre['prenom_auteur'] . ' ' . $livre['nom_auteur']); ?>
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
                            <?php if ($livre['nb_emprunts'] > 0): ?>
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
                                
                                <form method="post" class="flex-grow-1">
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
                                                <?php echo $livre['nb_emprunts'] > 0 ? 'disabled' : ''; ?>>
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
