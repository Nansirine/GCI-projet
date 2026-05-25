<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$error = '';
if (isset($_GET['logout'])) {
    $error = 'Déconnecté avec succès.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/database.php';
    $pdo = $pdo ?? require __DIR__ . '/config/database.php';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        if ($user['statut'] === 'inactif') {
            $error = 'Compte désactivé.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['photo'] = $user['photo'];
            // Cookie "se souvenir de moi"
            if ($remember) {
                setcookie('remember_me', $user['id'], time() + 60*60*24*30, '/');
            }
            // MAJ dernière connexion
            $pdo->prepare('UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?')->execute([$user['id']]);
            // Redirection selon rôle
            switch ($user['role']) {
                case 'admin':
                    header('Location: /gestion_projet/admin/dashboard.php'); exit;
                case 'ingenieur':
                    header('Location: /gestion_projet/ingenieur/dashboard.php'); exit;
                case 'dessinateur':
                    header('Location: /gestion_projet/dessinateur/dashboard.php'); exit;
                case 'client':
                    header('Location: /gestion_projet/client/dashboard.php'); exit;
            }
        }
    } else {
        $error = 'Identifiants invalides.';
    }
}
require_once 'includes/header.php';
?>
<style>
body {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a2d4f 0%, #3a5068 100%);
}
.login-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(26,45,79,0.15);
    padding: 2.5rem 2rem;
    max-width: 400px;
    margin: 60px auto;
}
.login-logo {
    width: 60px;
    margin-bottom: 1rem;
}
.form-error {
    color: #fff;
    background: #dc3545;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    text-align: center;
}
</style>
<div class="container">
    <div class="login-card">
        <div class="text-center">
            <img src="/gestion_projet/assets/img/logo.png" alt="Logo" class="login-logo">
            <h3 class="mb-3">Système de Gestion de Projets<br><small class="text-muted">Génie Civil</small></h3>
        </div>
        <?php if ($error): ?>
            <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            <div class="mb-3 position-relative">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword"><span class="bi bi-eye"></span></button>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                Se Connecter
            </button>
            <div class="mt-3 text-end">
                <a href="reset_password.php">Mot de passe oublié ?</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
<script>
// Toggle show/hide password
document.getElementById('togglePassword').onclick = function() {
    var pwd = document.getElementById('password');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        this.innerHTML = '<span class="bi bi-eye-slash"></span>';
    } else {
        pwd.type = 'password';
        this.innerHTML = '<span class="bi bi-eye"></span>';
    }
};
// Spinner bouton
document.getElementById('loginBtn').onclick = function() {
    document.getElementById('loginSpinner').classList.remove('d-none');
};
</script>
<?php require_once 'includes/footer.php'; ?>
