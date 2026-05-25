<?php
session_start();
require '../config/database.php';
require '../includes/auth.php';
header('Content-Type: application/json');
checkAuth();
checkRole(['client']);
$user_id = $_SESSION['user_id'];
try {
  $proj = $pdo->prepare("SELECT id FROM projets WHERE client_id = ? LIMIT 1");
  $proj->execute([$user_id]);
  $projet = $proj->fetch();
  if (!$projet) { echo json_encode([]); exit; }
  $stmt = $pdo->prepare("SELECT id, titre as name, DATE_FORMAT(date_debut,'%Y-%m-%d') as start, DATE_FORMAT(date_echeance,'%Y-%m-%d') as end, pourcentage as progress, statut FROM taches WHERE projet_id = ? ORDER BY date_debut ASC");
  $stmt->execute([$projet['id']]);
  $taches = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $gantt = array_map(fn($t) => [
    'id' => 'tache_'.$t['id'],
    'name' => $t['name'],
    'start' => $t['start'],
    'end' => $t['end'] ?: date('Y-m-d', strtotime($t['start'].' +7 days')),
    'progress' => (int)$t['progress'],
    'custom_class' => $t['statut'] === 'termine' ? 'bar-success' : ($t['statut'] === 'bloque' ? 'bar-danger' : 'bar-primary')
  ], $taches);
  echo json_encode($gantt);
} catch(Exception $e) {
  echo json_encode([]);
}
