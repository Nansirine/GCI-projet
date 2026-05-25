<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['ingenieur']);
$input = json_decode(file_get_contents('php://input'), true);
verifyCSRFToken($input['csrf_token'] ?? '');
$tache_id = (int)($input['tache_id'] ?? 0);
$pourcentage = max(0, min(100, (int)($input['pourcentage'] ?? 0)));
$statuts_ok = ['a_faire','en_cours','en_revision','termine','bloque'];
$statut = in_array($input['statut'] ?? '', $statuts_ok) ? $input['statut'] : 'en_cours';
$user_id = $_SESSION['user_id'];
try {
  $check = $pdo->prepare("SELECT id, projet_id, titre FROM taches WHERE id = ? AND assigne_a = ?");
  $check->execute([$tache_id, $user_id]);
  $tache = $check->fetch();
  if (!$tache) {
    echo json_encode(['success' => false, 'message' => 'Tâche introuvable ou accès refusé']);
    exit;
  }
  $date_completion = ($statut === 'termine') ? date('Y-m-d') : null;
  $stmt = $pdo->prepare("UPDATE taches SET pourcentage = ?, statut = ?, date_completion = ? WHERE id = ?");
  $stmt->execute([$pourcentage, $statut, $date_completion, $tache_id]);
  updateProjectProgress($pdo, $tache['projet_id']);
  $admins = $pdo->prepare("SELECT admin_id FROM projets WHERE id = ?");
  $admins->execute([$tache['projet_id']]);
  $proj = $admins->fetch();
  createNotification($pdo, $proj['admin_id'],
    'Tâche mise à jour',
    'La tâche "'.$tache['titre'].'" est à '.$pourcentage.'% ('.$statut.')',
    'info',
    '/admin/projet_detail.php?id='.$tache['projet_id'].'&tab=taches'
  );
  echo json_encode(['success' => true, 'pourcentage' => $pourcentage, 'statut' => $statut]);
} catch(Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
