<?php
require_once '../includes/auth.php';
checkRole(['ingenieur', 'admin']);
require_once '../config/database.php';

$taskId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = (int)$_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $taskId) {
    $pourcentage = max(0, min(100, (int)($_POST['pourcentage'] ?? 0)));
    $statut = $_POST['statut'] ?? 'a_faire';
    $allowedStatus = ['a_faire', 'en_cours', 'en_revision', 'termine', 'bloque'];

    if (in_array($statut, $allowedStatus, true)) {
        $stmt = $pdo->prepare('UPDATE taches SET pourcentage = ?, statut = ? WHERE id = ?');
        $stmt->execute([$pourcentage, $statut, $taskId]);
        $message = 'Tache mise a jour avec succes.';
    }
}

$stmt = $pdo->prepare('
    SELECT t.*, p.nom AS projet_nom, u.nom AS responsable_nom, u.prenom AS responsable_prenom
    FROM taches t
    LEFT JOIN projets p ON t.projet_id = p.id
    LEFT JOIN utilisateurs u ON t.assigne_a = u.id
    WHERE t.id = ?
');
$stmt->execute([$taskId]);
$task = $stmt->fetch();

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">

<?php renderAppLayoutStart('taches', 'bi-list-task', 'Detail de la tache'); ?>
            <div class="centered-page">
                <div class="page-header">
                    <h1 class="page-title"><i class="bi bi-list-task"></i> Detail de la tache</h1>
                    <div class="page-actions">
                        <a href="<?= $_SESSION['role'] === 'admin' ? '../admin/taches.php' : 'taches.php' ?>" class="btn-modern btn-outline-modern">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <?php if (!$task): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-exclamation-circle"></i></div>
                        <div class="empty-state-title">Tache introuvable</div>
                    </div>
                <?php else: ?>
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <div class="section-card centered-form-card">
                        <div class="section-header">
                            <div>
                                <div class="section-title"><?= htmlspecialchars($task['titre']) ?></div>
                                <div class="text-muted mt-1"><?= htmlspecialchars($task['projet_nom'] ?? 'Projet non renseigne') ?></div>
                            </div>
                            <span class="status-badge status-<?= htmlspecialchars(str_replace('_', '-', $task['statut'])) ?>">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $task['statut']))) ?>
                            </span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card-modern p-3 h-100">
                                    <div class="text-muted small">Responsable</div>
                                    <strong><?= htmlspecialchars(trim(($task['responsable_prenom'] ?? '') . ' ' . ($task['responsable_nom'] ?? '')) ?: '-') ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card-modern p-3 h-100">
                                    <div class="text-muted small">Echeance</div>
                                    <strong><?= htmlspecialchars($task['date_echeance'] ?? '-') ?></strong>
                                </div>
                            </div>
                        </div>

                        <form method="post" class="row g-3">
                            <div class="col-12">
                                <label class="form-label-modern" for="pourcentage">Avancement (%)</label>
                                <input type="range" class="form-range" min="0" max="100" id="pourcentage" name="pourcentage" value="<?= (int)$task['pourcentage'] ?>">
                                <div class="progress-cell mt-2">
                                    <div class="progress-bar-container">
                                        <div class="progress-bar-fill" id="progressPreview" style="width: <?= (int)$task['pourcentage'] ?>%;"></div>
                                    </div>
                                    <span class="progress-text" id="progressValue"><?= (int)$task['pourcentage'] ?>%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-modern" for="statut">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <?php foreach (['a_faire' => 'A faire', 'en_cours' => 'En cours', 'en_revision' => 'En revision', 'termine' => 'Termine', 'bloque' => 'Bloque'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $task['statut'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn-modern btn-primary-modern"><i class="bi bi-save"></i> Sauvegarder</button>
                                <?php if ($_SESSION['role'] === 'ingenieur'): ?>
                                    <a href="rapport_create.php?tache_id=<?= $taskId ?>" class="btn-modern btn-success-modern"><i class="bi bi-file-earmark-plus"></i> Soumettre un rapport</a>
                                    <a href="alertes.php?tache_id=<?= $taskId ?>" class="btn-modern btn-outline-modern"><i class="bi bi-exclamation-triangle"></i> Signaler un probleme</a>
                                <?php else: ?>
                                    <a href="../admin/rapports.php" class="btn-modern btn-success-modern"><i class="bi bi-file-earmark-text"></i> Voir les rapports</a>
                                    <a href="../admin/taches.php" class="btn-modern btn-outline-modern"><i class="bi bi-list-task"></i> Gestion des taches</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
<?php renderAppLayoutEnd(); ?>

<script>
const progressInput = document.getElementById('pourcentage');
progressInput?.addEventListener('input', function() {
    document.getElementById('progressPreview').style.width = this.value + '%';
    document.getElementById('progressValue').textContent = this.value + '%';
});
</script>

<?php require_once '../includes/footer.php'; ?>
