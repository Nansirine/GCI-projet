
<?php
require_once '../includes/auth.php';
checkRole(['admin']);
$projectId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
require_once '../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_action'], $_POST['plan_id'])) {
    $planId = (int)$_POST['plan_id'];
    $action = $_POST['plan_action'];

    $stmtPlan = $pdo->prepare('
        SELECT pl.*, p.client_id
        FROM plans pl
        JOIN projets p ON p.id = pl.projet_id
        WHERE pl.id = ? AND p.id = ?
    ');
    $stmtPlan->execute([$planId, $projectId]);
    $plan = $stmtPlan->fetch();

    if (!$plan) {
        $message = 'Plan introuvable pour ce projet.';
        $messageType = 'danger';
    } elseif ($action === 'valider') {
        $pdo->prepare("UPDATE plans SET statut = 'valide', commentaire = ? WHERE id = ?")->execute([sanitize($_POST['commentaire'] ?? ''), $planId]);
        createNotification($pdo, (int)$plan['dessinateur_id'], 'Plan valide', 'Votre plan "' . $plan['titre'] . '" a ete valide par le chef projet.', 'succes', '/dessinateur/plans.php');
        $message = 'Plan valide.';
        $messageType = 'success';
    } elseif ($action === 'rejeter') {
        $commentaire = sanitize($_POST['commentaire'] ?? 'Corrections demandees.');
        $pdo->prepare("UPDATE plans SET statut = 'rejete', commentaire = ?, partage_client = 0 WHERE id = ?")->execute([$commentaire, $planId]);
        createNotification($pdo, (int)$plan['dessinateur_id'], 'Plan rejete', 'Votre plan "' . $plan['titre'] . '" a ete rejete : ' . $commentaire, 'erreur', '/dessinateur/plans.php');
        $message = 'Plan rejete.';
        $messageType = 'warning';
    } elseif ($action === 'partager' && $plan['statut'] === 'valide') {
        $pdo->prepare('UPDATE plans SET partage_client = 1 WHERE id = ?')->execute([$planId]);
        createNotification($pdo, (int)$plan['client_id'], 'Nouveau plan disponible', 'Un plan valide est disponible pour votre projet : "' . $plan['titre'] . '".', 'info', '/client/plans.php');
        $message = 'Plan partage avec le client.';
        $messageType = 'success';
    }
}

$stmtPlans = $pdo->prepare('
    SELECT pl.*, u.nom AS dessinateur_nom, u.prenom AS dessinateur_prenom
    FROM plans pl
    JOIN utilisateurs u ON u.id = pl.dessinateur_id
    WHERE pl.projet_id = ?
    ORDER BY pl.date_upload DESC
');
$stmtPlans->execute([$projectId]);
$plansProjet = $stmtPlans->fetchAll();

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/components.css">
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <div class="sidebar-logo"><i class="bi bi-folder2"></i></div>
                <span class="sidebar-title">GC Manager</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i> <span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link active"><i class="bi bi-folder2"></i> <span>Projets</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i> <span>Tâches</span></a></li>
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
                <div class="navbar-breadcrumb"><i class="bi bi-folder2"></i> <span>Détail du projet</span></div>
            </div>
            <div class="navbar-right">
                <div class="navbar-search"><i class="bi bi-search"></i><input type="text" placeholder="Rechercher..."></div>
                <i class="bi bi-bell navbar-icon"><span class="navbar-icon-badge">3</span></i>
                <img src="<?= $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png' ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <!-- Content Area -->
        <div class="content-area">
            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <div class="page-header">
                <h1 class="page-title"><i class="bi bi-folder2"></i> Détail du Projet</h1>
            </div>
            <div class="section-card">
                <ul class="nav nav-tabs mb-3" id="projetTabs" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">Vue Générale</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-taches" data-bs-toggle="tab" data-bs-target="#taches" type="button" role="tab">Tâches</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-jalons" data-bs-toggle="tab" data-bs-target="#jalons" type="button" role="tab">Jalons</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-rapports" data-bs-toggle="tab" data-bs-target="#rapports" type="button" role="tab">Rapports</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-plans" data-bs-toggle="tab" data-bs-target="#plans" type="button" role="tab">Plans</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-alertes" data-bs-toggle="tab" data-bs-target="#alertes" type="button" role="tab">Alertes</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-messages" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">Messages</button></li>
                </ul>
                <div class="tab-content" id="projetTabsContent">
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <!-- Vue Générale : infos projet, membres, boutons -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card-modern mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Informations générales</h5>
                                        <!-- Infos projet ici -->
                                    </div>
                                </div>
                                <div class="card-modern">
                                    <div class="card-body">
                                        <h5 class="card-title">Membres du projet</h5>
                                        <!-- Membres ici -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card-modern h-100">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <h5 class="card-title">Actions</h5>
                                        <a href="projet_create.php?id=<?= $projectId ?>" class="btn-modern btn-primary-modern mb-2 text-center text-decoration-none">Modifier</a>
                                        <button type="button" class="btn-modern btn-danger-modern" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">Supprimer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="taches" role="tabpanel">
                        <!-- Liste des tâches du projet -->
                        <div class="table-container mt-3">
                            <div class="table-wrapper">
                                <table class="modern-table">
                                    <thead><tr><th>Tâche</th><th>Responsable</th><th>Statut</th><th>Échéance</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <!-- ... -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="jalons" role="tabpanel">
                        <!-- Timeline des jalons -->
                        <div class="timeline mt-3">
                            <!-- ... -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="rapports" role="tabpanel">
                        <!-- Liste des rapports -->
                        <div class="table-container mt-3">
                            <div class="table-wrapper">
                                <table class="modern-table">
                                    <thead><tr><th>Rapport</th><th>Date</th><th>Auteur</th><th>Statut</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <!-- ... -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="plans" role="tabpanel">
                        <div class="table-container mt-3">
                            <div class="table-wrapper">
                                <table class="modern-table">
                                    <thead><tr><th>Plan</th><th>Dessinateur</th><th>Statut</th><th>Client</th><th>Date</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php if (!$plansProjet): ?>
                                            <tr><td colspan="6" class="text-center">Aucun plan depose.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($plansProjet as $plan): ?>
                                            <tr>
                                                <td><i class="bi <?= documentIcon($plan['fichier']) ?>"></i> <?= htmlspecialchars($plan['titre']) ?></td>
                                                <td><?= htmlspecialchars($plan['dessinateur_prenom'] . ' ' . $plan['dessinateur_nom']) ?></td>
                                                <td><?= getBadgeStatut($plan['statut']) ?></td>
                                                <td><?= (int)$plan['partage_client'] === 1 ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Non visible</span>' ?></td>
                                                <td><?= htmlspecialchars(formatDate($plan['date_upload'])) ?></td>
                                                <td>
                                                    <?= renderDocumentActions('plan', (int)$plan['id'], $plan['fichier'], $plan['titre']) ?>
                                                    <form method="post" class="d-flex flex-wrap gap-1 mt-2">
                                                        <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                                                        <?php if ($plan['statut'] === 'soumis'): ?>
                                                            <button class="btn btn-sm btn-success" name="plan_action" value="valider">Valider</button>
                                                            <button class="btn btn-sm btn-outline-danger" name="plan_action" value="rejeter">Rejeter</button>
                                                        <?php endif; ?>
                                                        <?php if ($plan['statut'] === 'valide' && (int)$plan['partage_client'] === 0): ?>
                                                            <button class="btn btn-sm btn-primary" name="plan_action" value="partager">Partager client</button>
                                                        <?php endif; ?>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="alertes" role="tabpanel">
                        <!-- Liste des alertes -->
                        <ul class="alert-list mt-3">
                            <!-- ... -->
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="messages" role="tabpanel">
                        <!-- Messagerie interne -->
                        <div class="section-card mt-3">
                            <!-- ... -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<div class="modal fade modal-modern" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="projets.php">
            <input type="hidden" name="delete_id" value="<?= $projectId ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Supprimer ce projet ? Cette action est definitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-modern btn-danger-modern" <?= $projectId ? '' : 'disabled' ?>>Supprimer</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
<?php require_once '../includes/footer.php'; ?>
