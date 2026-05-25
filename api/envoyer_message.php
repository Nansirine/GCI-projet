<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
verifyCSRFToken($_POST['csrf_token'] ?? '');
$dest_id = (int)($_POST['destinataire_id'] ?? 0);
$sujet = sanitize($_POST['sujet'] ?? '');
$contenu = sanitize($_POST['contenu'] ?? '');
$projet_id = (int)($_POST['projet_id'] ?? 0) ?: null;
$user_id = $_SESSION['user_id'];
if (!$dest_id || strlen($contenu) < 2) {
  echo json_encode(['success' => false, 'message' => 'Données manquantes']);
  exit;
}
if ($dest_id === $user_id) {
  echo json_encode(['success' => false, 'message' => 'Impossible de s\'écrire à soi-même']);
  exit;
}
try {
  $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, projet_id, sujet, contenu) VALUES (?,?,?,?,?)");
  $stmt->execute([$user_id, $dest_id, $projet_id, $sujet, $contenu]);
  createNotification($pdo, $dest_id,
    'Nouveau message de '.$_SESSION['prenom'],
    substr($contenu, 0, 80).'...',
    'info', '/ingenieur/messages.php'
  );
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
