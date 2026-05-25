
<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
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
                <img src="/gestion_projet/image/WhatsApp%20Image%202026-05-11%20at%2017.24.59.jpeg" alt="Logo Buildflow" width="36" height="36" class="sidebar-logo rounded-circle" style="object-fit:cover;">
                <span class="sidebar-title">Buildflow</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="bi bi-house-door"></i> <span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i> <span>Mes Tâches</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> <span>Mes Rapports</span></a></li>
                <li class="nav-item"><a href="alertes.php" class="nav-link"><i class="bi bi-exclamation-triangle"></i> <span>Alertes</span></a></li>
                <li class="nav-item"><a href="documents.php" class="nav-link"><i class="bi bi-folder2"></i> <span>Documents</span></a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="bi bi-chat"></i> <span>Messages</span></a></li>
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
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-list-task"></i></div>
                    <div class="stat-card-label">Tâches en cours</div>
                    <div class="stat-card-value" id="stat-taches-cours">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-card-label">Tâches terminées</div>
                    <div class="stat-card-value" id="stat-taches-terminees">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon info"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-card-label">Rapports soumis</div>
                    <div class="stat-card-value" id="stat-rapports">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-card-label">Alertes ouvertes</div>
                    <div class="stat-card-value" id="stat-alertes">0</div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title"><i class="bi bi-list-task"></i> Mes Tâches Assignées</h2>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <table class="modern-table">
                                    <thead><tr><th>Projet</th><th>Tâche</th><th>Priorité</th><th>Statut</th><th>Avancement</th><th>Échéance</th></tr></thead>
                                    <tbody id="taches-list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-folder2"></i> Plans Récents</h2></div>
                        <ul class="file-list" id="plans-recents"></ul>
                    </div>
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-bell"></i> Dernières Notifications</h2></div>
                        <ul class="alert-list" id="last-notifs"></ul>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="rapport_create.php" class="btn-modern btn-success-modern flex-fill"><i class="bi bi-plus-circle"></i> Soumettre Rapport</a>
                        <a href="alertes.php" class="btn-modern btn-danger-modern flex-fill"><i class="bi bi-exclamation-triangle"></i> Signaler Problème</a>
                    </div>
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
