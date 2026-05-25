
<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
$prenom = $_SESSION['prenom'] ?? '';
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/app.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/dashboard-common.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/components.css">
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <div class="sidebar-logo"><i class="bi bi-person"></i></div>
                <span class="sidebar-title">GC Client</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="bi bi-house-door"></i> <span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="avancement.php" class="nav-link"><i class="bi bi-graph-up"></i> <span>Avancement</span></a></li>
                <li class="nav-item"><a href="plans.php" class="nav-link"><i class="bi bi-file-earmark"></i> <span>Plans</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> <span>Rapports</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link"><i class="bi bi-receipt"></i> <span>Factures</span></a></li>
                <li class="nav-item"><a href="demandes.php" class="nav-link"><i class="bi bi-chat"></i> <span>Demandes</span></a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i> <span>Notifications</span></a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="/gestion_projet/logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i> <span>Déconnexion</span></a>
        </div>
    </aside>
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-left">
                <i class="bi bi-list menu-toggle" id="menuToggle"></i>
                <div class="navbar-breadcrumb"><i class="bi bi-house-door"></i> <span>Tableau de bord</span></div>
            </div>
            <div class="navbar-right">
                <div class="navbar-search"><i class="bi bi-search"></i><input type="text" placeholder="Rechercher..."></div>
                <i class="bi bi-bell navbar-icon"><span class="navbar-icon-badge">3</span></i>
                <img src="<?= $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png' ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title">Bonjour <?= htmlspecialchars($prenom) ?>, voici l'état de votre projet</h1>
            </div>
            <div class="section-card mb-4">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h4 id="projet-nom">Nom du projet</h4>
                        <span class="status-badge status-en-cours" id="projet-statut">Statut</span>
                    </div>
                    <div class="col-md-5">
                        <div class="progress" style="height: 2rem;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="projet-avancement" style="width: 0%; font-size:1.2rem;">0%</div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div>Date début : <span id="projet-date-debut"></span></div>
                        <div>Date fin prévue : <span id="projet-date-fin"></span></div>
                        <div>Jours restants : <span id="projet-jours-restants"></span></div>
                    </div>
                </div>
            </div>
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-card-label">Tâches terminées / total</div>
                    <div class="stat-card-value" id="stat-taches">0/0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon info"><i class="bi bi-file-earmark"></i></div>
                    <div class="stat-card-label">Plans disponibles</div>
                    <div class="stat-card-value" id="stat-plans">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-card-label">Rapports validés</div>
                    <div class="stat-card-value" id="stat-rapports">0</div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-flag"></i> Prochain Jalon</h2></div>
                        <div class="section-body" id="prochain-jalon"></div>
                    </div>
                    <div class="section-card">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-bell"></i> Dernières Mises à Jour</h2></div>
                        <ul class="alert-list" id="last-notifs"></ul>
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-column gap-3 align-items-end">
                    <a href="avancement.php" class="btn-modern btn-primary-modern w-100"><i class="bi bi-graph-up"></i> Voir Avancement</a>
                    <a href="rapports.php" class="btn-modern btn-outline-modern w-100"><i class="bi bi-file-earmark-text"></i> Mes Rapports</a>
                    <a href="factures.php" class="btn-modern btn-outline-modern w-100"><i class="bi bi-receipt"></i> Mes Factures</a>
                    <a href="demandes.php" class="btn-modern btn-success-modern w-100"><i class="bi bi-chat"></i> Faire une Demande</a>
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
<script src="/gestion_projet/assets/js/app.js"></script>
<?php require_once '../includes/footer.php'; ?>
