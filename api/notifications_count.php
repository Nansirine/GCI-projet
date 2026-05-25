<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
$user_id = $_SESSION['user_id'];
try {
  $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE utilisateur_id = ? AND lu = 0");
  $stmt->execute([$user_id]);
  $row = $stmt->fetch();
  echo json_encode(['success' => true, 'count' => (int)$row['count']]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'count' => 0]);
}
