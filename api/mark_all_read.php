<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
$input = json_decode(file_get_contents('php://input'), true);
verifyCSRFToken($input['csrf_token'] ?? '');
$user_id = $_SESSION['user_id'];
try {
  $stmt = $pdo->prepare("UPDATE notifications SET lu = 1 WHERE utilisateur_id = ?");
  $stmt->execute([$user_id]);
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
