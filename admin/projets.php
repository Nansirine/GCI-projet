<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';

$statuts = ['en_attente', 'en_cours', 'suspendu', 'termine', 'annule'];
$message = '';

if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM projets WHERE id = ?');
    $stmt->execute([(int)$_POST['delete_id']]);
    header('Location: projets.php?deleted=1');
    exit();
}

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = 'p.nom LIKE :search';
    $params[':search'] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['statut']) && in_array($_GET['statut'], $statuts, true)) {
    $where[] = 'p.statut = :statut';
    $params[':statut'] = $_GET['statut'];
}
if (!empty($_GET['client'])) {
    $where[] = 'p.client_id = :client';
    $params[':client'] = (int)$_GET['client'];
}
if (!empty($_GET['date_debut'])) {
    $where[] = 'p.date_debut >= :date_debut';
    $params[':date_debut'] = $_GET['date_debut'];
}
if (!empty($_GET['date_fin'])) {
    $where[] = 'p.date_fin_prevue <= :date_fin';
    $params[':date_fin'] = $_GET['date_fin'];
}

$sql = 'SELECT p.*, u.nom AS client_nom, u.prenom AS client_prenom
        FROM projets p
        LEFT JOIN utilisateurs u ON p.client_id = u.id
        AND u.role = "client"';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY p.date_debut DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projets = $stmt->fetchAll();

$clients = $pdo->query('SELECT id, nom, prenom FROM utilisateurs WHERE role = "client" ORDER BY nom, prenom')->fetchAll();

$projectStats = $pdo->query("SELECT
    COUNT(*) AS total,
    COALESCE(SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END), 0) AS en_cours,
    COALESCE(SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END), 0) AS termines,
    COALESCE(AVG(pourcentage_avancement), 0) AS progression,
    COALESCE(SUM(budget), 0) AS budget_total
    FROM projets")->fetch();

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<style>
.projects-shell,
.content-area > .projects-page-header,
.projects-kpis,
.content-area > .projects-table {
    width: min(100%, 1280px);
    margin-left: auto;
    margin-right: auto;
}

.content-area > .projects-filters {
    width: min(100%, 980px);
    margin-left: auto;
    margin-right: auto;
}

.projects-page-header {
    margin-bottom: 1.5rem;
}

.projects-page-subtitle {
    margin: 0.5rem 0 0;
    color: #64748b;
    font-size: 0.95rem;
}

.projects-kpis {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.projects-filters {
    padding: 1.5rem;
}

.projects-filters .project-filters {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem 1.25rem;
}

.projects-search-group {
    grid-column: 1 / -1;
}

.project-search-control {
    position: relative;
}

.project-search-control i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1rem;
}

.project-search-control .filter-input {
    min-height: 52px;
    padding-left: 2.75rem;
    font-size: 0.95rem;
}

.projects-filters .filter-actions {
    grid-column: 1 / -1;
    justify-content: center;
    padding-top: 0.35rem;
}

.projects-filters .btn-filter,
.projects-filters .btn-reset {
    min-width: 160px;
}

.project-title-cell {
    min-width: 240px;
}

