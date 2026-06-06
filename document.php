<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

checkAuth();
ensureDocumentDecisionColumns($pdo);

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$download = isset($_GET['download']);
$userId = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? '';

if (!$id || !in_array($type, ['plan', 'rapport'], true)) {
    http_response_code(400);
    exit('Document invalide.');
}

$document = null;
$path = null;
$name = 'document';
$allowed = false;

if ($type === 'plan') {
    $stmt = $pdo->prepare('
        SELECT pl.*, p.client_id, p.admin_id
        FROM plans pl
        JOIN projets p ON p.id = pl.projet_id
        WHERE pl.id = ?
    ');
    $stmt->execute([$id]);
    $document = $stmt->fetch();
    if ($document) {
        $path = $document['fichier'];
        $name = $document['titre'] ?: 'plan';
        $allowed = $role === 'admin'
            || ((int)$document['dessinateur_id'] === $userId)
            || ($role === 'ingenieur' && userBelongsToProject($pdo, $userId, (int)$document['projet_id']))
            || ($role === 'client' && (int)$document['client_id'] === $userId && (int)$document['partage_client'] === 1);
        if ($allowed && $role === 'client' && $download && ($document['client_decision'] ?? 'en_attente') !== 'approuve') {
            http_response_code(403);
            exit('Vous devez approuver ce fichier avant de le telecharger.');
        }
    }
}

if ($type === 'rapport') {
    $stmt = $pdo->prepare('
        SELECT r.*, p.client_id, p.admin_id
        FROM rapports r
        JOIN projets p ON p.id = r.projet_id
        WHERE r.id = ?
    ');
    $stmt->execute([$id]);
    $document = $stmt->fetch();
    if ($document) {
        $path = $document['fichier_joint'];
        $name = $document['titre'] ?: 'rapport';
        $allowed = $role === 'admin'
            || ((int)$document['ingenieur_id'] === $userId)
            || ($role === 'ingenieur' && userBelongsToProject($pdo, $userId, (int)$document['projet_id']))
            || ($role === 'client' && (int)$document['client_id'] === $userId && $document['statut'] === 'valide');
        if ($allowed && $role === 'client' && $download && ($document['client_decision'] ?? 'en_attente') !== 'approuve') {
            http_response_code(403);
            exit('Vous devez approuver ce fichier avant de le telecharger.');
        }
    }
}

if (!$document || !$path || !$allowed) {
    http_response_code(403);
    exit('Acces refuse.');
}

$baseDir = realpath(__DIR__ . '/uploads');
$fullPath = realpath(__DIR__ . '/' . ltrim($path, '/\\'));

if (!$baseDir || !$fullPath || strpos($fullPath, $baseDir) !== 0 || !is_file($fullPath)) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

$mime = mime_content_type($fullPath) ?: 'application/octet-stream';
$extension = fileExtension($path);
$safeName = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $name) . ($extension ? '.' . $extension : '');

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $safeName . '"');
readfile($fullPath);
exit;
