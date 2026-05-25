<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin','ingenieur']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$alerte_id = (int)($_POST['alerte_id'] ?? 0);
$user_id = $_SESSION['user_id'];
try {
  $check = $pdo->prepare("SELECT * FROM alertes WHERE id = ?");
  $check->execute([$alerte_id]);
  $alerte = $check->fetch();
  if (!$alerte) { echo json_encode(['success'=>false,'message'=>'Alerte introuvable']); exit; }
  if ($_SESSION['role'] === 'ingenieur' && $alerte['signale_par'] !== $user_id) {
    echo json_encode(['success'=>false,'message'=>'Accès refusé']); exit;
  }
  $stmt = $pdo->prepare("UPDATE alertes SET statut='resolu', date_resolution=NOW() WHERE id=?");
  $stmt->execute([$alerte_id]);
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