.project-name-line {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.project-initial {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: #dbeafe;
    color: #1e40af;
    font-weight: 800;
}

.project-meta-line {
    margin-top: 0.35rem;
    color: #64748b;
    font-size: 0.82rem;
}

.project-budget {
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
}

.project-date-stack {
    color: #475569;
    font-size: 0.85rem;
    line-height: 1.5;
    white-space: nowrap;
}

@media (max-width: 1200px) {
    .projects-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .projects-filters .project-filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .projects-kpis,
    .projects-filters .project-filters {
        grid-template-columns: 1fr;
    }
}
</style>

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
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i><span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link active"><i class="bi bi-folder2"></i><span>Projets</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="paiements.php" class="nav-link"><i class="bi bi-credit-card"></i><span>Paiements</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i><span>Taches</span></a></li>
                <li class="nav-item"><a href="alertes.php" class="nav-link"><i class="bi bi-exclamation-triangle"></i><span>Alertes</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-person-gear"></i><span>Administrateur</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Rapports</span></a></li>
                <li class="nav-item"><a href="statistiques.php" class="nav-link"><i class="bi bi-bar-chart"></i><span>Statistiques</span></a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                <div class="navbar-breadcrumb"><i class="bi bi-folder2"></i><span>Projets</span></div>
            </div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action=""><i class="bi bi-search"></i><input type="text" name="search" placeholder="Rechercher..."></form>
                <a href="notifications.php" class="navbar-icon" title="Notifications"><i class="bi bi-bell"></i></a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>

        <div class="content-area">
            <div class="page-header projects-page-header">
                <div>
                    <h1 class="page-title"><i class="bi bi-folder2-open"></i> Gestion des projets</h1>
                    <p class="projects-page-subtitle">Suivi des chantiers, budgets, clients et niveaux d'avancement.</p>
                </div>
                <div class="page-actions">
                    <a href="projet_create.php" class="btn-modern btn-success-modern"><i class="bi bi-plus-circle"></i> Nouveau projet</a>
                </div>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Projet supprime avec succes.</div>
            <?php endif; ?>

            <div class="stats-grid projects-kpis">
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-folder2"></i></div>
                    <div class="stat-card-label">Total projets</div>
                    <div class="stat-card-value"><?= (int)$projectStats['total'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon warning"><i class="bi bi-cone-striped"></i></div>
                    <div class="stat-card-label">En cours</div>
                    <div class="stat-card-value"><?= (int)$projectStats['en_cours'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-check2-circle"></i></div>
                    <div class="stat-card-label">Progression moyenne</div>
                    <div class="stat-card-value"><?= round((float)$projectStats['progression']) ?>%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-cash-stack"></i></div>
                    <div class="stat-card-label">Budget total</div>
                    <div class="stat-card-value"><?= number_format((float)$projectStats['budget_total'], 0, ',', ' ') ?></div>
                </div>
            </div>

            <div class="filters-section projects-filters">
                <form class="filters-row project-filters" method="get">
                    <div class="filter-group projects-search-group">
                        <label class="filter-label" for="search">Rechercher</label>
                        <div class="project-search-control">
                            <i class="bi bi-search"></i>
                            <input type="text" class="filter-input" id="search" name="search" placeholder="Rechercher un projet par nom..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="statut">Statut</label>
                        <select class="filter-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts as $statut): ?>
                                <option value="<?= $statut ?>" <?= ($_GET['statut'] ?? '') === $statut ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $statut)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="client">Client</label>
                        <select class="filter-select" id="client" name="client">
                            <option value="">Tous les clients</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= (int)$client['id'] ?>" <?= (string)($_GET['client'] ?? '') === (string)$client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="date_debut">Date debut</label>
                        <input type="date" class="filter-input" id="date_debut" name="date_debut" value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="date_fin">Date fin</label>
                        <input type="date" class="filter-input" id="date_fin" name="date_fin" value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filtrer</button>
                        <a href="projets.php" class="btn-reset text-center text-decoration-none">Reinitialiser</a>
                    </div>
                </form>
            </div>

            <div class="table-container projects-table">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Client</th>
                                <th>Statut</th>
                                <th>Avancement</th>
                                <th>Budget</th>
                                <th>Planning</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$projets): ?>
                                <tr><td colspan="7" class="text-center">Aucun projet trouve.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($projets as $projet): ?>
                                <?php
                                $isLate = !in_array($projet['statut'], ['termine', 'annule'], true)
                                    && !empty($projet['date_fin_prevue'])
                                    && strtotime($projet['date_fin_prevue']) < strtotime(date('Y-m-d'));
                                $statusClass = $isLate ? 'annule' : str_replace('_', '-', $projet['statut']);
                                $statusLabel = $isLate ? 'En retard' : ucfirst(str_replace('_', ' ', $projet['statut']));
                                $progress = (int)($projet['pourcentage_avancement'] ?? 0);
                                ?>
                                <tr>
                                    <td class="project-title-cell">
                                        <div class="project-name-line">
                                            <span class="project-initial"><?= htmlspecialchars(strtoupper(substr($projet['nom'], 0, 1))) ?></span>
                                            <strong><?= htmlspecialchars($projet['nom']) ?></strong>
                                        </div>
                                        <div class="project-meta-line"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($projet['localisation'] ?: 'Localisation non renseignee') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars(trim(($projet['client_prenom'] ?? '') . ' ' . ($projet['client_nom'] ?? '')) ?: '-') ?></td>
                                    <td><span class="status-badge status-<?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                    <td>
                                        <div class="progress-cell">
                                            <div class="progress-bar-container">
                                                <div class="progress-bar-fill" style="width: <?= $progress ?>%;"></div>
                                            </div>
                                            <span class="progress-text"><?= $progress ?>%</span>
                                        </div>
                                    </td>
                                    <td><span class="project-budget"><?= number_format((float)($projet['budget'] ?? 0), 0, ',', ' ') ?></span></td>
                                    <td>
                                        <div class="project-date-stack">
                                            <div><i class="bi bi-play-circle"></i> <?= date('d/m/Y', strtotime($projet['date_debut'])) ?></div>
                                            <div><i class="bi bi-flag"></i> <?= date('d/m/Y', strtotime($projet['date_fin_prevue'])) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="projet_detail.php?id=<?= (int)$projet['id'] ?>" class="btn-action btn-action-view" title="Voir"><i class="bi bi-eye"></i></a>
                                            <a href="projet_create.php?id=<?= (int)$projet['id'] ?>" class="btn-action btn-action-edit" title="Modifier"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn-action btn-action-delete btn-delete-project" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteProjectModal" data-id="<?= (int)$projet['id'] ?>" data-name="<?= htmlspecialchars($projet['nom'], ENT_QUOTES) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade modal-modern" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post">
            <input type="hidden" name="delete_id" id="delete_project_id">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Supprimer le projet <strong id="delete_project_name"></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-modern btn-danger-modern">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});

document.querySelectorAll('.btn-delete-project').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('delete_project_id').value = this.dataset.id;
        document.getElementById('delete_project_name').textContent = this.dataset.name;
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
