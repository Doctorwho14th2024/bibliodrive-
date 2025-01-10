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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
