<?php
session_start();
require_once 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mel = trim($_POST['email'] ?? '');
    $motpasse = $_POST['password'] ?? '';

    if (empty($mel) || empty($motpasse)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $query = $pdo->prepare("SELECT * FROM utilisateur WHERE mel = ?");
        $query->execute([$mel]);
        $user = $query->fetch();

        if ($user && password_verify($motpasse, $user['motpasse'])) {
            $_SESSION['user_id'] = $user['mel'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            
            // Rediriger vers la page d'accueil
            header('Location: index.php');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="login-container">
    <div class="login-box">
        <h2>Connexion</h2>
        <form action="" method="POST">
            <div class="form-group">
                <input type="email" name="email" required placeholder="Email">
            </div>
            <div class="form-group">
                <input type="password" name="password" required placeholder="Mot de passe">
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <button type="submit" name="login" class="login-btn">Se connecter</button>
        </form>
        <div class="login-footer">
            <p>Pas encore membre ? <a href="register.php">Créer un compte</a></p>
        </div>
    </div>
</div>

<style>
    .login-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .login-box {
        background: var(--dark-surface);
        padding: 2.5rem;
        border-radius: 10px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }

    .login-box h2 {
        color: var(--text-light);
        text-align: center;
        margin-bottom: 2rem;
        font-family: 'Orbitron', sans-serif;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid var(--neon-blue);
        background: rgba(255, 255, 255, 0.05);
        border-radius: 5px;
        color: var(--text-light);
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--neon-cyan);
        box-shadow: 0 0 10px rgba(14, 165, 233, 0.3);
    }

    .form-group input::placeholder {
        color: #64748b;
    }

    .login-btn {
        width: 100%;
        padding: 0.8rem;
        background: var(--neon-blue);
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .login-btn:hover {
        background: var(--neon-cyan);
        transform: translateY(-2px);
    }

    .login-footer {
        text-align: center;
        margin-top: 1.5rem;
        color: #64748b;
    }

    .login-footer a {
        color: var(--neon-blue);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .login-footer a:hover {
        color: var(--neon-cyan);
    }
</style>

<?php
require_once 'includes/footer.php';
?>
