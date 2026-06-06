<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
ensureUserAccountColumns($pdo);

$token = $_GET['token'] ?? '';
$message = '';
$messageType = 'info';
$showForm = false;
$user = null;

if ($token) {
    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE reset_token = ? AND reset_token_expires > NOW() AND statut = "actif"');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    $showForm = (bool)$user;
}

if (!$showForm) {
    $message = 'Lien invalide ou expire.';
    $messageType = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (strlen($password) < 6) {
        $message = 'Le mot de passe doit contenir au moins 6 caracteres.';
        $messageType = 'danger';
    } elseif ($password !== $password2) {
        $message = 'Les mots de passe ne correspondent pas.';
        $messageType = 'danger';
    } else {
        $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        $message = 'Mot de passe modifie avec succes. Vous pouvez vous connecter.';
        $messageType = 'success';
        $showForm = false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - Buildflow</title>
    <link href="/gestion_projet/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/gestion_projet/assets/css/login.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Nouveau mot de passe</h1>
                <p class="login-subtitle">Choisissez un mot de passe personnel</p>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($showForm): ?>
                <form method="post" class="login-form">
                    <div class="form-group-modern">
                        <label for="password" class="form-label-modern">Nouveau mot de passe</label>
                        <input type="password" class="form-input-modern" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="form-group-modern">
                        <label for="password2" class="form-label-modern">Confirmer</label>
                        <input type="password" class="form-input-modern" id="password2" name="password2" minlength="6" required>
                    </div>
                    <button class="btn-login" type="submit">Modifier</button>
                </form>
            <?php endif; ?>
            <div class="login-footer">
                <a href="login.php" class="login-link">Retour a la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
