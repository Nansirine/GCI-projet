<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/layout.php';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alerte_id'], $_POST['statut'])) {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $alerteId = (int)$_POST['alerte_id'];
    $statut = $_POST['statut'];
    if ($alerteId > 0 && in_array($statut, ['ouvert', 'en_traitement', 'resolu'], true)) {
        $dateResolution = $statut === 'resolu' ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare('UPDATE alertes SET statut = ?, date_resolution = ? WHERE id = ?');
        $stmt->execute([$statut, $dateResolution, $alerteId]);
        header('Location: alertes.php?updated=1');
        exit;
    }
    $message = 'Impossible de mettre a jour cette alerte.';
    $messageType = 'danger';
}

$alertesStmt = $pdo->query("
    SELECT a.*, p.nom AS projet_nom, t.titre AS tache_titre,
           u.prenom AS auteur_prenom, u.nom AS auteur_nom, u.role AS auteur_role
    FROM alertes a
    JOIN projets p ON p.id = a.projet_id
    LEFT JOIN taches t ON t.id = a.tache_id
    JOIN utilisateurs u ON u.id = a.signale_par
    ORDER BY FIELD(a.statut, 'ouvert', 'en_traitement', 'resolu'),
             FIELD(a.niveau, 'critique', 'avertissement', 'info'),
             a.date_creation DESC
");
$alertes = $alertesStmt->fetchAll();

$budgetAlertsStmt = $pdo->query("
    SELECT p.id, p.nom, p.budget,
           COALESCE(SUM(f.montant_total), 0) AS realise,
           ROUND((COALESCE(SUM(f.montant_total), 0) / NULLIF(p.budget, 0)) * 100, 1) AS taux
    FROM projets p
    LEFT JOIN factures f ON f.projet_id = p.id AND f.statut <> 'annulee'
    WHERE p.budget > 0
    GROUP BY p.id, p.nom, p.budget
    HAVING realise >= p.budget * 0.9
    ORDER BY taux DESC, p.nom
");
$budgetAlerts = $budgetAlertsStmt->fetchAll();

$taskAlertsStmt = $pdo->query("
    SELECT t.id, t.titre, t.statut, t.priorite, t.date_echeance, t.pourcentage,
           p.nom AS projet_nom, u.prenom, u.nom
    FROM taches t
    JOIN projets p ON p.id = t.projet_id
    JOIN utilisateurs u ON u.id = t.assigne_a
    WHERE t.statut = 'bloque'
       OR (t.statut <> 'termine' AND t.date_echeance < CURDATE())
    ORDER BY t.date_echeance ASC, FIELD(t.priorite, 'urgente', 'haute', 'moyenne', 'basse')
");
$taskAlerts = $taskAlertsStmt->fetchAll();
?>
<?php renderAppLayoutStart('alertes', 'bi-exclamation-triangle', 'Alertes'); ?>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-exclamation-triangle"></i> Alertes et erreurs</h1>
            <p class="page-subtitle">Pilotage des problemes signales, retards de taches et seuils budgetaires.</p>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Alerte mise a jour.</div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-card-icon danger"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="stat-card-label">Alertes ouvertes</div>
            <div class="stat-card-value"><?= count(array_filter($alertes, fn($a) => $a['statut'] !== 'resolu')) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i class="bi bi-clock-history"></i></div>
            <div class="stat-card-label">Taches a surveiller</div>
            <div class="stat-card-value"><?= count($taskAlerts) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon primary"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-card-label">Budgets >= 90%</div>
            <div class="stat-card-value"><?= count($budgetAlerts) ?></div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="section-header">
            <div class="section-title"><i class="bi bi-bug"></i> Alertes signalees</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Probleme</th>
                        <th>Projet</th>
                        <th>Tache</th>
                        <th>Niveau</th>
                        <th>Signale par</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$alertes): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune alerte signalee.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($alertes as $alerte): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($alerte['titre']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($alerte['description']) ?></div>
                            </td>
                            <td><a href="projet_detail.php?id=<?= (int)$alerte['projet_id'] ?>"><?= htmlspecialchars($alerte['projet_nom']) ?></a></td>
                            <td><?= htmlspecialchars($alerte['tache_titre'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $alerte['niveau'] === 'critique' ? 'danger' : ($alerte['niveau'] === 'avertissement' ? 'warning' : 'info') ?>"><?= htmlspecialchars($alerte['niveau']) ?></span></td>
                            <td><?= htmlspecialchars(trim($alerte['auteur_prenom'] . ' ' . $alerte['auteur_nom']) . ' - ' . $alerte['auteur_role']) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="alerte_id" value="<?= (int)$alerte['id'] ?>">
                                    <select class="form-select form-select-sm" name="statut">
                                        <?php foreach (['ouvert' => 'Ouvert', 'en_traitement' => 'En traitement', 'resolu' => 'Resolu'] as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $alerte['statut'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-primary"><i class="bi bi-save"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="section-header">
            <div class="section-title"><i class="bi bi-cash-coin"></i> Alertes budgetaires</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Projet</th><th>Provisionnel</th><th>Realise facture</th><th>Taux</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (!$budgetAlerts): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun budget n'a atteint le seuil de 90%.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($budgetAlerts as $budget): ?>
                        <tr>
                            <td><?= htmlspecialchars($budget['nom']) ?></td>
                            <td><?= formatMontant((float)$budget['budget']) ?></td>
                            <td><?= formatMontant((float)$budget['realise']) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($budget['taux']) ?>%</span></td>
                            <td><a href="projet_detail.php?id=<?= (int)$budget['id'] ?>" class="btn-modern btn-outline-modern btn-sm">Voir projet</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <div class="section-title"><i class="bi bi-hourglass-split"></i> Taches en retard ou bloquees</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Tache</th><th>Projet</th><th>Responsable</th><th>Priorite</th><th>Statut</th><th>Echeance</th><th>%</th></tr>
                </thead>
                <tbody>
                    <?php if (!$taskAlerts): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucune tache critique.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($taskAlerts as $tache): ?>
                        <tr>
                            <td><a href="../ingenieur/tache_detail.php?id=<?= (int)$tache['id'] ?>"><?= htmlspecialchars($tache['titre']) ?></a></td>
                            <td><?= htmlspecialchars($tache['projet_nom']) ?></td>
                            <td><?= htmlspecialchars(trim($tache['prenom'] . ' ' . $tache['nom'])) ?></td>
                            <td><?= getPriorityBadge($tache['priorite']) ?></td>
                            <td><?= getBadgeStatut($tache['statut']) ?></td>
                            <td><?= formatDate($tache['date_echeance']) ?></td>
                            <td><?= (int)$tache['pourcentage'] ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
