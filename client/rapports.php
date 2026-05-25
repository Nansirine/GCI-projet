<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$stmt = $pdo->prepare('
    SELECT r.*, p.nom AS projet_nom, u.nom AS ingenieur_nom, u.prenom AS ingenieur_prenom
    FROM rapports r
    JOIN projets p ON p.id = r.projet_id
    JOIN utilisateurs u ON u.id = r.ingenieur_id
    WHERE p.client_id = ? AND r.statut = "valide"
    ORDER BY COALESCE(r.date_validation, r.date_soumission) DESC
');
$stmt->execute([$user_id]);
$rapports = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('rapports', 'bi-file-earmark-text', 'Rapports'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-file-earmark-text"></i> Rapports valides</h1>
    </div>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr><th>Titre</th><th>Ingenieur</th><th>Date validation</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!$rapports): ?>
                        <tr><td colspan="4" class="text-center">Aucun rapport valide disponible.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rapports as $rapport): ?>
                        <tr>
                            <td><i class="bi <?= documentIcon($rapport['fichier_joint']) ?>"></i> <?= htmlspecialchars($rapport['titre']) ?></td>
                            <td><?= htmlspecialchars($rapport['ingenieur_prenom'] . ' ' . $rapport['ingenieur_nom']) ?></td>
                            <td><?= htmlspecialchars($rapport['date_validation'] ? formatDatetime($rapport['date_validation']) : formatDatetime($rapport['date_soumission'])) ?></td>
                            <td><?= renderDocumentActions('rapport', (int)$rapport['id'], $rapport['fichier_joint'], $rapport['titre']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="no-rapport-msg" class="alert alert-info d-none mt-4">Aucun rapport valide disponible pour le moment.</div>

    <div class="modal fade modal-modern" id="modalRapport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Lecture du rapport</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="rapport-content"></div>
                <div class="modal-footer"><button class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Fermer</button></div>
            </div>
        </div>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
