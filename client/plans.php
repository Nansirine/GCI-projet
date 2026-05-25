<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$where = ['p.client_id = :client_id', 'pl.partage_client = 1'];
$params = [':client_id' => $user_id];
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
$typesPlans = ['architectural' => 'Architectural', 'structural' => 'Structural', 'electrique' => 'Electrique', 'plomberie' => 'Plomberie', 'autre' => 'Autre'];
require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('plans', 'bi-file-earmark', 'Plans'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-file-earmark"></i> Plans disponibles</h1>
    </div>

    <div class="filters-section">
        <form class="filters-row">
            <div class="filter-group">
                <label class="filter-label" for="type_plan">Type de plan</label>
                <select class="filter-select" id="type_plan" name="type_plan">
                    <option value="">Tous les types</option>
                    <?php foreach ($typesPlans as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($_GET['type_plan'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn-filter"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr><th>Titre</th><th>Type</th><th>Version</th><th>Date de partage</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!$plans): ?>
                        <tr><td colspan="5" class="text-center">Aucun plan disponible.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                            <td><?= htmlspecialchars($typesPlans[$plan['type_plan']] ?? $plan['type_plan']) ?></td>
                            <td>v<?= (int)$plan['version'] ?></td>
                            <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                            <td><?= renderDocumentActions('plan', (int)$plan['id'], $plan['fichier'], $plan['titre']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="preview-plan" class="mt-4"></div>
    <div id="no-plan-msg" class="alert alert-info d-none mt-4">Aucun plan n'est encore disponible. Revenez bientot.</div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
