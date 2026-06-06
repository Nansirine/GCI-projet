
<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
require_once '../config/database.php';
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'];

// Message de bienvenue personnalisé
$welcome = getWelcomeMessage($_SESSION['prenom'] ?? 'Ingénieur', $_SESSION['nom'] ?? '', 'ingenieur');

$stats = [
    'taches_cours' => 0,
    'taches_terminees' => 0,
    'rapports' => 0,
    'alertes' => 0,
];

$stmt = $pdo->prepare("SELECT
    SUM(statut = 'en_cours') AS taches_cours,
    SUM(statut = 'termine') AS taches_terminees
    FROM taches WHERE assigne_a = ?");
$stmt->execute([$user_id]);
$taskStats = $stmt->fetch() ?: [];
$stats['taches_cours'] = (int)($taskStats['taches_cours'] ?? 0);
$stats['taches_terminees'] = (int)($taskStats['taches_terminees'] ?? 0);

$stmt = $pdo->prepare('SELECT COUNT(*) FROM rapports WHERE ingenieur_id = ?');
$stmt->execute([$user_id]);
$stats['rapports'] = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM alertes WHERE signale_par = ? AND statut <> 'resolu'");
$stmt->execute([$user_id]);
$stats['alertes'] = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT t.id, t.titre, t.priorite, t.statut, t.pourcentage, t.date_echeance, p.nom AS projet_nom
                       FROM taches t
                       JOIN projets p ON p.id = t.projet_id
                       WHERE t.assigne_a = ?
                       ORDER BY t.date_echeance ASC
                       LIMIT 8");
$stmt->execute([$user_id]);
$tachesRecentes = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT pl.id, pl.titre, pl.statut, pl.date_upload, p.nom AS projet_nom
                       FROM plans pl
                       JOIN projets p ON p.id = pl.projet_id
                       JOIN affectations a ON a.projet_id = p.id
                       WHERE a.utilisateur_id = ?
                       ORDER BY pl.date_upload DESC
                       LIMIT 5");
$stmt->execute([$user_id]);
$plansRecents = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT titre, message, type, date_creation FROM notifications WHERE utilisateur_id = ? ORDER BY date_creation DESC LIMIT 5');
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
                    <div class="stat-card-icon primary"><i class="bi bi-list-task"></i></div>
                    <div class="stat-card-label">Tâches en cours</div>
                    <div class="stat-card-value" id="stat-taches-cours"><?= $stats['taches_cours'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-card-label">Tâches terminées</div>
                    <div class="stat-card-value" id="stat-taches-terminees"><?= $stats['taches_terminees'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon info"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-card-label">Rapports soumis</div>
                    <div class="stat-card-value" id="stat-rapports"><?= $stats['rapports'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-card-label">Alertes ouvertes</div>
                    <div class="stat-card-value" id="stat-alertes"><?= $stats['alertes'] ?></div>
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
                                    <tbody id="taches-list">
                                        <?php if (!$tachesRecentes): ?>
                                            <tr><td colspan="6" class="text-center text-muted py-4">Aucune tache assignee.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($tachesRecentes as $tache): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($tache['projet_nom']) ?></td>
                                                <td><a href="tache_detail.php?id=<?= (int)$tache['id'] ?>"><?= htmlspecialchars($tache['titre']) ?></a></td>
                                                <td><?= getPriorityBadge($tache['priorite']) ?></td>
                                                <td><?= getBadgeStatut($tache['statut']) ?></td>
                                                <td><?= (int)$tache['pourcentage'] ?>%</td>
                                                <td><?= formatDate($tache['date_echeance']) ?></td>
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
                        <div class="section-header"><h2 class="section-title"><i class="bi bi-folder2"></i> Plans Récents</h2></div>
                        <ul class="file-list" id="plans-recents">
                            <?php if (!$plansRecents): ?>
                                <li class="text-muted">Aucun plan recent.</li>
                            <?php endif; ?>
                            <?php foreach ($plansRecents as $plan): ?>
                                <li>
                                    <a href="documents.php"><?= htmlspecialchars($plan['titre']) ?></a>
                                    <small><?= htmlspecialchars($plan['projet_nom']) ?> - <?= getBadgeStatut($plan['statut']) ?></small>
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
<?php require_once '../includes/footer.php'; ?>
