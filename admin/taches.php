<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
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
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i> <span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i> <span>Projets</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link active"><i class="bi bi-list-task"></i> <span>Tâches</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-people"></i> <span>Utilisateurs</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> <span>Rapports</span></a></li>
                <li class="nav-item"><a href="statistiques.php" class="nav-link"><i class="bi bi-bar-chart"></i> <span>Statistiques</span></a></li>
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
                <div class="navbar-breadcrumb"><i class="bi bi-list-task"></i> <span>Tâches</span></div>
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
                <h1 class="page-title"><i class="bi bi-list-task"></i> Gestion des Tâches</h1>
                <div class="page-actions">
                    <a href="tache_create.php" class="btn-modern btn-success-modern"><i class="bi bi-plus-circle"></i> Nouvelle Tâche</a>
                </div>
            </div>
            <div class="filters-section mb-3">
                <form class="filters-row" method="get">
                    <div class="filter-group">
                        <label class="filter-label">Statut</label>
                        <select class="filter-select" name="statut" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="en-cours">En cours</option>
                            <option value="termine">Terminé</option>
                            <option value="suspendu">Suspendu</option>
                            <option value="en-attente">En attente</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr><th>Titre</th><th>Projet</th><th>Responsable</th><th>Statut</th><th>Échéance</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <!-- Exemple de ligne -->
                            <tr>
                                <td>Préparation plans</td>
                                <td>Projet A</td>
                                <td>J. Dupont</td>
                                <td><span class="status-badge status-en-cours">En cours</span></td>
                                <td>25/05/2026</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="../ingenieur/tache_detail.php?id=1" class="btn-modern btn-outline-modern" title="Voir"><i class="bi bi-eye"></i></a>
                                        <a href="tache_create.php?id=1" class="btn-modern btn-outline-modern" title="Modifier"><i class="bi bi-pencil"></i></a>
                                        <button type="button" class="btn-modern btn-danger-modern" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteTaskModal"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <!-- ...autres tâches... -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<div class="modal fade modal-modern" id="deleteTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Voulez-vous vraiment supprimer cette tache ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <a href="taches.php?delete=1" class="btn-modern btn-danger-modern" data-no-confirm="true">Supprimer</a>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
<?php require_once '../includes/footer.php'; ?>
