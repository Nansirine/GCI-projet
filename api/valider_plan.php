<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin', 'ingenieur']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$plan_id = (int)($_POST['plan_id'] ?? 0);
$action = in_array($_POST['action'] ?? '', ['valide','rejete']) ? $_POST['action'] : null;
$commentaire = sanitize($_POST['commentaire'] ?? '');
try {
  $info = $pdo->prepare("SELECT p.*, u.id as dess_id, pr.client_id FROM plans p JOIN utilisateurs u ON u.id = p.dessinateur_id JOIN projets pr ON pr.id = p.projet_id WHERE p.id = ?");
  $info->execute([$plan_id]);
  $plan = $info->fetch();
  if (!$plan) { echo json_encode(['success'=>false,'message'=>'Plan introuvable']); exit; }
  if ($_SESSION['role'] === 'ingenieur' && !userBelongsToProject($pdo, (int)$_SESSION['user_id'], (int)$plan['projet_id'])) {
    echo json_encode(['success'=>false,'message'=>'Acces refuse']);
    exit;
  }
  $stmt = $pdo->prepare("UPDATE plans SET statut = ?, commentaire = ? WHERE id = ?");
  $stmt->execute([$action, $commentaire, $plan_id]);
  createNotification($pdo, $plan['dess_id'],
    'Plan '.($action==='valide'?'validé ✅':'rejeté ❌'),
    'Le plan "'.$plan['titre'].'" a été '.($action==='valide'?'validé':'rejeté').($commentaire ? ' : '.$commentaire : '.'),
    $action==='valide'?'succes':'erreur', '/dessinateur/plans.php'
  );
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
