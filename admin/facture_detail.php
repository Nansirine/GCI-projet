<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';

$factureId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

function refreshInvoice(PDO $pdo, int $factureId): void {
    $stmt = $pdo->prepare("SELECT montant_total, COALESCE((SELECT SUM(montant) FROM paiements WHERE facture_id = ? AND statut = 'valide'), 0) AS paye FROM factures WHERE id = ?");
    $stmt->execute([$factureId, $factureId]);
    $row = $stmt->fetch();
    if (!$row) return;
    $total = (float)$row['montant_total'];
    $paye = (float)$row['paye'];
    $statut = $paye <= 0 ? 'emise' : ($paye < $total ? 'partiellement_payee' : 'payee');
    $datePaiement = $statut === 'payee' ? date('Y-m-d') : null;
    $upd = $pdo->prepare('UPDATE factures SET montant_paye = ?, statut = ?, date_paiement = ? WHERE id = ?');
    $upd->execute([$paye, $statut, $datePaiement, $factureId]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['montant'])) {
    $stmtFacture = $pdo->prepare('SELECT client_id FROM factures WHERE id = ?');
    $stmtFacture->execute([$factureId]);
    $clientId = (int)$stmtFacture->fetchColumn();
    if ($clientId) {
        $stmt = $pdo->prepare("INSERT INTO paiements (facture_id, client_id, montant, mode_paiement, reference, statut, date_paiement, commentaire) VALUES (?, ?, ?, ?, ?, 'valide', ?, ?)");
        $stmt->execute([
            $factureId,
            $clientId,
            (float)$_POST['montant'],
            $_POST['mode_paiement'] ?? 'virement',
            trim($_POST['reference'] ?? ''),
            $_POST['date_paiement'] ?? date('Y-m-d'),
            trim($_POST['commentaire'] ?? ''),
        ]);
        refreshInvoice($pdo, $factureId);
    }
    header('Location: facture_detail.php?id=' . $factureId);
    exit();
}

$stmt = $pdo->prepare("SELECT f.*, p.nom AS projet_nom, u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email
    FROM factures f
    JOIN projets p ON f.projet_id = p.id
    JOIN utilisateurs u ON f.client_id = u.id
    WHERE f.id = ?");
$stmt->execute([$factureId]);
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

$payments = $pdo->prepare('SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement DESC, id DESC');
$payments->execute([$factureId]);
$payments = $payments->fetchAll();

$reste = (float)$facture['montant_total'] - (float)$facture['montant_paye'];

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<?php renderAppLayoutStart('factures', 'bi-receipt', 'Facture'); ?>
            <div class="page-header">
                <h1 class="page-title"><i class="bi bi-receipt"></i> Facture <?= htmlspecialchars($facture['numero']) ?></h1>
                <div class="page-actions"><a href="factures.php" class="btn-modern btn-outline-modern">Retour</a></div>
            </div>
            <div class="section-card">
                <div class="row g-3">
                    <div class="col-md-4"><strong>Projet</strong><div><?= htmlspecialchars($facture['projet_nom']) ?></div></div>
                    <div class="col-md-4"><strong>Client</strong><div><?= htmlspecialchars($facture['client_prenom'] . ' ' . $facture['client_nom']) ?></div></div>
                    <div class="col-md-4"><strong>Statut</strong><div><span class="status-badge status-en-attente"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $facture['statut']))) ?></span></div></div>
                    <div class="col-md-4"><strong>Total</strong><div><?= number_format((float)$facture['montant_total'], 0, ',', ' ') ?></div></div>
                    <div class="col-md-4"><strong>Paye</strong><div><?= number_format((float)$facture['montant_paye'], 0, ',', ' ') ?></div></div>
                    <div class="col-md-4"><strong>Reste</strong><div><?= number_format($reste, 0, ',', ' ') ?></div></div>
                </div>
            </div>
            <div class="section-card">
                <div class="section-title mb-3">Lignes facture</div>
                <table class="modern-table">
                    <thead><tr><th>Designation</th><th>Description</th><th>Quantite</th><th>Prix</th><th>Montant</th></tr></thead>
                    <tbody>
                    <?php foreach ($lines as $line): ?>
                        <tr><td><?= htmlspecialchars($line['designation']) ?></td><td><?= htmlspecialchars($line['description'] ?? '') ?></td><td><?= number_format((float)$line['quantite'], 2, ',', ' ') ?></td><td><?= number_format((float)$line['prix_unitaire'], 0, ',', ' ') ?></td><td><?= number_format((float)$line['montant_ligne'], 0, ',', ' ') ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="section-card">
                <div class="section-title mb-3">Ajouter un paiement</div>
                <form method="post" class="row g-3">
                    <div class="col-md-3"><input type="number" step="0.01" min="1" name="montant" class="form-control-modern" placeholder="Montant" required></div>
                    <div class="col-md-3">
                        <select name="mode_paiement" class="filter-select">
                            <option value="virement">Virement</option><option value="especes">Especes</option><option value="cheque">Cheque</option><option value="mobile_money">Mobile money</option><option value="carte">Carte</option><option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="col-md-3"><input name="reference" class="form-control-modern" placeholder="Reference"></div>
                    <div class="col-md-3"><input type="date" name="date_paiement" class="form-control-modern" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-12"><textarea name="commentaire" class="form-control-modern" rows="2" placeholder="Commentaire"></textarea></div>
                    <div class="col-12"><button class="btn-modern btn-success-modern">Enregistrer paiement</button></div>
                </form>
            </div>
            <div class="section-card">
                <div class="section-title mb-3">Paiements</div>
                <table class="modern-table">
                    <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Reference</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php if (!$payments): ?><tr><td colspan="5" class="text-center">Aucun paiement.</td></tr><?php endif; ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr><td><?= htmlspecialchars($payment['date_paiement']) ?></td><td><?= number_format((float)$payment['montant'], 0, ',', ' ') ?></td><td><?= htmlspecialchars($payment['mode_paiement']) ?></td><td><?= htmlspecialchars($payment['reference'] ?? '') ?></td><td><?= htmlspecialchars($payment['statut']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
