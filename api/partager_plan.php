<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin', 'ingenieur']);

$input = json_decode(file_get_contents('php://input'), true);
verifyCSRFToken($input['csrf_token'] ?? '');

$plan_id = (int)($input['plan_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

try {
  $check = $pdo->prepare("SELECT p.id, p.titre, p.projet_id, pr.client_id FROM plans p JOIN projets pr ON p.projet_id = pr.id WHERE p.id = ? AND p.statut = 'valide'");
  $check->execute([$plan_id]);
  $plan = $check->fetch();

  if (!$plan) {
    echo json_encode(['success' => false, 'message' => 'Plan introuvable ou non valide']);
    exit;
  }

  if ($_SESSION['role'] === 'ingenieur' && !userBelongsToProject($pdo, $user_id, (int)$plan['projet_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acces refuse']);
    exit;
  }

  $stmt = $pdo->prepare("UPDATE plans SET partage_client = 1 WHERE id = ?");
  $stmt->execute([$plan_id]);

  createNotification($pdo, (int)$plan['client_id'],
    'Nouveau plan disponible',
    'Le plan "'.$plan['titre'].'" de votre projet est maintenant accessible.',
    'info',
    '/client/plans.php'
  );

  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
