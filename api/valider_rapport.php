<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$rapport_id = (int)($_POST['rapport_id'] ?? 0);
$action = in_array($_POST['action'] ?? '', ['valide','rejete']) ? $_POST['action'] : null;
$commentaire = sanitize($_POST['commentaire'] ?? '');
if (!$rapport_id || !$action) {
  echo json_encode(['success' => false, 'message' => 'Données invalides']);
  exit;
}
if ($action === 'rejete' && strlen($commentaire) < 5) {
  echo json_encode(['success' => false, 'message' => 'Un commentaire est requis pour le rejet']);
  exit;
}
try {
  $info = $pdo->prepare("SELECT r.*, p.client_id, p.nom as projet_nom, CONCAT(u.prenom,' ',u.nom) as ingenieur_nom FROM rapports r JOIN projets p ON p.id = r.projet_id JOIN utilisateurs u ON u.id = r.ingenieur_id WHERE r.id = ?");
  $info->execute([$rapport_id]);
  $rapport = $info->fetch();
  if (!$rapport) {
    echo json_encode(['success' => false, 'message' => 'Rapport introuvable']);
    exit;
  }
  $date_val = ($action === 'valide') ? date('Y-m-d H:i:s') : null;
  $stmt = $pdo->prepare("UPDATE rapports SET statut = ?, commentaire_admin = ?, date_validation = ? WHERE id = ?");
  $stmt->execute([$action, $commentaire, $date_val, $rapport_id]);
  $msg_ing = $action === 'valide'
    ? 'Votre rapport "'.$rapport['titre'].'" a été validé.'
    : 'Votre rapport "'.$rapport['titre'].'" a été rejeté : '.$commentaire;
  createNotification($pdo, $rapport['ingenieur_id'],
    'Rapport '.($action === 'valide' ? 'validé ✅' : 'rejeté ❌'),
    $msg_ing, $action === 'valide' ? 'succes' : 'erreur',
    '/ingenieur/rapports.php'
  );
  if ($action === 'valide') {
    createNotification($pdo, $rapport['client_id'],
      'Nouveau rapport disponible',
      'Un rapport sur votre projet "'.$rapport['projet_nom'].'" est maintenant disponible.',
      'info', '/client/rapports.php'
    );
  }
  echo json_encode(['success' => true, 'action' => $action]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
