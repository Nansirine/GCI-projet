<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';

if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM factures WHERE id = ?');
    $stmt->execute([(int)$_POST['delete_id']]);
    header('Location: factures.php?deleted=1');
    exit();
}

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = '(f.numero LIKE :search OR p.nom LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)';
    $params[':search'] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['statut'])) {
    $where[] = 'f.statut = :statut';
    $params[':statut'] = $_GET['statut'];
}

$sql = "SELECT f.*, p.nom AS projet_nom, u.nom AS client_nom, u.prenom AS client_prenom
        FROM factures f
        JOIN projets p ON f.projet_id = p.id
        JOIN utilisateurs u ON f.client_id = u.id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY f.date_creation DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll();

$stats = $pdo->query("SELECT
    COALESCE(SUM(montant_total), 0) AS total_facture,
    COALESCE(SUM(montant_paye), 0) AS total_paye,
    COALESCE(SUM(montant_total - montant_paye), 0) AS reste
    FROM factures")->fetch();

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">

<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><img src="/gestion_projet/image/WhatsApp%20Image%202026-05-11%20at%2017.24.59.jpeg" alt="Logo Buildflow" width="36" height="36" class="sidebar-logo rounded-circle" style="object-fit:cover;"><span class="sidebar-title">Buildflow</span></a></div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i><span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i><span>Projets</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link active"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="paiements.php" class="nav-link"><i class="bi bi-credit-card"></i><span>Paiements</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-people"></i><span>Utilisateurs</span></a></li>
            </ul>
        </nav>
        <div class="sidebar-footer"><a href="/gestion_projet/logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i><span>Deconnexion</span></a></div>
    </aside>

    <main class="main-content" id="mainContent">
        <nav class="top-navbar">
            <div class="navbar-left"><i class="bi bi-list menu-toggle" id="menuToggle"></i><div class="navbar-breadcrumb"><i class="bi bi-receipt"></i><span>Factures</span></div></div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action=""><i class="bi bi-search"></i><input type="text" name="search" placeholder="Rechercher..."></form>
                <a href="notifications.php" class="navbar-icon" title="Notifications"><i class="bi bi-bell"></i></a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="bi bi-receipt"></i> Factures</h1>
                <div class="page-actions"><a href="facture_create.php" class="btn-modern btn-success-modern"><i class="bi bi-plus-circle"></i> Nouvelle facture</a></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-card-label">Total facture</div><div class="stat-card-value"><?= number_format((float)$stats['total_facture'], 0, ',', ' ') ?></div></div>
                <div class="stat-card"><div class="stat-card-label">Total paye</div><div class="stat-card-value"><?= number_format((float)$stats['total_paye'], 0, ',', ' ') ?></div></div>
                <div class="stat-card"><div class="stat-card-label">Reste a payer</div><div class="stat-card-value"><?= number_format((float)$stats['reste'], 0, ',', ' ') ?></div></div>
            </div>

            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Facture supprimee.</div><?php endif; ?>

            <div class="filters-section">
                <form class="filters-row" method="get">
                    <div class="filter-group"><label class="filter-label">Recherche</label><input class="filter-input" name="search" placeholder="Numero, projet, client..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
                    <div class="filter-group">
                        <label class="filter-label">Statut</label>
                        <select class="filter-select" name="statut">
                            <option value="">Tous</option>
                            <?php foreach (['brouillon','emise','partiellement_payee','payee','annulee','en_retard'] as $statut): ?>
                                <option value="<?= $statut ?>" <?= ($_GET['statut'] ?? '') === $statut ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $statut)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-actions"><button class="btn-filter"><i class="bi bi-search"></i> Filtrer</button><a href="factures.php" class="btn-reset text-decoration-none">Reinitialiser</a></div>
                </form>
            </div>

            <div class="table-container">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead><tr><th>Numero</th><th>Projet</th><th>Client</th><th>Total</th><th>Paye</th><th>Statut</th><th>Echeance</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$factures): ?><tr><td colspan="8" class="text-center">Aucune facture.</td></tr><?php endif; ?>
                        <?php foreach ($factures as $facture): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($facture['numero']) ?></strong></td>
                                <td><?= htmlspecialchars($facture['projet_nom']) ?></td>
                                <td><?= htmlspecialchars($facture['client_prenom'] . ' ' . $facture['client_nom']) ?></td>
                                <td><?= number_format((float)$facture['montant_total'], 0, ',', ' ') ?></td>
                                <td><?= number_format((float)$facture['montant_paye'], 0, ',', ' ') ?></td>
                                <td><span class="status-badge status-en-attente"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $facture['statut']))) ?></span></td>
                                <td><?= htmlspecialchars($facture['date_echeance']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="facture_detail.php?id=<?= (int)$facture['id'] ?>" class="btn-action btn-action-view" title="Voir"><i class="bi bi-eye"></i></a>
                                        <button type="button" class="btn-action btn-action-delete btn-delete-invoice" data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal" data-id="<?= (int)$facture['id'] ?>" data-name="<?= htmlspecialchars($facture['numero'], ENT_QUOTES) ?>"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade modal-modern" id="deleteInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post">
            <input type="hidden" name="delete_id" id="delete_invoice_id">
            <div class="modal-header"><h5 class="modal-title">Confirmer la suppression</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">Supprimer la facture <strong id="delete_invoice_name"></strong> ?</div>
            <div class="modal-footer"><button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button><button class="btn-modern btn-danger-modern">Supprimer</button></div>
        </form>
    </div>
</div>
<script>
document.getElementById('menuToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('open'));
document.querySelectorAll('.btn-delete-invoice').forEach(btn => btn.addEventListener('click', function() {
    document.getElementById('delete_invoice_id').value = this.dataset.id;
    document.getElementById('delete_invoice_name').textContent = this.dataset.name;
}));
</script>
<?php require_once '../includes/footer.php'; ?>

