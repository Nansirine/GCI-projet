<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vérifie que l'utilisateur est connecté
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /gestion_projet/login.php');
        exit();
    }
}
// Vérifie le rôle de l'utilisateur
function checkRole($roles_autorises) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles_autorises)) {
        header('Location: /gestion_projet/403.php');
        exit();
    }
}
function isAdmin() { return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'); }
function isIngenieur() { return (isset($_SESSION['role']) && $_SESSION['role'] === 'ingenieur'); }
function isDessinateur() { return (isset($_SESSION['role']) && $_SESSION['role'] === 'dessinateur'); }
function isClient() { return (isset($_SESSION['role']) && $_SESSION['role'] === 'client'); }
// Retourne les infos complètes de l'utilisateur connecté
function getCurrentUser($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
