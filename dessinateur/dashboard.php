
<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
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
                <li class="nav-item"><a href="plans.php" class="nav-link"><i class="bi bi-file-earmark"></i> <span>Mes Plans</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i> <span>Mes Tâches</span></a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="bi bi-chat"></i> <span>Messages</span></a></li>
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
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-upload"></i></div>
                    <div class="stat-card-label">Plans déposés</div>
                    <div class="stat-card-value" id="stat-plans-deposes">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-card-label">Plans validés</div>
                    <div class="stat-card-value" id="stat-plans-valides">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon warning"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-card-label">Plans en attente</div>
                    <div class="stat-card-value" id="stat-plans-attente">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon info"><i class="bi bi-people"></i></div>
                    <div class="stat-card-label">Plans partagés client</div>
                    <div class="stat-card-value" id="stat-plans-client">0</div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title"><i class="bi bi-file-earmark"></i> Plans Récents</h2>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <table class="modern-table">
                                    <thead><tr><th>Projet</th><th>Titre</th><th>Version</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
                                    <tbody id="plans-list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-folder2"></i> Mes Projets Assignés</h2></div>
                        <ul class="project-card" id="projets-assignes"></ul>
                    </div>
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-bell"></i> Dernières Notifications</h2></div>
                        <ul class="alert-list" id="last-notifs"></ul>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="plan_upload.php" class="btn-modern btn-success-modern flex-fill"><i class="bi bi-plus-circle"></i> Déposer un Plan</a>
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
