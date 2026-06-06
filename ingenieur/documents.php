<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';
ensureDocumentDecisionColumns($pdo);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_action'], $_POST['plan_id'])) {
    $planId = (int)$_POST['plan_id'];
    $action = $_POST['plan_action'];

    $stmtPlan = $pdo->prepare('
        SELECT pl.*, p.client_id
        FROM plans pl
        JOIN projets p ON p.id = pl.projet_id
        WHERE pl.id = ?
          AND (
              EXISTS (SELECT 1 FROM affectations a WHERE a.projet_id = p.id AND a.utilisateur_id = ?)
              OR EXISTS (SELECT 1 FROM taches t WHERE t.projet_id = p.id AND t.assigne_a = ?)
          )
    ');
    $stmtPlan->execute([$planId, $user_id, $user_id]);
    $plan = $stmtPlan->fetch();

    if (!$plan) {
        $message = 'Plan introuvable ou acces refuse.';
        $messageType = 'danger';
    } elseif ($action === 'valider') {
        $pdo->prepare("UPDATE plans SET statut = 'valide', commentaire = ? WHERE id = ?")->execute([sanitize($_POST['commentaire'] ?? ''), $planId]);
        createNotification($pdo, (int)$plan['dessinateur_id'], 'Plan valide', 'Votre plan "' . $plan['titre'] . '" a ete valide.', 'succes', '/dessinateur/plans.php');
        $message = 'Plan valide avec succes. Vous pouvez maintenant le partager au client.';
        $messageType = 'success';
    } elseif ($action === 'rejeter') {
        $commentaire = sanitize($_POST['commentaire'] ?? 'Corrections demandees.');
        $pdo->prepare("UPDATE plans SET statut = 'rejete', commentaire = ?, partage_client = 0 WHERE id = ?")->execute([$commentaire, $planId]);
        createNotification($pdo, (int)$plan['dessinateur_id'], 'Plan rejete', 'Votre plan "' . $plan['titre'] . '" a ete rejete : ' . $commentaire, 'erreur', '/dessinateur/plans.php');
        $message = 'Plan rejete avec commentaire.';
        $messageType = 'warning';
    } elseif ($action === 'partager' && $plan['statut'] === 'valide') {
        $pdo->prepare('UPDATE plans SET partage_client = 1 WHERE id = ?')->execute([$planId]);
        createNotification($pdo, (int)$plan['client_id'], 'Nouveau plan disponible', 'Un plan valide est disponible pour votre projet : "' . $plan['titre'] . '".', 'info', '/client/plans.php');
        $message = 'Plan partage avec le client.';
        $messageType = 'success';
    } else {
        $message = 'Action impossible pour le statut actuel du plan.';
        $messageType = 'danger';
    }
}

$projetId = (int)($_GET['projet_id'] ?? 0);
$typePlan = $_GET['type_plan'] ?? '';
$documentType = $_GET['document_type'] ?? '';

$planWhere = ['(
    EXISTS (SELECT 1 FROM affectations a WHERE a.projet_id = p.id AND a.utilisateur_id = :plan_affectation_user)
    OR EXISTS (SELECT 1 FROM taches t WHERE t.projet_id = p.id AND t.assigne_a = :plan_task_user)
)'];
$planParams = [
    ':plan_affectation_user' => $user_id,
    ':plan_task_user' => $user_id,
];
$rapportWhere = ['(
    EXISTS (SELECT 1 FROM affectations a WHERE a.projet_id = p.id AND a.utilisateur_id = :rapport_affectation_user)
    OR EXISTS (SELECT 1 FROM taches t WHERE t.projet_id = p.id AND t.assigne_a = :rapport_task_user)
)'];
$rapportParams = [
    ':rapport_affectation_user' => $user_id,
    ':rapport_task_user' => $user_id,
];

if ($projetId) {
    $planWhere[] = 'pl.projet_id = :projet_id';
    $planParams[':projet_id'] = $projetId;
    $rapportWhere[] = 'r.projet_id = :rapport_projet_id';
    $rapportParams[':rapport_projet_id'] = $projetId;
}
if ($typePlan !== '') {
    $planWhere[] = 'pl.type_plan = :type_plan';
    $planParams[':type_plan'] = $typePlan;
}

