<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_action'], $_POST['plan_id'])) {
    $planId = (int)$_POST['plan_id'];
    $action = $_POST['plan_action'];

    $stmtPlan = $pdo->prepare('
        SELECT pl.*, p.client_id
        FROM plans pl
        JOIN projets p ON p.id = pl.projet_id
        JOIN affectations a ON a.projet_id = p.id
        WHERE pl.id = ? AND a.utilisateur_id = ?
    ');
    $stmtPlan->execute([$planId, $user_id]);
    $plan = $stmtPlan->fetch();

    if (!$plan) {
        $message = 'Plan introuvable ou acces refuse.';
        $messageType = 'danger';
    } elseif ($action === 'valider') {
        $pdo->prepare("UPDATE plans SET statut = 'valide', commentaire = ? WHERE id = ?")->execute([sanitize($_POST['commentaire'] ?? ''), $planId]);
        createNotification($pdo, (int)$plan['dessinateur_id'], 'Plan valide', 'Votre plan "' . $plan['titre'] . '" a ete valide.', 'succes', '/dessinateur/plans.php');
        $message = 'Plan valide. Vous pouvez maintenant le partager au client.';
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
    }
}

$where = ['a.utilisateur_id = :user_id'];
$params = [':user_id' => $user_id];
if (!empty($_GET['projet_id'])) {
    $where[] = 'pl.projet_id = :projet_id';
    $params[':projet_id'] = (int)$_GET['projet_id'];
}
if (!empty($_GET['type_plan'])) {
    $where[] = 'pl.type_plan = :type_plan';
    $params[':type_plan'] = $_GET['type_plan'];
}

$stmt = $pdo->prepare('
    SELECT pl.*, p.nom AS projet_nom, u.nom AS dessinateur_nom, u.prenom AS dessinateur_prenom
    FROM plans pl
    JOIN projets p ON p.id = pl.projet_id
    JOIN affectations a ON a.projet_id = p.id
    JOIN utilisateurs u ON u.id = pl.dessinateur_id
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY pl.date_upload DESC
');
$stmt->execute($params);
$plans = $stmt->fetchAll();

$projets = $pdo->prepare("SELECT DISTINCT p.id, p.nom FROM projets p JOIN affectations a ON a.projet_id = p.id WHERE a.utilisateur_id = ? ORDER BY p.nom");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();
$typesPlans = ['architectural' => 'Architectural', 'structural' => 'Structural', 'electrique' => 'Electrique', 'plomberie' => 'Plomberie', 'autre' => 'Autre'];
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('documents', 'bi-folder2', 'Documents'); ?>
<div class="page-container">
    <h2 class="fw-bold mb-4">Documents & Plans</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="projet_id">
                <option value="">Tous projets</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= (string)($_GET['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>><?= htmlspecialchars($projet['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="type_plan">
                <option value="">Tous types</option>
                <?php foreach ($typesPlans as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($_GET['type_plan'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
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
                <tr><th>Plan</th><th>Projet</th><th>Dessinateur</th><th>Statut</th><th>Client</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (!$plans): ?>
                    <tr><td colspan="7" class="text-center">Aucun document disponible.</td></tr>
                <?php endif; ?>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                        <td><?= htmlspecialchars($plan['projet_nom']) ?></td>
                        <td><?= htmlspecialchars($plan['dessinateur_prenom'] . ' ' . $plan['dessinateur_nom']) ?></td>
                        <td><?= getBadgeStatut($plan['statut']) ?></td>
                        <td><?= (int)$plan['partage_client'] === 1 ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Non visible</span>' ?></td>
                        <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                        <td>
                            <?= renderDocumentActions('plan', (int)$plan['id'], $plan['fichier'], $plan['titre']) ?>
                            <form method="post" class="d-flex flex-wrap gap-1 mt-2">
                                <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                                <?php if ($plan['statut'] === 'soumis'): ?>
                                    <button class="btn btn-sm btn-success" name="plan_action" value="valider">Valider</button>
                                    <button class="btn btn-sm btn-outline-danger" name="plan_action" value="rejeter">Rejeter</button>
                                <?php endif; ?>
                                <?php if ($plan['statut'] === 'valide' && (int)$plan['partage_client'] === 0): ?>
                                    <button class="btn btn-sm btn-primary" name="plan_action" value="partager">Partager client</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal commentaire -->
    <div class="modal fade" id="modalComment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Commenter le Plan</h5></div>
                <div class="modal-body">
                    <textarea class="form-control" rows="3" placeholder="Votre commentaire..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Envoyer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
