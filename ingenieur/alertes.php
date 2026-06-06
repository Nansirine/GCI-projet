<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';
$old = $_POST;

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
            createNotification($pdo, $adminId, 'Nouvelle alerte projet', ($_SESSION['prenom'] ?? 'Un ingenieur') . ' a signale : "' . $titre . '".', 'avertissement', '/admin/projet_detail.php?id=' . $projetId);
        }
        $message = 'Alerte signalee avec succes.';
        $messageType = 'success';
        $old = [];
    }
}

$projets = $pdo->prepare("
    SELECT DISTINCT p.id, p.nom
    FROM projets p
    JOIN affectations a ON a.projet_id = p.id
    WHERE a.utilisateur_id = ?
    ORDER BY p.nom
");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();

$stmtTaches = $pdo->prepare("SELECT id, titre, projet_id FROM taches WHERE assigne_a = ? ORDER BY date_echeance, titre");
$stmtTaches->execute([$user_id]);
$taches = $stmtTaches->fetchAll();

$stmtAlertes = $pdo->prepare("
    SELECT al.*, p.nom AS projet_nom
    FROM alertes al
    JOIN projets p ON p.id = al.projet_id
    WHERE al.signale_par = ?
    ORDER BY al.date_creation DESC
");
$stmtAlertes->execute([$user_id]);
$alertes = $stmtAlertes->fetchAll();

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('alertes', 'bi-exclamation-triangle', 'Alertes'); ?>
<div class="page-container">
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <h2 class="fw-bold mb-4">Signaler un probleme</h2>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" id="projet_id" required>
                <option value="">Selectionner un projet</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= (string)($old['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($projet['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tache liee</label>
            <select class="form-select" name="tache_id" id="tache_id">
                <option value="">Aucune tache liee</option>
                <?php foreach ($taches as $tache): ?>
                    <option value="<?= (int)$tache['id'] ?>" data-projet-id="<?= (int)$tache['projet_id'] ?>" <?= (string)($old['tache_id'] ?? '') === (string)$tache['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tache['titre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Niveau *</label>
            <select class="form-select" name="niveau" required>
                <?php foreach (['info' => 'Info', 'avertissement' => 'Avertissement', 'critique' => 'Critique'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($old['niveau'] ?? 'info') === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre du probleme *</label>
            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($old['titre'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description detaillee *</label>
            <textarea class="form-control" name="description" rows="2" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-warning"><i class="bi bi-exclamation-triangle"></i> Signaler le probleme</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Titre</th><th>Niveau</th><th>Statut</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php if (!$alertes): ?>
                    <tr><td colspan="5" class="text-center">Aucune alerte signalee.</td></tr>
                <?php endif; ?>
                <?php foreach ($alertes as $alerte): ?>
                    <tr>
                        <td><?= htmlspecialchars($alerte['projet_nom']) ?></td>
                        <td><?= htmlspecialchars($alerte['titre']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($alerte['niveau'])) ?></td>
                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $alerte['statut']))) ?></td>
                        <td><?= htmlspecialchars(formatDatetime($alerte['date_creation'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
const projectSelect = document.getElementById('projet_id');
const taskSelect = document.getElementById('tache_id');
function filterTasks() {
    const projectId = projectSelect?.value || '';
    taskSelect?.querySelectorAll('option[data-projet-id]').forEach(function(option) {
        option.hidden = projectId && option.dataset.projetId !== projectId;
    });
    if (taskSelect?.selectedOptions[0]?.hidden) {
        taskSelect.value = '';
    }
}
projectSelect?.addEventListener('change', filterTasks);
filterTasks();
</script>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
