
<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom'] ?? '');
    $clientId = (int)($_POST['client_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $localisation = sanitize($_POST['localisation'] ?? '');
    $budget = (float)($_POST['budget'] ?? 0);
    $dateDebut = $_POST['date_debut'] ?? '';
    $dateFinPrevue = $_POST['date_fin_prevue'] ?? '';
    $ingenieursSelectionnes = array_map('intval', $_POST['ingenieurs'] ?? []);
    $dessinateursSelectionnes = array_map('intval', $_POST['dessinateurs'] ?? []);
    $jalons = $_POST['jalons'] ?? [];

    if ($nom === '' || !$clientId || $description === '' || $localisation === '' || $budget <= 0 || !$dateDebut || !$dateFinPrevue) {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $messageType = 'danger';
    } elseif ($dateFinPrevue < $dateDebut) {
        $message = 'La date de fin prevue doit etre posterieure a la date de debut.';
        $messageType = 'danger';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO projets (nom, description, localisation, budget, date_debut, date_fin_prevue, admin_id, client_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nom, $description, $localisation, $budget, $dateDebut, $dateFinPrevue, $_SESSION['user_id'], $clientId]);
            $projetId = (int)$pdo->lastInsertId();

            $affectation = $pdo->prepare('INSERT INTO affectations (projet_id, utilisateur_id, role_projet) VALUES (?, ?, ?)');
            foreach (array_unique($ingenieursSelectionnes) as $ingenieurId) {
                $affectation->execute([$projetId, $ingenieurId, 'ingenieur']);
                createNotification($pdo, $ingenieurId, 'Nouveau projet assigne', 'Vous avez ete associe au projet "' . $nom . '".', 'info', '/ingenieur/dashboard.php');
            }
            foreach (array_unique($dessinateursSelectionnes) as $dessinateurId) {
                $affectation->execute([$projetId, $dessinateurId, 'dessinateur']);
                createNotification($pdo, $dessinateurId, 'Nouveau projet assigne', 'Vous avez ete associe au projet "' . $nom . '".', 'info', '/dessinateur/dashboard.php');
            }

            createNotification($pdo, $clientId, 'Projet cree', 'Votre projet "' . $nom . '" est maintenant disponible.', 'succes', '/client/dashboard.php');

            $stmtJalon = $pdo->prepare('INSERT INTO jalons (projet_id, titre, date_prevue) VALUES (?, ?, ?)');
            foreach ($jalons as $jalon) {
                $titreJalon = sanitize($jalon['titre'] ?? '');
                $datePrevue = $jalon['date_prevue'] ?? '';
                if ($titreJalon !== '' && $datePrevue !== '') {
                    $stmtJalon->execute([$projetId, $titreJalon, $datePrevue]);
                }
            }

            $pdo->commit();
            header('Location: projets.php?created=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'Impossible de creer le projet. Verifiez les informations saisies.';
            $messageType = 'danger';
        }
    }
}

$clients = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'client' AND statut = 'actif' ORDER BY nom, prenom")->fetchAll();
$ingenieurs = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'ingenieur' AND statut = 'actif' ORDER BY nom, prenom")->fetchAll();
$dessinateurs = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role = 'dessinateur' AND statut = 'actif' ORDER BY nom, prenom")->fetchAll();
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/app.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/dashboard-common.css">
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
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i> <span>Projets</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i> <span>Tâches</span></a></li>
                <li class="nav-item"><a href="alertes.php" class="nav-link"><i class="bi bi-exclamation-triangle"></i> <span>Alertes</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-person-gear"></i> <span>Administrateur</span></a></li>
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
                <div class="navbar-breadcrumb"><i class="bi bi-folder2"></i> <span>Créer un projet</span></div>
            </div>
            <div class="navbar-right">
                <div class="navbar-search"><i class="bi bi-search"></i><input type="text" placeholder="Rechercher..."></div>
                <i class="bi bi-bell navbar-icon"><span class="navbar-icon-badge">3</span></i>
                <img src="<?= $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png' ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-container">
                <div class="page-header">
                    <h1 class="page-title"><i class="bi bi-folder2"></i> Créer un Nouveau Projet</h1>
                </div>
                <div class="section-card">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-modern">Nom du projet *</label>
                        <input type="text" class="form-control-modern" name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Client *</label>
                        <select class="filter-select" name="client_id" required>
                            <option value="">Selectionner un client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= (int)$client['id'] ?>" <?= (string)($old['client_id'] ?? '') === (string)$client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom'] . ' - ' . $client['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-modern">Description *</label>
                        <textarea class="form-control-modern" name="description" rows="2" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Localisation *</label>
                        <input type="text" class="form-control-modern" name="localisation" value="<?= htmlspecialchars($old['localisation'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Budget (FCFA) *</label>
                        <input type="number" class="form-control-modern" name="budget" value="<?= htmlspecialchars($old['budget'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Date début *</label>
                        <input type="date" class="form-control-modern" name="date_debut" value="<?= htmlspecialchars($old['date_debut'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Date fin prévue *</label>
                        <input type="date" class="form-control-modern" name="date_fin_prevue" value="<?= htmlspecialchars($old['date_fin_prevue'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Ingénieurs affectés</label>
                        <select class="filter-select" name="ingenieurs[]" multiple>
                            <?php foreach ($ingenieurs as $ingenieur): ?>
                                <option value="<?= (int)$ingenieur['id'] ?>" <?= in_array((string)$ingenieur['id'], array_map('strval', $old['ingenieurs'] ?? []), true) ? 'selected' : '' ?>><?= htmlspecialchars($ingenieur['prenom'] . ' ' . $ingenieur['nom'] . ' - ' . $ingenieur['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Dessinateurs affectés</label>
                        <select class="filter-select" name="dessinateurs[]" multiple>
                            <?php foreach ($dessinateurs as $dessinateur): ?>
                                <option value="<?= (int)$dessinateur['id'] ?>" <?= in_array((string)$dessinateur['id'], array_map('strval', $old['dessinateurs'] ?? []), true) ? 'selected' : '' ?>><?= htmlspecialchars($dessinateur['prenom'] . ' ' . $dessinateur['nom'] . ' - ' . $dessinateur['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-modern">Jalons</label>
                        <div id="jalons-list"></div>
                        <button type="button" class="btn-modern btn-outline-modern btn-sm mt-2" id="addJalon">+ Ajouter Jalon</button>
                    </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn-modern btn-success-modern me-2">✅ Créer le Projet</button>
                            <a href="projets.php" class="btn-modern btn-outline-modern">❌ Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
// JS pour ajouter dynamiquement des jalons
let jalonIdx = 0;
document.getElementById('addJalon').onclick = function() {
    const list = document.getElementById('jalons-list');
    const div = document.createElement('div');
    div.className = 'row g-2 align-items-end mb-2';
    div.innerHTML = `<div class="col-md-6"><input type="text" name="jalons[${jalonIdx}][titre]" class="form-control-modern" placeholder="Titre du jalon" required></div><div class="col-md-4"><input type="date" name="jalons[${jalonIdx}][date_prevue]" class="form-control-modern" required></div><div class="col-md-2"><button type="button" class="btn-modern btn-danger-modern btn-sm" onclick="this.parentNode.parentNode.remove()">Supprimer</button></div>`;
    list.appendChild(div);
    jalonIdx++;
};
</script>
<?php require_once '../includes/footer.php'; ?>
