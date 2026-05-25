<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
verifyCSRFToken($_POST['csrf_token'] ?? '');
$id = (int)($_POST['id'] ?? 0);
$user_id = $_SESSION['user_id'];
try {
  $stmt = $pdo->prepare("UPDATE notifications SET lu = 1 WHERE id = ? AND utilisateur_id = ?");
  $stmt->execute([$id, $user_id]);
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
