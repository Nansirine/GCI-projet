<?php
require_once '../includes/auth.php';
checkRole(['client']);
require_once '../config/database.php';

$stmt = $pdo->prepare("SELECT f.*, p.nom AS projet_nom
    FROM factures f
    JOIN projets p ON f.projet_id = p.id
    WHERE f.client_id = ?
    ORDER BY f.date_creation DESC");
$stmt->execute([$_SESSION['user_id']]);
$factures = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('factures', 'bi-receipt', 'Factures'); ?>
            <div class="page-header"><h1 class="page-title"><i class="bi bi-receipt"></i> Mes factures</h1></div>
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead><tr><th>Numero</th><th>Projet</th><th>Total</th><th>Paye</th><th>Reste</th><th>Statut</th><th>Echeance</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$factures): ?><tr><td colspan="8" class="text-center">Aucune facture disponible.</td></tr><?php endif; ?>
                        <?php foreach ($factures as $facture): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($facture['numero']) ?></strong></td>
                                <td><?= htmlspecialchars($facture['projet_nom']) ?></td>
                                <td><?= number_format((float)$facture['montant_total'], 0, ',', ' ') ?></td>
                                <td><?= number_format((float)$facture['montant_paye'], 0, ',', ' ') ?></td>
                                <td><?= number_format((float)$facture['montant_total'] - (float)$facture['montant_paye'], 0, ',', ' ') ?></td>
                                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $facture['statut']))) ?></td>
                                <td><?= htmlspecialchars($facture['date_echeance']) ?></td>
                                <td><a href="facture_detail.php?id=<?= (int)$facture['id'] ?>" class="btn-action btn-action-view"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
