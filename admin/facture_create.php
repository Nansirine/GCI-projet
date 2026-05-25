<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';

$projets = $pdo->query("SELECT p.id, p.nom, p.client_id, u.nom AS client_nom, u.prenom AS client_prenom
    FROM projets p JOIN utilisateurs u ON p.client_id = u.id ORDER BY p.nom")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $dateEmission = $_POST['date_emission'] ?? date('Y-m-d');
    $dateEcheance = $_POST['date_echeance'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    $designations = $_POST['designation'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prix = $_POST['prix_unitaire'] ?? [];

    $selectedProject = null;
    foreach ($projets as $projet) {
        if ((int)$projet['id'] === $projetId) {
            $selectedProject = $projet;
            break;
        }
    }

    if (!$selectedProject) {
        $error = 'Veuillez selectionner un projet valide.';
    } else {
        $lines = [];
        $total = 0;
        foreach ($designations as $i => $designation) {
            $designation = trim($designation);
            $qty = max(0, (float)($quantites[$i] ?? 0));
            $unit = max(0, (float)($prix[$i] ?? 0));
            if ($designation === '' || $qty <= 0 || $unit <= 0) {
                continue;
            }
            $amount = $qty * $unit;
            $total += $amount;
            $lines[] = [$designation, trim($descriptions[$i] ?? ''), $qty, $unit, $amount, $i];
        }

        if (!$lines) {
            $error = 'Ajoutez au moins une ligne de facture valide.';
        } else {
            $pdo->beginTransaction();
            $numero = 'FAC-' . date('Ymd-His');
            $stmt = $pdo->prepare("INSERT INTO factures (numero, projet_id, client_id, admin_id, montant_total, statut, date_emission, date_echeance, notes) VALUES (?, ?, ?, ?, ?, 'emise', ?, ?, ?)");
            $stmt->execute([$numero, $projetId, $selectedProject['client_id'], $_SESSION['user_id'], $total, $dateEmission, $dateEcheance, $notes]);
            $factureId = (int)$pdo->lastInsertId();

            $lineStmt = $pdo->prepare('INSERT INTO lignes_facture (facture_id, designation, description, quantite, prix_unitaire, montant_ligne, ordre) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($lines as $line) {
                $lineStmt->execute([$factureId, ...$line]);
            }
            $pdo->commit();
            header('Location: facture_detail.php?id=' . $factureId);
            exit();
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<?php renderAppLayoutStart('factures', 'bi-receipt', 'Nouvelle facture'); ?>
            <div class="page-header">
                <h1 class="page-title"><i class="bi bi-receipt"></i> Nouvelle facture</h1>
                <div class="page-actions"><a href="factures.php" class="btn-modern btn-outline-modern">Retour</a></div>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <div class="section-card centered-form-card">
                <form method="post" class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-modern">Projet *</label>
                        <select class="filter-select" name="projet_id" required>
                            <option value="">Selectionner un projet</option>
                            <?php foreach ($projets as $projet): ?>
                                <option value="<?= (int)$projet['id'] ?>"><?= htmlspecialchars($projet['nom'] . ' - ' . $projet['client_prenom'] . ' ' . $projet['client_nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label-modern">Date emission *</label><input type="date" class="form-control-modern" name="date_emission" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="col-md-6"><label class="form-label-modern">Date echeance *</label><input type="date" class="form-control-modern" name="date_echeance" required></div>
                    <div class="col-12"><label class="form-label-modern">Notes</label><textarea class="form-control-modern" name="notes" rows="2"></textarea></div>
                    <div class="col-12">
                        <div class="section-header"><div class="section-title">Lignes de facture</div><button type="button" class="btn-modern btn-outline-modern btn-sm" id="addLine">Ajouter</button></div>
                        <div id="invoiceLines"></div>
                    </div>
                    <div class="col-12"><button class="btn-modern btn-success-modern">Creer la facture</button></div>
                </form>
            </div>
<?php renderAppLayoutEnd(); ?>
<script>
let lineIndex = 0;
function addLine() {
    const wrapper = document.createElement('div');
    wrapper.className = 'row g-2 mb-2';
    wrapper.innerHTML = `
        <div class="col-md-4"><input class="form-control-modern" name="designation[]" placeholder="Designation" required></div>
        <div class="col-md-3"><input class="form-control-modern" name="description[]" placeholder="Description"></div>
        <div class="col-md-2"><input type="number" min="0.01" step="0.01" class="form-control-modern" name="quantite[]" placeholder="Quantite" required></div>
        <div class="col-md-2"><input type="number" min="0.01" step="0.01" class="form-control-modern" name="prix_unitaire[]" placeholder="Prix unitaire" required></div>
        <div class="col-md-1"><button type="button" class="btn-modern btn-danger-modern" onclick="this.closest('.row').remove()">X</button></div>
    `;
    document.getElementById('invoiceLines').appendChild(wrapper);
    lineIndex++;
}
document.getElementById('addLine').addEventListener('click', addLine);
addLine();
</script>
<?php require_once '../includes/footer.php'; ?>
