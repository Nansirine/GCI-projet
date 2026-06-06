<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$projetsStmt = $pdo->prepare("SELECT DISTINCT p.id, p.nom
                              FROM projets p
                              LEFT JOIN affectations a ON a.projet_id = p.id
                              LEFT JOIN taches t ON t.projet_id = p.id
                              WHERE a.utilisateur_id = ? OR t.assigne_a = ?
                              ORDER BY p.nom");
$projetsStmt->execute([$user_id, $user_id]);
$projets = $projetsStmt->fetchAll();

$statut = $_GET['statut'] ?? '';
$projetId = (int)($_GET['projet_id'] ?? 0);
$where = ['t.assigne_a = ?'];
$params = [$user_id];

if ($statut !== '' && in_array($statut, ['a_faire', 'en_cours', 'en_revision', 'termine', 'bloque'], true)) {
    $where[] = 't.statut = ?';
    $params[] = $statut;
}

if ($projetId > 0) {
    $where[] = 't.projet_id = ?';
    $params[] = $projetId;
}

$stmt = $pdo->prepare("SELECT t.id, t.titre, t.priorite, t.statut, t.pourcentage, t.date_echeance, p.nom AS projet_nom
                       FROM taches t
                       JOIN projets p ON p.id = t.projet_id
                       WHERE " . implode(' AND ', $where) . "
                       ORDER BY t.date_echeance ASC");
$stmt->execute($params);
$taches = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('taches', 'bi-list-task', 'Mes taches'); ?>
<div class="page-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Mes taches</h2>
    </div>
    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="statut">
                <option value="">Tous</option>
                <option value="a_faire" <?= $statut === 'a_faire' ? 'selected' : '' ?>>A faire</option>
                <option value="en_cours" <?= $statut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                <option value="en_revision" <?= $statut === 'en_revision' ? 'selected' : '' ?>>En revision</option>
                <option value="termine" <?= $statut === 'termine' ? 'selected' : '' ?>>Termine</option>
                <option value="bloque" <?= $statut === 'bloque' ? 'selected' : '' ?>>Bloque</option>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="projet_id">
                <option value="">Tous projets</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= $projetId === (int)$projet['id'] ? 'selected' : '' ?>><?= htmlspecialchars($projet['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filtrer</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Titre</th><th>Priorite</th><th>Statut</th><th>%</th><th>Echeance</th><th>Actions</th></tr>
            </thead>
            <tbody id="taches-list">
                <?php if (!$taches): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune tache trouvee.</td></tr>
                <?php endif; ?>
                <?php foreach ($taches as $tache): ?>
                    <tr>
                        <td><?= htmlspecialchars($tache['projet_nom']) ?></td>
                        <td><?= htmlspecialchars($tache['titre']) ?></td>
                        <td><?= getPriorityBadge($tache['priorite']) ?></td>
                        <td><?= getBadgeStatut($tache['statut']) ?></td>
                        <td><?= (int)$tache['pourcentage'] ?>%</td>
                        <td><?= formatDate($tache['date_echeance']) ?></td>
                        <td><a href="tache_detail.php?id=<?= (int)$tache['id'] ?>" class="btn-modern btn-outline-modern"><i class="bi bi-eye"></i> Voir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
