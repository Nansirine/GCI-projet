<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $tacheId = (int)($_POST['tache_id'] ?? 0) ?: null;
    $niveau = $_POST['niveau'] ?? 'info';
    $titre = sanitize($_POST['titre'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    if (!$projetId || $titre === '' || $description === '') {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $messageType = 'danger';
    } elseif (!userBelongsToProject($pdo, $user_id, $projetId)) {
        $message = 'Vous ne pouvez pas signaler une alerte sur ce projet.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('INSERT INTO alertes (projet_id, tache_id, signale_par, titre, description, niveau) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$projetId, $tacheId, $user_id, $titre, $description, $niveau]);
        $admin = $pdo->prepare('SELECT admin_id FROM projets WHERE id = ?');
        $admin->execute([$projetId]);
        $adminId = (int)$admin->fetchColumn();
        if ($adminId) {
            createNotification($pdo, $adminId, 'Nouvelle alerte projet', $_SESSION['prenom'] . ' a signale : "' . $titre . '".', 'avertissement', '/admin/projet_detail.php?id=' . $projetId);
        }
        $message = 'Alerte signalee avec succes.';
        $messageType = 'success';
    }
}

$projets = $pdo->prepare("SELECT DISTINCT p.id, p.nom FROM projets p JOIN affectations a ON a.projet_id = p.id WHERE a.utilisateur_id = ? ORDER BY p.nom");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();
$stmtTaches = $pdo->prepare("SELECT id, titre, projet_id FROM taches WHERE assigne_a = ? ORDER BY date_echeance, titre");
$stmtTaches->execute([$user_id]);
$taches = $stmtTaches->fetchAll();
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('alertes', 'bi-exclamation-triangle', 'Alertes'); ?>
<div class="page-container">
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <h2 class="fw-bold mb-4">Signaler un Problème</h2>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required>
                <option value="">Selectionner un projet</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>"><?= htmlspecialchars($projet['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tâche liée</label>
            <select class="form-select" name="tache_id">
                <option value="">Aucune tache liee</option>
                <?php foreach ($taches as $tache): ?>
                    <option value="<?= (int)$tache['id'] ?>" data-projet-id="<?= (int)$tache['projet_id'] ?>"><?= htmlspecialchars($tache['titre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Niveau *</label>
            <select class="form-select" name="niveau" required>
                <option value="info">Info</option>
                <option value="avertissement">Avertissement</option>
                <option value="critique">Critique</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre du problème *</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description détaillée *</label>
            <textarea class="form-control" name="description" rows="2" required></textarea>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-warning">⚠ Signaler le Problème</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Titre</th><th>Niveau</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody id="alertes-list">
                <!-- Alertes dynamiques -->
            </tbody>
        </table>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
