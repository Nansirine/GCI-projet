<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';
ensureDocumentDecisionColumns($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'], $_POST['decision'])) {
    $planId = (int)$_POST['plan_id'];
    $decision = $_POST['decision'];
    $commentaire = sanitize($_POST['commentaire_client'] ?? '');

    if (in_array($decision, ['approuve', 'refuse'], true)) {
        $stmt = $pdo->prepare('
            UPDATE plans pl
            JOIN projets p ON p.id = pl.projet_id
            SET pl.client_decision = ?, pl.commentaire_client = ?, pl.date_decision_client = NOW()
            WHERE pl.id = ? AND p.client_id = ? AND pl.partage_client = 1
        ');
        $stmt->execute([$decision, $commentaire, $planId, $user_id]);

        $info = $pdo->prepare('
            SELECT pl.titre, pl.dessinateur_id, p.nom AS projet_nom, p.admin_id
            FROM plans pl
            JOIN projets p ON p.id = pl.projet_id
            WHERE pl.id = ?
        ');
        $info->execute([$planId]);
        if ($plan = $info->fetch()) {
            $texte = $decision === 'approuve'
                ? 'Le client a approuve le plan "' . $plan['titre'] . '".'
                : 'Le client a refuse le plan "' . $plan['titre'] . '"' . ($commentaire ? ' : ' . $commentaire : '.');
            createNotification($pdo, (int)$plan['admin_id'], 'Decision client sur un plan', $texte, $decision === 'approuve' ? 'succes' : 'avertissement', '/admin/projet_detail.php');
            createNotification($pdo, (int)$plan['dessinateur_id'], 'Decision client sur un plan', $texte, $decision === 'approuve' ? 'succes' : 'avertissement', '/dessinateur/plans.php');
        }
    }

    header('Location: plans.php?decision=1');
    exit;
}

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
    <?php if (isset($_GET['decision'])): ?>
        <div class="alert alert-success">Votre decision a ete enregistree.</div>
    <?php endif; ?>

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
                    <tr><th>Titre</th><th>Type</th><th>Version</th><th>Decision</th><th>Date de partage</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!$plans): ?>
                        <tr><td colspan="6" class="text-center">Aucun plan disponible.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                            <td><?= htmlspecialchars($typesPlans[$plan['type_plan']] ?? $plan['type_plan']) ?></td>
                            <td>v<?= (int)$plan['version'] ?></td>
                            <td><?= getBadgeStatut($plan['client_decision'] ?? 'en_attente') ?></td>
                            <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                            <td>
                                <?= renderClientDocumentActions('plan', (int)$plan['id'], $plan['fichier'], $plan['client_decision'] ?? 'en_attente', $plan['titre']) ?>
                                <form method="post" class="d-flex flex-wrap gap-1 mt-2">
                                    <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                                    <input type="text" name="commentaire_client" class="form-control form-control-sm" placeholder="Commentaire optionnel">
                                    <button class="btn btn-sm btn-success" name="decision" value="approuve">Approuver</button>
                                    <button class="btn btn-sm btn-outline-danger" name="decision" value="refuse">Refuser</button>
                                </form>
                            </td>
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
