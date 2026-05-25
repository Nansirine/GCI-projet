<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['ingenieur', 'admin']);
$projet_id = (int)($_GET['projet_id'] ?? 0);
$user_id = $_SESSION['user_id'];
if (!$projet_id) {
  echo json_encode(['success' => false, 'message' => 'projet_id requis']);
  exit;
}
try {
  $check = $pdo->prepare("SELECT id FROM affectations WHERE projet_id = ? AND utilisateur_id = ?");
  $check->execute([$projet_id, $user_id]);
  if (!$check->fetch() && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
  }
  $stmt = $pdo->prepare("SELECT id, titre, statut, pourcentage FROM taches WHERE projet_id = ? AND assigne_a = ? ORDER BY date_echeance ASC");
  $stmt->execute([$projet_id, $user_id]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) {
  echo json_encode([]);
}
