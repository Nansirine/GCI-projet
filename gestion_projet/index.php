<?php
// Redirection vers la page de connexion si non connecté
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Redirection selon le rôle
switch ($_SESSION['role']) {
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'ingenieur':
        header('Location: ingenieur/dashboard.php');
        break;
    case 'dessinateur':
        header('Location: dessinateur/dashboard.php');
        break;
    case 'client':
        header('Location: client/dashboard.php');
        break;
    default:
        header('Location: login.php');
}
exit();
