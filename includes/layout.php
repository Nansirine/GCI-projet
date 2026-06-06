<?php
require_once __DIR__ . '/functions.php';

function getNotificationCountForCurrentUser(PDO $pdo): int
{
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$userId) {
        return 0;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function getRoleLayoutConfig(string $role): array
{
    $configs = [
        'admin' => [
            'title' => 'Buildflow',
            'logo' => 'bi-kanban',
            'notification' => 'notifications.php',
            'items' => [
                'dashboard' => ['dashboard.php', 'bi-house-door', 'Tableau de bord'],
                'projets' => ['projets.php', 'bi-folder2', 'Projets'],
                'factures' => ['factures.php', 'bi-receipt', 'Factures'],
                'paiements' => ['paiements.php', 'bi-credit-card', 'Paiements'],
                'taches' => ['taches.php', 'bi-list-task', 'Taches'],
                'alertes' => ['alertes.php', 'bi-exclamation-triangle', 'Alertes'],
                'utilisateurs' => ['utilisateurs.php', 'bi-person-gear', 'Administrateur'],
                'rapports' => ['rapports.php', 'bi-file-earmark-text', 'Rapports'],
                'messages' => ['messages.php', 'bi-chat', 'Messages'],
                'statistiques' => ['statistiques.php', 'bi-bar-chart', 'Statistiques'],
                'notifications' => ['notifications.php', 'bi-bell', 'Notifications'],
            ],
        ],
        'ingenieur' => [
            'title' => 'Buildflow',
            'logo' => 'bi-gear',
            'notification' => 'notifications.php',
            'items' => [
                'dashboard' => ['dashboard.php', 'bi-house-door', 'Tableau de bord'],
                'taches' => ['taches.php', 'bi-list-task', 'Mes taches'],
                'rapports' => ['rapports.php', 'bi-file-earmark-text', 'Rapports'],
                'alertes' => ['alertes.php', 'bi-exclamation-triangle', 'Alertes'],
                'documents' => ['documents.php', 'bi-folder2', 'Documents'],
                'messages' => ['messages.php', 'bi-chat', 'Messages'],
                'notifications' => ['notifications.php', 'bi-bell', 'Notifications'],
            ],
        ],
        'dessinateur' => [
            'title' => 'Buildflow',
            'logo' => 'bi-pencil',
            'notification' => 'notifications.php',
            'items' => [
                'dashboard' => ['dashboard.php', 'bi-house-door', 'Tableau de bord'],
                'plans' => ['plans.php', 'bi-file-earmark', 'Plans'],
                'taches' => ['taches.php', 'bi-list-task', 'Taches'],
                'messages' => ['messages.php', 'bi-chat', 'Messages'],
                'notifications' => ['notifications.php', 'bi-bell', 'Notifications'],
            ],
        ],
        'client' => [
            'title' => 'Buildflow',
            'logo' => 'bi-person',
            'notification' => 'notifications.php',
            'items' => [
                'dashboard' => ['dashboard.php', 'bi-house-door', 'Tableau de bord'],
                'avancement' => ['avancement.php', 'bi-graph-up', 'Avancement'],
                'plans' => ['plans.php', 'bi-file-earmark', 'Plans'],
                'rapports' => ['rapports.php', 'bi-file-earmark-text', 'Rapports'],
                'factures' => ['factures.php', 'bi-receipt', 'Factures'],
                'planning' => ['planning.php', 'bi-calendar3', 'Planning'],
                'demandes' => ['demandes.php', 'bi-chat', 'Demandes'],
                'notifications' => ['notifications.php', 'bi-bell', 'Notifications'],
            ],
        ],
    ];

    return $configs[$role] ?? $configs['client'];
}

function renderAppLayoutStart(string $active, string $icon, string $title): void
{
    global $pdo;
    $role = $_SESSION['role'] ?? '';
    $config = getRoleLayoutConfig($role);
    $notifCount = isset($pdo) ? getNotificationCountForCurrentUser($pdo) : 0;
    $photo = htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png');
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
                <?php foreach ($config['items'] as $key => [$href, $itemIcon, $label]): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($href) ?>" class="nav-link <?= $active === $key ? 'active' : '' ?>">
                            <i class="bi <?= htmlspecialchars($itemIcon) ?>"></i><span><?= htmlspecialchars($label) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
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
                <a href="<?= htmlspecialchars($config['notification']) ?>" class="navbar-icon" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="navbar-icon-badge" style="<?= $notifCount > 0 ? '' : 'display:none;' ?>"><?= $notifCount ?></span>
                </a>
                <img src="<?= $photo ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <div class="content-area">
<?php
}

function renderAppLayoutEnd(): void
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
