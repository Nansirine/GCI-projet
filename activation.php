<?php
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/config/database.php';

$token = $_GET['token'] ?? '';
$message = '';
$show_form = false;

if ($token) {
    $stmt = $pdo->prepare('SELECT id, email, statut FROM utilisateurs WHERE activation_token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user && $user['statut'] === 'inactif') {
        $show_form = true;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'], $_POST['password2'])) {
            $password = $_POST['password'];
            $password2 = $_POST['password2'];
            if (strlen($password) < 6) {
                $message = "Le mot de passe doit contenir au moins 6 caractères.";
            } elseif ($password !== $password2) {
                $message = "Les mots de passe ne correspondent pas.";
            } else {
                $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ?, statut = ?, activation_token = NULL WHERE id = ?');
                $stmt->execute([
                    password_hash($password, PASSWORD_DEFAULT),
                    'actif',
                    $user['id']
                ]);
                $message = "Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.";
                $show_form = false;
            }
        }
    } else {
        $message = "Lien d'activation invalide ou compte déjà activé.";
    }
} else {
    $message = "Lien d'activation invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation du compte</title>
    <link rel="stylesheet" href="/gestion_projet/assets/css/app.css">
</head>
<body>
    <div class="container" style="max-width:500px;margin:40px auto;">
        <h2>Activation du compte</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($show_form): ?>
        <form method="post">
            <div class="mb-3">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" name="password" id="password" required minlength="6">
            </div>
            <div class="mb-3">
                <label for="password2" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" name="password2" id="password2" required minlength="6">
            </div>
            <button type="submit" class="btn btn-success">Activer mon compte</button>
        </form>
        <?php endif; ?>
        <div style="margin-top:20px;">
            <a href="/gestion_projet/login.php">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>
