<?php
function renderClientLayoutStart(string $active, string $icon, string $title): void
{
    global $pdo;
    $notifCount = 0;
    if (isset($pdo) && !empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0');
        $stmt->execute([$_SESSION['user_id']]);
        $notifCount = (int)$stmt->fetchColumn();
    }
    $search = htmlspecialchars($_GET['search'] ?? '');
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
                <li class="nav-item"><a href="dashboard.php" class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-house-door"></i><span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="avancement.php" class="nav-link <?= $active === 'avancement' ? 'active' : '' ?>"><i class="bi bi-graph-up"></i><span>Avancement</span></a></li>
                <li class="nav-item"><a href="plans.php" class="nav-link <?= $active === 'plans' ? 'active' : '' ?>"><i class="bi bi-file-earmark"></i><span>Plans</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link <?= $active === 'rapports' ? 'active' : '' ?>"><i class="bi bi-file-earmark-text"></i><span>Rapports</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link <?= $active === 'factures' ? 'active' : '' ?>"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="demandes.php" class="nav-link <?= $active === 'demandes' ? 'active' : '' ?>"><i class="bi bi-chat"></i><span>Demandes</span></a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link <?= $active === 'notifications' ? 'active' : '' ?>"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                <div class="navbar-breadcrumb"><i class="bi <?= htmlspecialchars($icon) ?>"></i><span><?= htmlspecialchars($title) ?></span></div>
            </div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action="">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?= $search ?>" placeholder="Rechercher...">
                </form>
                <a href="notifications.php" class="navbar-icon" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="navbar-icon-badge" style="<?= $notifCount > 0 ? '' : 'display:none;' ?>"><?= $notifCount ?></span>
                </a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <div class="content-area">
<?php
}

function renderClientLayoutEnd(): void
{
?>
        </div>
    </main>
</div>
<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
<?php
}
