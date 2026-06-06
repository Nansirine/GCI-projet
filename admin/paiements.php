<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';

$stmt = $pdo->query("SELECT pa.*, f.numero, p.nom AS projet_nom, u.nom AS client_nom, u.prenom AS client_prenom
    FROM paiements pa
    JOIN factures f ON pa.facture_id = f.id
    JOIN projets p ON f.projet_id = p.id
    JOIN utilisateurs u ON pa.client_id = u.id
    ORDER BY pa.date_paiement DESC, pa.id DESC");
$paiements = $stmt->fetchAll();

$stats = $pdo->query("SELECT
    COALESCE(SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END), 0) AS total_valide,
    COALESCE(SUM(CASE WHEN statut = 'en_attente' THEN montant ELSE 0 END), 0) AS total_attente,
    COUNT(*) AS total_paiements
    FROM paiements")->fetch();

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <img src="/gestion_projet/image/WhatsApp%20Image%202026-05-11%20at%2017.24.59.jpeg" alt="Logo Buildflow" width="36" height="36" class="sidebar-logo rounded-circle" style="object-fit:cover;">
                <span class="sidebar-title">Buildflow</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i><span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i><span>Projets</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="paiements.php" class="nav-link active"><i class="bi bi-credit-card"></i><span>Paiements</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i><span>Taches</span></a></li>
                <li class="nav-item"><a href="alertes.php" class="nav-link"><i class="bi bi-exclamation-triangle"></i><span>Alertes</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-person-gear"></i><span>Administrateur</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Rapports</span></a></li>
                <li class="nav-item"><a href="statistiques.php" class="nav-link"><i class="bi bi-bar-chart"></i><span>Statistiques</span></a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="/gestion_projet/logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i><span>Deconnexion</span></a>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <nav class="top-navbar">
            <div class="navbar-left">
                <i class="bi bi-list menu-toggle" id="menuToggle"></i>
                <div class="navbar-breadcrumb"><i class="bi bi-credit-card"></i><span>Paiements</span></div>
            </div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action=""><i class="bi bi-search"></i><input type="text" name="search" placeholder="Rechercher..."></form>
                <a href="notifications.php" class="navbar-icon" title="Notifications"><i class="bi bi-bell"></i></a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>

        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="bi bi-credit-card"></i> Paiements</h1>
                <div class="page-actions"><a href="factures.php" class="btn-modern btn-outline-modern">Factures</a></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-label">Paiements valides</div>
                    <div class="stat-card-value"><?= number_format((float)$stats['total_valide'], 0, ',', ' ') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">En attente</div>
                    <div class="stat-card-value"><?= number_format((float)$stats['total_attente'], 0, ',', ' ') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Nombre paiements</div>
                    <div class="stat-card-value"><?= (int)$stats['total_paiements'] ?></div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead><tr><th>Date</th><th>Facture</th><th>Projet</th><th>Client</th><th>Montant</th><th>Mode</th><th>Reference</th><th>Statut</th></tr></thead>
                        <tbody>
                        <?php if (!$paiements): ?><tr><td colspan="8" class="text-center">Aucun paiement.</td></tr><?php endif; ?>
                        <?php foreach ($paiements as $paiement): ?>
                            <tr>
                                <td><?= htmlspecialchars($paiement['date_paiement']) ?></td>
                                <td><a href="facture_detail.php?id=<?= (int)$paiement['facture_id'] ?>"><?= htmlspecialchars($paiement['numero']) ?></a></td>
                                <td><?= htmlspecialchars($paiement['projet_nom']) ?></td>
                                <td><?= htmlspecialchars($paiement['client_prenom'] . ' ' . $paiement['client_nom']) ?></td>
                                <td><?= number_format((float)$paiement['montant'], 0, ',', ' ') ?></td>
                                <td><?= htmlspecialchars($paiement['mode_paiement']) ?></td>
                                <td><?= htmlspecialchars($paiement['reference'] ?? '') ?></td>
                                <td><?= htmlspecialchars($paiement['statut']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
<?php require_once '../includes/footer.php'; ?>

