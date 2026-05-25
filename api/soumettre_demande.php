<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['client']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$titre = sanitize($_POST['titre'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$user_id = $_SESSION['user_id'];
if (strlen($titre) < 5 || strlen($description) < 15) {
  echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs correctement']);
  exit;
}
try {
  $proj = $pdo->prepare("SELECT id, nom, admin_id FROM projets WHERE client_id = ? LIMIT 1");
  $proj->execute([$user_id]);
  $projet = $proj->fetch();
  if (!$projet) {
    echo json_encode(['success' => false, 'message' => 'Aucun projet trouvé']);
    exit;
  }
  $stmt = $pdo->prepare("INSERT INTO demandes (client_id, projet_id, titre, description) VALUES (?,?,?,?)");
  $stmt->execute([$user_id, $projet['id'], $titre, $description]);
  createNotification($pdo, $projet['admin_id'],
    'Nouvelle demande client',
    $_SESSION['prenom'].' '.$_SESSION['nom'].' a soumis une demande : "'.$titre.'"',
    'avertissement', '/admin/projet_detail.php?id='.$projet['id'].'&tab=demandes'
  );
  echo json_encode(['success' => true, 'message' => 'Demande envoyée avec succès']);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
