<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
ensureUserAccountColumns($pdo);

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, statut FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['statut'] === 'actif') {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $pdo->prepare('UPDATE utilisateurs SET reset_token = ?, reset_token_expires = ? WHERE id = ?')
                ->execute([$token, $expires, $user['id']]);

            $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/gestion_projet/reset_password_confirm.php?token=' . $token;
            $body = "Bonjour " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ",<br><br>"
                . "Vous avez demande la modification de votre mot de passe Buildflow.<br>"
                . "Cliquez sur ce lien valable pendant 1 heure :<br>"
                . "<a href='" . htmlspecialchars($link) . "'>" . htmlspecialchars($link) . "</a>";
            sendMailSMTP($email, 'Modification de votre mot de passe Buildflow', $body, $user['prenom'] . ' ' . $user['nom']);
        }

        $message = 'Si ce compte existe et est actif, un email de reinitialisation a ete envoye.';
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublie - Buildflow</title>
    <link href="/gestion_projet/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/gestion_projet/assets/css/login.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Mot de passe oublie</h1>
                <p class="login-subtitle">Recevez un lien pour definir un nouveau mot de passe</p>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" class="login-form">
                <div class="form-group-modern">
                    <label for="email" class="form-label-modern">Adresse email</label>
                    <input type="email" class="form-input-modern" id="email" name="email" required autofocus>
                </div>
                <button class="btn-login" type="submit">Envoyer le lien</button>
            </form>
            <div class="login-footer">
                <a href="login.php" class="login-link">Retour a la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
