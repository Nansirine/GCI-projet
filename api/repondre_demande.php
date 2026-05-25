<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$demande_id = (int)($_POST['demande_id'] ?? 0);
$reponse = sanitize($_POST['reponse'] ?? '');
$statut = in_array($_POST['statut']??'',['traite','refuse','en_cours']) ?$_POST['statut']:'traite';
if (!$demande_id || strlen($reponse) < 5) {
  echo json_encode(['success' => false, 'message' => 'Réponse trop courte']);
  exit;
}
try {
  $info = $pdo->prepare("SELECT d.*, p.nom as projet_nom FROM demandes d JOIN projets p ON p.id = d.projet_id WHERE d.id = ?");
  $info->execute([$demande_id]);
  $demande = $info->fetch();
  if (!$demande) { echo json_encode(['success'=>false,'message'=>'Introuvable']); exit; }
  $stmt = $pdo->prepare("UPDATE demandes SET reponse=?, statut=?, date_reponse=NOW() WHERE id=?");
  $stmt->execute([$reponse, $statut, $demande_id]);
  createNotification($pdo, $demande['client_id'],
    'Réponse à votre demande',
    'Votre demande "'.$demande['titre'].'" a reçu une réponse.',
    'succes', '/client/demandes.php'
  );
  echo json_encode(['success' => true]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
