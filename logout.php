<?php
// Déconnexion
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
session_unset();
session_destroy();
if (isset($_COOKIE['remember_me'])) {
	setcookie('remember_me', '', time() - 3600, '/');
}
header('Location: login.php?logout=1');
exit();
