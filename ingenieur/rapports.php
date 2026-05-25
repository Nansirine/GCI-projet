<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$stmt = $pdo->prepare('
    SELECT r.*, p.nom AS projet_nom
    FROM rapports r
    JOIN projets p ON p.id = r.projet_id
    WHERE r.ingenieur_id = ?
    ORDER BY r.date_soumission DESC
');
$stmt->execute([$user_id]);
$rapports = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('rapports', 'bi-file-earmark-text', 'Mes rapports'); ?>
<div class="page-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Mes Rapports</h2>
        <a href="rapport_create.php" class="btn btn-success">+ Nouveau Rapport</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Titre</th><th>Projet</th><th>Statut</th><th>Date soumission</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (!$rapports): ?>
                    <tr><td colspan="5" class="text-center">Aucun rapport soumis.</td></tr>
                <?php endif; ?>
                <?php foreach ($rapports as $rapport): ?>
                    <tr>
                        <td><i class="bi <?= documentIcon($rapport['fichier_joint']) ?>"></i> <?= htmlspecialchars($rapport['titre']) ?></td>
                        <td><?= htmlspecialchars($rapport['projet_nom']) ?></td>
                        <td><?= getBadgeStatut($rapport['statut']) ?></td>
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
