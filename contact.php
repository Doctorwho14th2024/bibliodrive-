<?php
require_once 'includes/header.php';

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $sujet = filter_input(INPUT_POST, 'sujet', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if (!empty($nom) && !empty($email) && !empty($sujet) && !empty($message)) {
    
        $success = true;
    } else {
        $error = "Veuillez remplir tous les champs du formulaire.";
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title h2 mb-4">Contactez-nous</h1>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom complet</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nom" 
                                   name="nom" 
                                   required
                                   value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                            <div class="invalid-feedback">
                                Veuillez entrer votre nom.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sujet" class="form-label">Sujet</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="sujet" 
                                   name="sujet" 
                                   required
                                   value="<?php echo isset($_POST['sujet']) ? htmlspecialchars($_POST['sujet']) : ''; ?>">
                            <div class="invalid-feedback">
                                Veuillez entrer le sujet de votre message.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" 
                                      id="message" 
                                      name="message" 
                                      rows="5" 
                                      required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            <div class="invalid-feedback">
                                Veuillez entrer votre message.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>
                            Envoyer le message
                        </button>
                    </form>

                    <div class="mt-5">
                        <h3 class="h5 mb-3">Autres moyens de nous contacter</h3>
                        <div class="d-flex flex-column gap-2">
                            <div>
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                123 Rue de la Bibliothèque, 75000 Paris
                            </div>
                            <div>
                                <i class="fas fa-phone text-primary me-2"></i>
                                +33 (0)1 23 45 67 89
                            </div>
                            <div>
                                <i class="fas fa-envelope text-primary me-2"></i>
                                contact@bibliotech.fr
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation des formulaires Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once 'includes/footer.php'; ?>
