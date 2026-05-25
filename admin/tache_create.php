
<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $assigneA = (int)($_POST['assigne_a'] ?? 0);
    $titre = sanitize($_POST['titre'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $dateDebut = $_POST['date_debut'] ?? '';
    $dateEcheance = $_POST['date_echeance'] ?? '';
    $priorite = $_POST['priorite'] ?? 'moyenne';
    $priorites = ['basse', 'moyenne', 'haute', 'urgente'];

    if (!$projetId || !$assigneA || $titre === '' || $description === '' || !$dateDebut || !$dateEcheance || !in_array($priorite, $priorites, true)) {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $messageType = 'danger';
    } elseif ($dateEcheance < $dateDebut) {
        $message = 'La date echeance doit etre posterieure a la date de debut.';
        $messageType = 'danger';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO taches (projet_id, titre, description, assigne_a, cree_par, priorite, date_debut, date_echeance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$projetId, $titre, $description, $assigneA, $_SESSION['user_id'], $priorite, $dateDebut, $dateEcheance]);

            $check = $pdo->prepare('SELECT 1 FROM affectations WHERE projet_id = ? AND utilisateur_id = ?');
            $check->execute([$projetId, $assigneA]);
            if (!$check->fetchColumn()) {
                $affectation = $pdo->prepare('INSERT INTO affectations (projet_id, utilisateur_id, role_projet) VALUES (?, ?, ?)');
                $affectation->execute([$projetId, $assigneA, 'ingenieur']);
            }

            createNotification($pdo, $assigneA, 'Nouvelle tache assignee', 'Une nouvelle tache vous a ete assignee : "' . $titre . '".', 'info', '/ingenieur/taches.php');
            $pdo->commit();
            header('Location: taches.php?created=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'Impossible de creer la tache.';
            $messageType = 'danger';
        }
    }
}

$projets = $pdo->query("SELECT id, nom, statut FROM projets ORDER BY nom")->fetchAll();
$ingenieurs = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'ingenieur' AND statut = 'actif' ORDER BY nom, prenom")->fetchAll();
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
                <div class="navbar-breadcrumb"><i class="bi bi-list-task"></i> <span>Créer une tâche</span></div>
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
                <h1 class="page-title"><i class="bi bi-list-task"></i> Créer une Tâche</h1>
            </div>
            <div class="section-card">
                <?php if ($message): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-modern">Projet *</label>
                        <select class="filter-select" name="projet_id" required>
                            <option value="">Selectionner un projet</option>
                            <?php foreach ($projets as $projet): ?>
                                <option value="<?= (int)$projet['id'] ?>"><?= htmlspecialchars($projet['nom'] . ' - ' . ucfirst(str_replace('_', ' ', $projet['statut']))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Assigné à *</label>
                        <select class="filter-select" name="assigne_a" required>
                            <option value="">Selectionner un ingenieur</option>
                            <?php foreach ($ingenieurs as $ingenieur): ?>
                                <option value="<?= (int)$ingenieur['id'] ?>"><?= htmlspecialchars($ingenieur['prenom'] . ' ' . $ingenieur['nom'] . ' - ' . $ingenieur['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-modern">Titre *</label>
                        <input type="text" class="form-control-modern" name="titre" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-modern">Description *</label>
                        <textarea class="form-control-modern" name="description" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Date début *</label>
                        <input type="date" class="form-control-modern" name="date_debut" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Date échéance *</label>
                        <input type="date" class="form-control-modern" name="date_echeance" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Priorité *</label>
                        <select class="filter-select" name="priorite" required>
                            <option value="basse">Basse</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn-modern btn-success-modern me-2">✅ Créer Tâche</button>
                        <a href="projets.php" class="btn-modern btn-outline-modern">❌ Annuler</a>
                    </div>
                </form>
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
