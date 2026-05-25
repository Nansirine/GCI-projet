<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['dessinateur','ingenieur','admin']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$plan_id = (int)($_POST['plan_id'] ?? 0);
$contenu = trim(sanitize($_POST['annotation'] ?? ''));
$user_id = $_SESSION['user_id'];
if (strlen($contenu) < 3) {
  echo json_encode(['success' => false, 'message' => 'Annotation trop courte']);
  exit;
}
try {
  $check = $pdo->prepare("SELECT p.id, p.projet_id FROM plans p JOIN affectations a ON a.projet_id = p.projet_id WHERE p.id = ? AND a.utilisateur_id = ?");
  $check->execute([$plan_id, $user_id]);
  if (!$check->fetch() && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
  }
  $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, projet_id, sujet, contenu) SELECT ?, dessinateur_id, projet_id, CONCAT('Annotation plan #', ?), ? FROM plans WHERE id = ?");
  $stmt->execute([$user_id, $plan_id, $contenu, $plan_id]);
  $user = $pdo->prepare("SELECT CONCAT(prenom,' ',nom) as nom FROM utilisateurs WHERE id=?");
  $user->execute([$user_id]);
  $u = $user->fetch();
  echo json_encode([
    'success' => true,
    'auteur' => htmlspecialchars($u['nom']),
    'date' => date('d/m/Y à H:i'),
    'contenu' => htmlspecialchars($contenu)
  ]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
