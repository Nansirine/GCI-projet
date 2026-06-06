<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';
ensureDocumentDecisionColumns($pdo);

$where = ['pl.dessinateur_id = :user_id'];
$params = [':user_id' => $user_id];

if (!empty($_GET['statut'])) {
    $where[] = 'pl.statut = :statut';
    $params[':statut'] = $_GET['statut'];
}
if (!empty($_GET['projet_id'])) {
    $where[] = 'pl.projet_id = :projet_id';
    $params[':projet_id'] = (int)$_GET['projet_id'];
}
if (!empty($_GET['type_plan'])) {
    $where[] = 'pl.type_plan = :type_plan';
    $params[':type_plan'] = $_GET['type_plan'];
}

$stmt = $pdo->prepare('
    SELECT pl.*, p.nom AS projet_nom
    FROM plans pl
    JOIN projets p ON p.id = pl.projet_id
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY pl.date_upload DESC
');
$stmt->execute($params);
$plans = $stmt->fetchAll();

$projets = $pdo->prepare("
    SELECT DISTINCT p.id, p.nom
    FROM projets p
    JOIN affectations a ON a.projet_id = p.id
    WHERE a.utilisateur_id = ?
    ORDER BY p.nom
");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();
$typesPlans = ['architectural' => 'Architectural', 'structural' => 'Structural', 'electrique' => 'Electrique', 'plomberie' => 'Plomberie', 'autre' => 'Autre'];
$statuts = ['brouillon', 'soumis', 'valide', 'rejete', 'archive'];
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('plans', 'bi-file-earmark', 'Plans'); ?>
<div class="page-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Mes plans</h2>
        <a href="plan_upload.php" class="btn btn-success">+ Deposer un plan</a>
    </div>
    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">Plan depose avec succes.</div>
    <?php endif; ?>
    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="statut">
                <option value="">Tous statuts</option>
                <?php foreach ($statuts as $statut): ?>
                    <option value="<?= $statut ?>" <?= ($_GET['statut'] ?? '') === $statut ? 'selected' : '' ?>>
                        <?= strip_tags(getBadgeStatut($statut)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="projet_id">
                <option value="">Tous projets</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= (string)($_GET['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($projet['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="type_plan">
                <option value="">Tous types</option>
                <?php foreach ($typesPlans as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($_GET['type_plan'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
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
                <tr><th>Projet</th><th>Titre</th><th>Type</th><th>Version</th><th>Statut</th><th>Partage client</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (!$plans): ?>
                    <tr><td colspan="8" class="text-center">Aucun plan trouve.</td></tr>
                <?php endif; ?>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><?= htmlspecialchars($plan['projet_nom']) ?></td>
                        <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                        <td><?= htmlspecialchars($typesPlans[$plan['type_plan']] ?? $plan['type_plan']) ?></td>
                        <td>v<?= (int)$plan['version'] ?></td>
                        <td><?= getBadgeStatut($plan['statut']) ?></td>
                        <td>
                            <?= (int)$plan['partage_client'] === 1 ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Non visible</span>' ?>
                            <?= getBadgeStatut($plan['client_decision'] ?? 'en_attente') ?>
                        </td>
                        <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                        <td>
                            <?= renderDocumentActions('plan', (int)$plan['id'], $plan['fichier'], $plan['titre']) ?>
                            <a href="plan_detail.php?id=<?= (int)$plan['id'] ?>" class="btn btn-sm btn-outline-primary mt-1">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
