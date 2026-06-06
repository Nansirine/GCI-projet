
<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
require_once '../config/database.php';
require_once '../includes/functions.php';

$user_id = (int)$_SESSION['user_id'];

// Message de bienvenue personnalisé
$welcome = getWelcomeMessage($_SESSION['prenom'] ?? 'Dessinateur', $_SESSION['nom'] ?? '', 'dessinateur');

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(statut = 'valide') AS valides,
        SUM(statut = 'soumis') AS attente,
        SUM(partage_client = 1) AS partages
    FROM plans
    WHERE dessinateur_id = ?
");
$stmt->execute([$user_id]);
$planStats = $stmt->fetch() ?: [];
$stats = [
    'plans_deposes' => (int)($planStats['total'] ?? 0),
    'plans_valides' => (int)($planStats['valides'] ?? 0),
    'plans_attente' => (int)($planStats['attente'] ?? 0),
    'plans_client' => (int)($planStats['partages'] ?? 0),
];

$stmt = $pdo->prepare("
    SELECT pl.id, pl.titre, pl.version, pl.statut, pl.fichier, pl.date_upload, p.nom AS projet_nom
    FROM plans pl
    JOIN projets p ON p.id = pl.projet_id
    WHERE pl.dessinateur_id = ?
    ORDER BY pl.date_upload DESC
    LIMIT 8
");
$stmt->execute([$user_id]);
$plansRecents = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT DISTINCT p.id, p.nom, p.statut, p.pourcentage_avancement
    FROM projets p
    JOIN affectations a ON a.projet_id = p.id
    WHERE a.utilisateur_id = ?
    ORDER BY p.date_creation DESC
    LIMIT 6
");
$stmt->execute([$user_id]);
$projetsAssignes = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0');
$stmt->execute([$user_id]);
$notifCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT titre, message, type, date_creation, lien FROM notifications WHERE utilisateur_id = ? ORDER BY date_creation DESC LIMIT 5');
$stmt->execute([$user_id]);
$notificationsRecentes = $stmt->fetchAll();

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
                <a href="notifications.php" class="navbar-icon" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="navbar-icon-badge" style="<?= $notifCount > 0 ? '' : 'display:none;' ?>"><?= $notifCount ?></span>
                </a>
                <img src="<?= $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png' ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <!-- Content Area -->
        <div class="content-area">
            <!-- Message de bienvenue -->
            <div class="welcome-banner">
                <div class="welcome-banner-content">
                    <div class="welcome-salutation">
                        <span class="welcome-emoji"><?= $welcome['emoji'] ?></span>
                        <span><?= $welcome['salutation'] ?></span>
                    </div>
                    <div class="welcome-name"><?= htmlspecialchars($welcome['nom_complet']) ?></div>
                    <div class="welcome-message"><?= $welcome['message'] ?></div>
                </div>
            </div>
            
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-upload"></i></div>
                    <div class="stat-card-label">Plans déposés</div>
                    <div class="stat-card-value" id="stat-plans-deposes"><?= $stats['plans_deposes'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-card-label">Plans validés</div>
                    <div class="stat-card-value" id="stat-plans-valides"><?= $stats['plans_valides'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon warning"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-card-label">Plans en attente</div>
                    <div class="stat-card-value" id="stat-plans-attente"><?= $stats['plans_attente'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon info"><i class="bi bi-people"></i></div>
                    <div class="stat-card-label">Plans partagés client</div>
                    <div class="stat-card-value" id="stat-plans-client"><?= $stats['plans_client'] ?></div>
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
                                    <tbody id="plans-list">
                                        <?php if (!$plansRecents): ?>
                                            <tr><td colspan="6" class="text-center text-muted py-4">Aucun plan depose.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($plansRecents as $plan): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($plan['projet_nom']) ?></td>
                                                <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                                                <td>v<?= (int)$plan['version'] ?></td>
                                                <td><?= getBadgeStatut($plan['statut']) ?></td>
                                                <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                                                <td><a href="plan_detail.php?id=<?= (int)$plan['id'] ?>" class="btn-action btn-action-view" title="Voir"><i class="bi bi-eye"></i></a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-folder2"></i> Mes Projets Assignés</h2></div>
                        <ul class="list-group" id="projets-assignes">
                            <?php if (!$projetsAssignes): ?>
                                <li class="list-group-item text-muted">Aucun projet assigne.</li>
                            <?php endif; ?>
                            <?php foreach ($projetsAssignes as $projet): ?>
                                <li class="list-group-item">
                                    <div class="fw-semibold"><?= htmlspecialchars($projet['nom']) ?></div>
                                    <small><?= getBadgeStatut($projet['statut']) ?> - <?= (int)$projet['pourcentage_avancement'] ?>%</small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="section-card mb-4">
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-bell"></i> Dernières Notifications</h2></div>
                        <ul class="alert-list" id="last-notifs">
                            <?php if (!$notificationsRecentes): ?>
                                <li class="text-muted">Aucune notification recente.</li>
                            <?php endif; ?>
                            <?php foreach ($notificationsRecentes as $notification): ?>
                                <li>
                                    <strong><?= htmlspecialchars($notification['titre']) ?></strong>
                                    <small><?= htmlspecialchars($notification['message']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
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
<?php require_once '../includes/footer.php'; ?>
