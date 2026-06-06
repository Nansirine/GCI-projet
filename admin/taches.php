<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/functions.php';
ensureTaskDependencyColumn($pdo);
require_once '../includes/header.php';
require_once '../includes/layout.php';

$statuts = ['a_faire', 'en_cours', 'en_revision', 'termine', 'bloque'];
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_id'])) {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $taskId = (int)$_POST['update_task_id'];
    $pourcentage = max(0, min(100, (int)($_POST['pourcentage'] ?? 0)));
    $newStatut = $_POST['statut'] ?? 'a_faire';

    if ($taskId > 0 && in_array($newStatut, $statuts, true)) {
        $taskProject = $pdo->prepare('SELECT projet_id FROM taches WHERE id = ?');
        $taskProject->execute([$taskId]);
        $projectIdForUpdate = (int)$taskProject->fetchColumn();

        if ($projectIdForUpdate) {
            $dateCompletion = $newStatut === 'termine' ? date('Y-m-d') : null;
            $stmt = $pdo->prepare('UPDATE taches SET pourcentage = ?, statut = ?, date_completion = ? WHERE id = ?');
            $stmt->execute([$pourcentage, $newStatut, $dateCompletion, $taskId]);
            updateProjectProgress($pdo, $projectIdForUpdate);
            header('Location: taches.php?updated=1');
            exit;
        }
    }

    $message = 'Impossible de mettre a jour cette tache.';
    $messageType = 'danger';
}

$statut = $_GET['statut'] ?? '';
$projetId = (int)($_GET['projet_id'] ?? 0);
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if ($statut !== '' && in_array($statut, $statuts, true)) {
    $where[] = 't.statut = :statut';
    $params[':statut'] = $statut;
}

if ($projetId > 0) {
    $where[] = 't.projet_id = :projet_id';
    $params[':projet_id'] = $projetId;
}

if ($search !== '') {
    $where[] = '(t.titre LIKE :search OR p.nom LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$sql = "SELECT t.id, t.titre, t.priorite, t.statut, t.pourcentage, t.date_echeance,
               p.nom AS projet_nom,
               u.nom AS assigne_nom, u.prenom AS assigne_prenom,
               dep.titre AS dependance_titre
        FROM taches t
        JOIN projets p ON p.id = t.projet_id
        JOIN utilisateurs u ON u.id = t.assigne_a
        LEFT JOIN taches dep ON dep.id = t.dependance_id";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY t.date_echeance ASC, t.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$taches = $stmt->fetchAll();

$projets = $pdo->query('SELECT id, nom FROM projets ORDER BY nom')->fetchAll();
?>
<?php renderAppLayoutStart('taches', 'bi-list-task', 'Taches'); ?>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-list-task"></i> Gestion des taches</h1>
            <p class="page-subtitle">Suivi des taches, responsables, priorites et echeances.</p>
        </div>
        <div class="page-actions">
            <a href="tache_create.php" class="btn-modern btn-success-modern"><i class="bi bi-plus-circle"></i> Nouvelle tache</a>
        </div>
    </div>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">Tache creee avec succes.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Avancement de la tache mis a jour et projet recalcule.</div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="filters-section mb-3">
        <form class="filters-row" method="get">
            <div class="filter-group">
                <label class="filter-label" for="search">Recherche</label>
                <input class="filter-input" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Titre, projet, responsable">
            </div>
            <div class="filter-group">
                <label class="filter-label" for="statut">Statut</label>
                <select class="filter-select" id="statut" name="statut">
                    <option value="">Tous</option>
                    <?php foreach ($statuts as $value): ?>
                        <option value="<?= $value ?>" <?= $statut === $value ? 'selected' : '' ?>>
                            <?= strip_tags(getBadgeStatut($value)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="projet_id">Projet</label>
                <select class="filter-select" id="projet_id" name="projet_id">
                    <option value="0">Tous les projets</option>
                    <?php foreach ($projets as $projet): ?>
                        <option value="<?= (int)$projet['id'] ?>" <?= $projetId === (int)$projet['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($projet['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn-modern btn-primary-modern" type="submit"><i class="bi bi-search"></i> Filtrer</button>
        </form>
    </div>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Projet</th>
                        <th>Responsable</th>
                        <th>Dependance</th>
                        <th>Priorite</th>
                        <th>Statut</th>
                        <th>Progression</th>
                        <th>Echeance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$taches): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Aucune tache trouvee.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($taches as $tache): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($tache['titre']) ?></td>
                            <td><?= htmlspecialchars($tache['projet_nom']) ?></td>
                            <td><?= htmlspecialchars(trim($tache['assigne_prenom'] . ' ' . $tache['assigne_nom'])) ?></td>
                            <td><?= htmlspecialchars($tache['dependance_titre'] ?? '-') ?></td>
                            <td><?= getPriorityBadge($tache['priorite']) ?></td>
                            <td>
                                <form method="post" id="progressForm<?= (int)$tache['id'] ?>" class="d-flex flex-column gap-2" style="min-width: 150px;">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="update_task_id" value="<?= (int)$tache['id'] ?>">
                                    <select class="form-select form-select-sm" name="statut">
                                        <?php foreach ($statuts as $value): ?>
                                            <option value="<?= $value ?>" <?= $tache['statut'] === $value ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $value)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                    <div class="d-flex align-items-center gap-2" style="min-width: 180px;">
                                        <input type="number" class="form-control form-control-sm" name="pourcentage" min="0" max="100" value="<?= (int)$tache['pourcentage'] ?>" form="progressForm<?= (int)$tache['id'] ?>">
                                        <span class="text-muted">%</span>
                                        <button class="btn btn-sm btn-primary" title="Enregistrer" form="progressForm<?= (int)$tache['id'] ?>"><i class="bi bi-save"></i></button>
                                    </div>
                            </td>
                            <td><?= formatDate($tache['date_echeance']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="../ingenieur/tache_detail.php?id=<?= (int)$tache['id'] ?>" class="btn-action btn-action-view" title="Voir"><i class="bi bi-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
