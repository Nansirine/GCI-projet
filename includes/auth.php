<?php
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/gestion_projet',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/functions.php';

function checkAuth(): void {
  if (empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /gestion_projet/login.php?error=session_expired');
    exit;
  }
  // Vérifier que l'utilisateur existe toujours et est actif
  global $pdo;
  $stmt = $pdo->prepare("SELECT id, statut FROM utilisateurs WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();
  if (!$user || $user['statut'] === 'inactif') {
    session_destroy();
    header('Location: /gestion_projet/login.php?error=compte_inactif');
    exit;
  }
}

function checkRole(array $roles): void {
  checkAuth();
  if (!in_array($_SESSION['role'], $roles, true)) {
    header('HTTP/1.1 403 Forbidden');
    include __DIR__.'/../403.php';
    exit;
  }
}

function generateCSRFToken(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): void {
  if (empty($_SESSION['csrf_token']) || 
      !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Token CSRF invalide']);
    exit;
  }
}

function loginRateLimit(string $email): bool {
  $key = 'login_attempts_'.md5($email);
  if (!isset($_SESSION[$key])) $_SESSION[$key] = ['count'=>0,'time'=>time()];
  // Reset si plus de 15 min
  if (time() - $_SESSION[$key]['time'] > 900) {
    $_SESSION[$key] = ['count'=>0,'time'=>time()];
  }
  $_SESSION[$key]['count']++;
  return $_SESSION[$key]['count'] <= 5; // max 5 tentatives
}

function resetLoginAttempts(string $email): void {
  unset($_SESSION['login_attempts_'.md5($email)]);
}
