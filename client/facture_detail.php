<?php
require_once '../includes/auth.php';
checkRole(['client']);
require_once '../config/database.php';

$factureId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT f.*, p.nom AS projet_nom FROM factures f JOIN projets p ON f.projet_id = p.id WHERE f.id = ? AND f.client_id = ?");
$stmt->execute([$factureId, $_SESSION['user_id']]);
$facture = $stmt->fetch();

if (!$facture) {
    require_once '../includes/header.php';
    require_once '../includes/layout.php';
    renderAppLayoutStart('factures', 'bi-receipt', 'Facture');
    echo '<div class="alert alert-danger">Facture introuvable.</div>';
    renderAppLayoutEnd();
    require_once '../includes/footer.php';
    exit();
}

$lines = $pdo->prepare('SELECT * FROM lignes_facture WHERE facture_id = ? ORDER BY ordre, id');
$lines->execute([$factureId]);
$lines = $lines->fetchAll();
$payments = $pdo->prepare('SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement DESC');
$payments->execute([$factureId]);
$payments = $payments->fetchAll();

// Traitement du formulaire de paiement client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['montant_client'])) {
    $stmt = $pdo->prepare("INSERT INTO paiements (facture_id, client_id, montant, mode_paiement, reference, statut, date_paiement, commentaire) VALUES (?, ?, ?, ?, ?, 'en_attente', ?, ?)");
    $stmt->execute([
        $factureId,
        $_SESSION['user_id'],
        (float)$_POST['montant_client'],
        $_POST['mode_paiement_client'] ?? 'virement',
        trim($_POST['reference_client'] ?? ''),
        $_POST['date_paiement_client'] ?? date('Y-m-d'),
        trim($_POST['commentaire_client'] ?? ''),
    ]);
    header('Location: facture_detail.php?id=' . $factureId . '&paiement=ok');
    exit();
}

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<?php renderAppLayoutStart('factures', 'bi-receipt', 'Facture'); ?>
            <div class="page-header"><h1 class="page-title"><i class="bi bi-receipt"></i> Facture <?= htmlspecialchars($facture['numero']) ?></h1><div class="page-actions"><a href="factures.php" class="btn-modern btn-outline-modern">Retour</a></div></div>
            <div class="section-card">
                <div><strong>Projet :</strong> <?= htmlspecialchars($facture['projet_nom']) ?></div>
                <div><strong>Total :</strong> <?= number_format((float)$facture['montant_total'], 0, ',', ' ') ?></div>
                <div><strong>Paye :</strong> <?= number_format((float)$facture['montant_paye'], 0, ',', ' ') ?></div>
                <div><strong>Statut :</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $facture['statut']))) ?></div>
            </div>
            <div class="section-card">
                <div class="section-title mb-3">Detail facture</div>
                <table class="modern-table"><thead><tr><th>Designation</th><th>Quantite</th><th>Prix</th><th>Montant</th></tr></thead><tbody>
                <?php foreach ($lines as $line): ?><tr><td><?= htmlspecialchars($line['designation']) ?></td><td><?= number_format((float)$line['quantite'], 2, ',', ' ') ?></td><td><?= number_format((float)$line['prix_unitaire'], 0, ',', ' ') ?></td><td><?= number_format((float)$line['montant_ligne'], 0, ',', ' ') ?></td></tr><?php endforeach; ?>
                </tbody></table>
            </div>
            <div class="section-card">
                <div class="section-title mb-3">Paiements</div>
                <table class="modern-table"><thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Reference</th><th>Statut</th></tr></thead><tbody>
                <?php if (!$payments): ?><tr><td colspan="5" class="text-center">Aucun paiement.</td></tr><?php endif; ?>
                <?php foreach ($payments as $payment): ?><tr><td><?= htmlspecialchars($payment['date_paiement']) ?></td><td><?= number_format((float)$payment['montant'], 0, ',', ' ') ?></td><td><?= htmlspecialchars($payment['mode_paiement']) ?></td><td><?= htmlspecialchars($payment['reference'] ?? '') ?></td><td><?= htmlspecialchars($payment['statut']) ?></td></tr><?php endforeach; ?>
                </tbody></table>
                </div>

                <div class="section-card">
                    <div class="section-title mb-3">Saisir un paiement</div>
                    <form method="post" class="row g-3">
                        <div class="col-md-3"><input type="number" step="0.01" min="1" name="montant_client" class="form-control-modern" placeholder="Montant" required></div>
                        <div class="col-md-3">
                            <select name="mode_paiement_client" class="filter-select">
                                <option value="virement">Virement</option><option value="especes">Especes</option><option value="cheque">Cheque</option><option value="mobile_money">Mobile money</option><option value="carte">Carte</option><option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input name="reference_client" class="form-control-modern" placeholder="Reference"></div>
                        <div class="col-md-3"><input type="date" name="date_paiement_client" class="form-control-modern" value="<?= date('Y-m-d') ?>"></div>
                        <div class="col-12"><textarea name="commentaire_client" class="form-control-modern" rows="2" placeholder="Commentaire"></textarea></div>
                        <div class="col-12"><button class="btn-modern btn-success-modern">Soumettre paiement</button></div>
                    </form>
            </div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
