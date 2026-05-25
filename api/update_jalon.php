<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['admin']);
verifyCSRFToken($_POST['csrf_token'] ?? '');
$jalon_id = (int)($_POST['jalon_id'] ?? 0);
$statut = in_array($_POST['statut']??'',['atteint','manque','a_venir']) ? $_POST['statut'] : 'a_venir';
try {
  $date_reelle = ($statut === 'atteint') ? date('Y-m-d') : null;
  $stmt = $pdo->prepare("UPDATE jalons SET statut = ?, date_reelle = ? WHERE id = ?");
  $stmt->execute([$statut, $date_reelle, $jalon_id]);
  if ($statut === 'atteint') {
    $info = $pdo->prepare("SELECT j.titre, pr.client_id, pr.nom as projet_nom FROM jalons j JOIN projets pr ON pr.id = j.projet_id WHERE j.id = ?");
    $info->execute([$jalon_id]);
    $j = $info->fetch();
    if ($j) {
      createNotification($pdo, $j['client_id'],
        '🎉 Jalon atteint !',
        'Le jalon "'.$j['titre'].'" de votre projet a été atteint.',
        'succes', '/client/avancement.php'
      );
    }
  }
  echo json_encode(['success' => true, 'statut' => $statut]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
