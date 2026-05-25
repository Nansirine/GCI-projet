<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$projet_id = (int)($_POST['projet_id'] ?? 0);
$user_id_cible = (int)($_POST['utilisateur_id'] ?? 0);
try {
  $stmt = $pdo->prepare("DELETE FROM affectations WHERE projet_id = ? AND utilisateur_id = ?");
  $stmt->execute([$projet_id, $user_id_cible]);
  createNotification($pdo, $user_id_cible,
    'Retrait de projet',
    'Vous avez été retiré d\'un projet.',
    'avertissement', '/ingenieur/dashboard.php'
  );
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
