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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Buildflow</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS Local -->
    <link href="/gestion_projet/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- FontAwesome Local -->
    <link href="/gestion_projet/assets/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Login -->
    <link href="/gestion_projet/assets/css/login.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="/gestion_projet/image/WhatsApp%20Image%202026-05-11%20at%2017.24.59.jpeg" alt="Logo Buildflow" style="object-fit:cover;width:60px;height:60px;border-radius:50%;">
                </div>
                <h1 class="login-title">Buildflow</h1>
                <p class="login-subtitle">Gestion de projets professionnels</p>
            </div>
            
            <?php if ($error): ?>
                <div class="login-alert <?= strpos($error, 'succès') !== false ? 'login-alert-success' : 'login-alert-error' ?>">
                    <i class="bi <?= strpos($error, 'succès') !== false ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post" class="login-form" autocomplete="off">
                <div class="form-group-modern">
                    <label for="email" class="form-label-modern">Adresse Email</label>
                    <div class="input-with-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" class="form-input-modern" id="email" name="email" placeholder="votre@email.com" required autofocus>
                    </div>
                </div>
                
                <div class="form-group-modern">
                    <label for="password" class="form-label-modern">Mot de passe</label>
                    <div class="input-with-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" class="form-input-modern" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-check-modern">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="loginText">Se Connecter</span>
                    <span id="loginSpinner" class="spinner d-none"></span>
                </button>
            </form>
            
            <div class="login-footer">
                <a href="reset_password.php" class="login-link">Mot de passe oublié ?</a>
            </div>
            
            <div class="login-info">
                <p class="login-info-text">Accès réservé aux utilisateurs autorisés</p>
                <div class="login-info-roles">
                    <span class="role-badge">Admin</span>
                    <span class="role-badge">Ingénieur</span>
                    <span class="role-badge">Dessinateur</span>
                    <span class="role-badge">Client</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Local -->
    <script src="/gestion_projet/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle show/hide password
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Spinner sur le bouton de connexion
        document.querySelector('.login-form').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const text = document.getElementById('loginText');
            const spinner = document.getElementById('loginSpinner');
            
            btn.disabled = true;
            text.classList.add('d-none');
            spinner.classList.remove('d-none');
        });
    </script>
</body>
</html>
<?php exit; ?>