$plans = [];
if ($documentType !== 'rapport') {
    $stmt = $pdo->prepare('
        SELECT DISTINCT pl.*, p.nom AS projet_nom, u.nom AS auteur_nom, u.prenom AS auteur_prenom
        FROM plans pl
        JOIN projets p ON p.id = pl.projet_id
        JOIN utilisateurs u ON u.id = pl.dessinateur_id
        WHERE ' . implode(' AND ', $planWhere) . '
        ORDER BY pl.date_upload DESC
    ');
    $stmt->execute($planParams);
    $plans = $stmt->fetchAll();
}

$rapports = [];
if ($documentType !== 'plan' && $typePlan === '') {
    $stmt = $pdo->prepare('
        SELECT DISTINCT r.*, p.nom AS projet_nom, u.nom AS auteur_nom, u.prenom AS auteur_prenom
        FROM rapports r
        JOIN projets p ON p.id = r.projet_id
        JOIN utilisateurs u ON u.id = r.ingenieur_id
        WHERE ' . implode(' AND ', $rapportWhere) . '
        ORDER BY r.date_soumission DESC
    ');
    $stmt->execute($rapportParams);
    $rapports = $stmt->fetchAll();
}

$projets = $pdo->prepare("
    SELECT DISTINCT p.id, p.nom
    FROM projets p
    WHERE EXISTS (SELECT 1 FROM affectations a WHERE a.projet_id = p.id AND a.utilisateur_id = ?)
       OR EXISTS (SELECT 1 FROM taches t WHERE t.projet_id = p.id AND t.assigne_a = ?)
    ORDER BY p.nom
");
$projets->execute([$user_id, $user_id]);
$projets = $projets->fetchAll();
$typesPlans = ['architectural' => 'Architectural', 'structural' => 'Structural', 'electrique' => 'Electrique', 'plomberie' => 'Plomberie', 'autre' => 'Autre'];
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('documents', 'bi-folder2', 'Documents'); ?>
<div class="page-container">
    <h2 class="fw-bold mb-4">Documents et fichiers</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="projet_id">
                <option value="">Tous projets</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= $projetId === (int)$projet['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($projet['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="document_type">
                <option value="">Tous fichiers</option>
                <option value="plan" <?= $documentType === 'plan' ? 'selected' : '' ?>>Plans</option>
                <option value="rapport" <?= $documentType === 'rapport' ? 'selected' : '' ?>>Rapports</option>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="type_plan">
                <option value="">Tous types de plans</option>
                <?php foreach ($typesPlans as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $typePlan === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
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
                <tr><th>Fichier</th><th>Type</th><th>Projet</th><th>Auteur</th><th>Statut</th><th>Client</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (!$plans && !$rapports): ?>
                    <tr><td colspan="8" class="text-center">Aucun fichier disponible.</td></tr>
                <?php endif; ?>

                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                        <td>Plan - <?= htmlspecialchars($typesPlans[$plan['type_plan']] ?? $plan['type_plan']) ?></td>
                        <td><?= htmlspecialchars($plan['projet_nom']) ?></td>
                        <td><?= htmlspecialchars($plan['auteur_prenom'] . ' ' . $plan['auteur_nom']) ?></td>
                        <td><?= getBadgeStatut($plan['statut']) ?></td>
                        <td>
                            <?= (int)$plan['partage_client'] === 1 ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Non visible</span>' ?>
                            <?= getBadgeStatut($plan['client_decision'] ?? 'en_attente') ?>
                        </td>
                        <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                        <td>
                            <?= renderDocumentActions('plan', (int)$plan['id'], $plan['fichier'], $plan['titre']) ?>
                            <?php if (!empty($plan['commentaire'])): ?>
                                <div class="small text-muted mt-2">Commentaire: <?= htmlspecialchars($plan['commentaire']) ?></div>
                            <?php endif; ?>
                            <form method="post" class="mt-2">
                                <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                                <?php if ($plan['statut'] === 'soumis'): ?>
                                    <textarea class="form-control form-control-sm mb-2" name="commentaire" rows="2" placeholder="Commentaire ou erreur a signaler"></textarea>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button class="btn btn-sm btn-success" name="plan_action" value="valider">Valider</button>
                                        <button class="btn btn-sm btn-outline-danger" name="plan_action" value="rejeter">Rejeter / signaler erreur</button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($plan['statut'] === 'valide' && (int)$plan['partage_client'] === 0): ?>
                                    <button class="btn btn-sm btn-primary" name="plan_action" value="partager">Partager client</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php foreach ($rapports as $rapport): ?>
                    <tr>
                        <td><i class="bi <?= documentIcon($rapport['fichier_joint']) ?>"></i> <?= htmlspecialchars($rapport['titre']) ?></td>
                        <td>Rapport</td>
                        <td><?= htmlspecialchars($rapport['projet_nom']) ?></td>
                        <td><?= htmlspecialchars($rapport['auteur_prenom'] . ' ' . $rapport['auteur_nom']) ?></td>
                        <td><?= getBadgeStatut($rapport['statut']) ?></td>
                        <td><?= getBadgeStatut($rapport['client_decision'] ?? 'en_attente') ?></td>
                        <td><?= htmlspecialchars(formatDatetime($rapport['date_soumission'])) ?></td>
                        <td><?= renderDocumentActions('rapport', (int)$rapport['id'], $rapport['fichier_joint'], $rapport['titre']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
