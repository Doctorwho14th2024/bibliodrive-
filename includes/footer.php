<?php
// Contenu du footer original
?>
    </div><!-- Fermeture du container -->
</div><!-- Fermeture du container principal -->

<footer class="footer mt-5">
    <div class="container">
        <div class="row py-4">
            <div class="col-md-4 mb-3">
                <h5 class="text-uppercase mb-3">BiblioTech</h5>
                <p>Votre bibliothèque numérique nouvelle génération. Un accès illimité à la culture, disponible 24/7.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5 class="text-uppercase mb-3">Horaires</h5>
                <ul class="list-unstyled">
                    <li>Lundi - Vendredi : 9h - 17h</li>
                    <li>Service Biblio Drive uniquement</li>
                    <li>Réservation en ligne 24/7</li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5 class="text-uppercase mb-3">Contact</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope me-2"></i>contact@bibliotech.fr</li>
                    <li><i class="fas fa-phone me-2"></i>01 23 45 67 89</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i>123 Rue du Code, 75000 Paris</li>
                </ul>
            </div>
        </div>
        <div class="row border-top py-3">
            <div class="col-md-6 text-center text-md-start">
                <small>&copy; <?= date('Y') ?> BiblioTech. Tous droits réservés.</small>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="/biblio/mentions-legales.php" class="text-decoration-none me-3">Mentions légales</a>
                <a href="/biblio/contact.php" class="text-decoration-none">Contact</a>
            </div>
        </div>
    </div>
</footer>

<!-- Effet de particules -->
<div id="particles"></div>

<!-- Cookie Consent -->
<div id="cookieConsent" class="cookie-consent" style="display: none;">
    <div class="cookie-content">
        <div class="cookie-header">
            <i class="fas fa-cookie-bite text-primary me-2"></i>
            <h5 class="mb-0">Nous utilisons des cookies</h5>
        </div>
        <p class="mb-3">
            Ce site utilise des cookies pour améliorer votre expérience. En continuant à naviguer sur ce site, 
            vous acceptez notre utilisation des cookies.
            <a href="mentions-legales.php#cookies" class="text-primary">En savoir plus</a>
        </p>
        <div class="cookie-buttons">
            <button onclick="refuseCookies()" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>Refuser
            </button>
            <button onclick="acceptCookies()" class="btn btn-primary">
                <i class="fas fa-check me-2"></i>Accepter
            </button>
        </div>
    </div>
</div>

<style>
.cookie-consent {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(13, 17, 23, 0.95);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #2d5af7;
    box-shadow: 0 0 20px rgba(45, 90, 247, 0.3);
    z-index: 9999;
    max-width: 400px;
    width: 90%;
    animation: slideUp 0.5s ease-out;
    color: #fff;
    backdrop-filter: blur(10px);
}

@keyframes slideUp {
    from {
        transform: translate(-50%, 100%);
        opacity: 0;
    }
    to {
        transform: translate(-50%, 0);
        opacity: 1;
    }
}

.cookie-content {
    text-align: center;
}

.cookie-header {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: #2d5af7;
}

.cookie-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.cookie-buttons .btn {
    min-width: 120px;
    border-radius: 5px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.cookie-buttons .btn-primary {
    background: #2d5af7;
    border-color: #2d5af7;
    box-shadow: 0 0 10px rgba(45, 90, 247, 0.3);
}

.cookie-buttons .btn-outline-secondary {
    border-color: #6c757d;
    color: #fff;
}

.cookie-buttons .btn-outline-secondary:hover {
    background: rgba(108, 117, 125, 0.2);
    color: #fff;
}

.cookie-consent a {
    color: #2d5af7;
    text-decoration: none;
    transition: color 0.3s ease;
}

.cookie-consent a:hover {
    color: #4d71f7;
    text-decoration: underline;
}
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/biblio/assets/js/cookies.js"></script>
<script>
    // Animation des particules
    document.addEventListener('DOMContentLoaded', () => {
        const particlesContainer = document.getElementById('particles');
        if (particlesContainer) {
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + 'vw';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particle.style.animationDuration = (Math.random() * 5 + 5) + 's';
                particlesContainer.appendChild(particle);
            }
        }
    });
</script>
</body>
</html>
