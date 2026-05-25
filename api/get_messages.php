<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
$with = (int)($_GET['with'] ?? 0);
$user_id = $_SESSION['user_id'];
if (!$with) { echo json_encode([]); exit; }
try {
  $stmt = $pdo->prepare("SELECT m.*, CONCAT(u.prenom,' ',u.nom) as expediteur_nom FROM messages m JOIN utilisateurs u ON u.id = m.expediteur_id WHERE (m.expediteur_id = ? AND m.destinataire_id = ?) OR (m.expediteur_id = ? AND m.destinataire_id = ?) ORDER BY m.date_envoi ASC LIMIT 100");
  $stmt->execute([$user_id, $with, $with, $user_id]);
  $upd = $pdo->prepare("UPDATE messages SET lu = 1 WHERE expediteur_id = ? AND destinataire_id = ?");
  $upd->execute([$with, $user_id]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) {
  echo json_encode([]);
}
