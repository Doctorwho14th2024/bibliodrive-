<?php
require_once '../includes/header.php';
requireAdmin();

// Récupérer l'ID du livre
$nolivre = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer les données du formulaire
        $titre = $_POST['titre'];
        $resume = $_POST['resume'];
        $isbn13 = $_POST['isbn13'];
        $anneeparution = $_POST['anneeparution'];
        $categorie = $_POST['categorie'];
        $auteur_nom = $_POST['auteur_nom'];
        $auteur_prenom = $_POST['auteur_prenom'];

        // Démarrer une transaction
        $pdo->beginTransaction();

        // Mettre à jour ou créer l'auteur
        $stmt = $pdo->prepare("
            SELECT noauteur 
            FROM auteur 
            WHERE LOWER(nom) = LOWER(?) AND LOWER(prenom) = LOWER(?)
        ");
        $stmt->execute([trim($auteur_nom), trim($auteur_prenom)]);
        $auteur = $stmt->fetch();

        if ($auteur) {
            $noauteur = $auteur['noauteur'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO auteur (nom, prenom) VALUES (?, ?)");
            $stmt->execute([trim($auteur_nom), trim($auteur_prenom)]);
            $noauteur = $pdo->lastInsertId();
        }

        // Gérer l'upload de l'image si une nouvelle image est fournie
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Supprimer l'ancienne image si elle existe
                $stmt = $pdo->prepare("SELECT image FROM livre WHERE nolivre = ?");
                $stmt->execute([$nolivre]);
                $old_image = $stmt->fetchColumn();
                
                if ($old_image && file_exists("../" . $old_image)) {
                    unlink("../" . $old_image);
                }
                
                $image_path = 'uploads/' . $new_filename;
            }
        }

        // Mettre à jour le livre
        $sql = "UPDATE livre SET 
                titre = ?, 
                resume = ?, 
                isbn13 = ?,
                anneeparution = ?,
                categorie = ?,
                noauteur = ?";
        
        $params = [
            $titre,
            $resume,
            $isbn13,
            $anneeparution,
            $categorie,
            $noauteur
        ];

        if ($image_path !== null) {
            $sql .= ", image = ?";
            $params[] = $image_path;
        }

        $sql .= " WHERE nolivre = ?";
        $params[] = $nolivre;

        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $pdo->commit();
            $_SESSION['success_message'] = "Le livre a été modifié avec succès.";
            header("Location: liste-livres.php");
            exit;
        } else {
            throw new Exception("Erreur lors de la mise à jour du livre.");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    }
}

// Récupérer les informations du livre
try {
    $stmt = $pdo->prepare("
        SELECT l.*, a.nom as auteur_nom, a.prenom as auteur_prenom
        FROM livre l
        LEFT JOIN auteur a ON l.noauteur = a.noauteur
        WHERE l.nolivre = ?
    ");
    $stmt->execute([$nolivre]);
    $livre = $stmt->fetch();

    if (!$livre) {
        $_SESSION['error_message'] = "Livre non trouvé.";
        header("Location: liste-livres.php");
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération du livre : " . $e->getMessage();
    header("Location: liste-livres.php");
    exit;
}
?>

<div class="container py-5">
    <h1>Modifier un livre</h1>

    <?php displayMessages(); ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <!-- Titre -->
                <div class="col-md-6">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="titre" name="titre" 
                           value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
                </div>

                <!-- ISBN13 -->
                <div class="col-md-6">
                    <label for="isbn13" class="form-label">ISBN13</label>
                    <input type="text" class="form-control" id="isbn13" name="isbn13" 
                           value="<?php echo htmlspecialchars($livre['isbn13']); ?>">
                </div>

                <!-- Auteur -->
                <div class="col-md-6">
                    <label for="auteur_nom" class="form-label">Nom de l'auteur</label>
                    <input type="text" class="form-control" id="auteur_nom" name="auteur_nom" 
                           value="<?php echo htmlspecialchars($livre['auteur_nom']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="auteur_prenom" class="form-label">Prénom de l'auteur</label>
                    <input type="text" class="form-control" id="auteur_prenom" name="auteur_prenom" 
                           value="<?php echo htmlspecialchars($livre['auteur_prenom']); ?>" required>
                </div>

                <!-- Année et Catégorie -->
                <div class="col-md-6">
                    <label for="anneeparution" class="form-label">Année de parution</label>
                    <input type="number" class="form-control" id="anneeparution" name="anneeparution" 
                           value="<?php echo htmlspecialchars($livre['anneeparution']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <input type="text" class="form-control" id="categorie" name="categorie" 
                           value="<?php echo htmlspecialchars($livre['categorie']); ?>" required>
                </div>

                <!-- Image -->
                <div class="col-12">
                    <label for="image" class="form-label">Image (laisser vide pour conserver l'image actuelle)</label>
                    <?php if ($livre['image']): ?>
                        <div class="mb-2">
                            <img src="/biblio/<?php echo htmlspecialchars($livre['image']); ?>" 
                                 alt="Image actuelle" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <!-- Résumé -->
                <div class="col-12">
                    <label for="resume" class="form-label">Résumé</label>
                    <textarea class="form-control" id="resume" name="resume" rows="4"><?php 
                        echo htmlspecialchars($livre['resume']); 
                    ?></textarea>
                </div>

                <!-- Boutons -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                    <a href="liste-livres.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
