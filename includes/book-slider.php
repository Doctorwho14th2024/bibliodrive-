<?php
$query = "SELECT l.*, a.nom as nom_auteur, a.prenom as prenom_auteur 
         FROM livre l 
         JOIN auteur a ON l.noauteur = a.noauteur 
         ORDER BY l.dateajout DESC";
$stmt = $pdo->query($query);
?>

<!-- Ajout des liens CSS et JS de Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<!-- Style personnalisé pour le slider -->
<style>
    .swiper {
        width: 100%;
        height: 400px;
        padding: 20px 0;
    }
    .swiper-slide {
        background: #fff;
        height: 350px;
    }
    .book-card {
        height: 100%;
        display: flex;
        flex-direction: row;
    }
    .book-image {
        width: 200px;
        height: 100%;
        object-fit: cover;
    }
    .book-content {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
    }
    .book-resume {
        max-height: 150px;
        overflow-y: auto;
    }
</style>

<div class="swiper mySwiper">
    <div class="swiper-wrapper">
        <?php while ($livre = $stmt->fetch()): ?>
            <div class="swiper-slide">
                <div class="book-card">
                    <div class="book-image">
                        <?php if ($livre['image']): ?>
                            <img src="<?= htmlspecialchars($livre['image']) ?>" 
                                 class="img-fluid h-100 w-100 object-fit-cover" 
                                 alt="Couverture de <?= htmlspecialchars($livre['titre']) ?>">
                        <?php else: ?>
                            <div class="bg-light h-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-book fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="book-content">
                        <h5 class="card-title"><?= htmlspecialchars($livre['titre']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            Par <?= htmlspecialchars($livre['prenom_auteur'] . ' ' . $livre['nom_auteur']) ?>
                        </h6>
                        <p class="card-text">
                            <small class="text-muted">Année : <?= htmlspecialchars($livre['anneeparution']) ?></small>
                        </p>
                        <?php if (!empty($livre['resume'])): ?>
                            <div class="book-resume">
                                <p class="card-text"><?= nl2br(htmlspecialchars($livre['resume'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="livre.php?id=<?= $livre['nolivre'] ?>" class="btn btn-primary btn-sm">Voir plus</a>
                            <?php if (isLoggedIn()): ?>
                                <a href="emprunter.php?id=<?= $livre['nolivre'] ?>" class="btn btn-outline-primary btn-sm">Emprunter</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
</div>

<!-- Initialisation de Swiper -->
<script>
    var swiper = new Swiper(".mySwiper", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
</script>
