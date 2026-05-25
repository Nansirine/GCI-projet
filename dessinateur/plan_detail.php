<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('
    SELECT pl.*, p.nom AS projet_nom
    FROM plans pl
    JOIN projets p ON p.id = pl.projet_id
    WHERE pl.id = ? AND pl.dessinateur_id = ?
');
$stmt->execute([$planId, $user_id]);
$plan = $stmt->fetch();

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('plans', 'bi-file-earmark', 'Detail du plan'); ?>
<div class="page-container">
    <?php if (!$plan): ?>
        <div class="alert alert-danger">Plan introuvable.</div>
    <?php else: ?>
        <div class="page-header">
            <h1 class="page-title"><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></h1>
            <div class="page-actions">
                <a href="plans.php" class="btn-modern btn-outline-modern">Retour</a>
            </div>
        </div>

        <div class="section-card mb-4">
            <div class="row g-3 mb-3">
                <div class="col-md-4"><strong>Projet</strong><div><?= htmlspecialchars($plan['projet_nom']) ?></div></div>
                <div class="col-md-2"><strong>Version</strong><div>v<?= (int)$plan['version'] ?></div></div>
                <div class="col-md-3"><strong>Statut</strong><div><?= getBadgeStatut($plan['statut']) ?></div></div>
                <div class="col-md-3"><strong>Client</strong><div><?= (int)$plan['partage_client'] === 1 ? 'Visible' : 'Non visible' ?></div></div>
            </div>
            <?= renderDocumentPreview('plan', (int)$plan['id'], $plan['fichier'], $plan['titre']) ?>
        </div>
    <?php endif; ?>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
