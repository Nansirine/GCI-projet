<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';


$stats = [
    'total_projets' => (int)$pdo->query('SELECT COUNT(*) FROM projets')->fetchColumn(),
    'projets_en_cours' => (int)$pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'en_cours'")->fetchColumn(),
    'taches_retard' => (int)$pdo->query("SELECT COUNT(*) FROM taches WHERE statut <> 'termine' AND date_echeance < CURDATE()") ->fetchColumn(),
    'membres_actifs' => (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE statut = 'actif'")->fetchColumn(),
];

// Revenus generes par annee, bases sur les factures emises.
$revenuParAnneeStmt = $pdo->query('SELECT YEAR(date_emission) AS annee, COALESCE(SUM(montant_total), 0) AS total FROM factures GROUP BY YEAR(date_emission) ORDER BY annee');
$revenuParAnnee = $revenuParAnneeStmt->fetchAll(PDO::FETCH_ASSOC);
if (!$revenuParAnnee) {
    $revenuParAnnee = [['annee' => date('Y'), 'total' => 0]];
}
$revenuLabels = array_map(fn($r) => (string)$r['annee'], $revenuParAnnee);
$revenuData = array_map(fn($r) => (float)$r['total'], $revenuParAnnee);
$revenuTotal = array_sum($revenuData);
$revenuMoyenne = count($revenuData) ? $revenuTotal / count($revenuData) : 0;
$revenuMax = $revenuData ? max($revenuData) : 0;
$revenuBestIndex = $revenuData ? array_search($revenuMax, $revenuData, true) : 0;
$revenuBestYear = $revenuLabels[$revenuBestIndex] ?? date('Y');

$recentProjectsStmt = $pdo->query('
    SELECT p.id, p.nom, p.statut, p.pourcentage_avancement, p.date_fin_prevue,
           u.nom AS client_nom, u.prenom AS client_prenom
    FROM projets p
    LEFT JOIN utilisateurs u ON p.client_id = u.id
    ORDER BY p.date_creation DESC
    LIMIT 5
');
$recentProjects = $recentProjectsStmt->fetchAll();

$chartStmt = $pdo->query('
    SELECT nom, statut, pourcentage_avancement
    FROM projets
    ORDER BY date_creation DESC
    LIMIT 6
');
$chartProjects = $chartStmt->fetchAll();

$alertsStmt = $pdo->query('
    SELECT a.titre, a.niveau, a.date_creation, p.nom AS projet_nom
    FROM alertes a
    LEFT JOIN projets p ON a.projet_id = p.id
    ORDER BY a.date_creation DESC
    LIMIT 3
');
$alerts = $alertsStmt->fetchAll();

$reportsStmt = $pdo->query('
    SELECT r.id, r.titre, r.date_soumission, u.nom, u.prenom
    FROM rapports r
    LEFT JOIN utilisateurs u ON r.ingenieur_id = u.id
    WHERE r.statut = "soumis"
    ORDER BY r.date_soumission DESC
    LIMIT 3
');
$reports = $reportsStmt->fetchAll();

$statusColors = [
    'en_cours' => '#3b82f6',
    'en_attente' => '#64748b',
    'termine' => '#10b981',
    'suspendu' => '#f59e0b',
    'annule' => '#ef4444',
];
$chartLabels = array_map(fn($p) => $p['nom'], $chartProjects);
$chartData = array_map(fn($p) => (int)$p['pourcentage_avancement'], $chartProjects);
$chartRemainingData = array_map(fn($p) => max(0, 100 - (int)$p['pourcentage_avancement']), $chartProjects);
$chartStatusLabels = array_map(fn($p) => ucfirst(str_replace('_', ' ', $p['statut'])), $chartProjects);
$chartColors = array_map(fn($p) => $statusColors[$p['statut']] ?? '#3b82f6', $chartProjects);

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/admin-dashboard.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<style>
.project-chart-panel {
    width: 100%;
    height: 360px;
    padding: 0.25rem 0 0.5rem;
}

.dashboard-chart-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1rem;
    color: #64748b;
    font-size: 0.875rem;
}

.dashboard-chart-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.dashboard-chart-dot {
    width: 0.65rem;
    height: 0.65rem;
    border-radius: 999px;
}

.revenue-chart-panel {
    height: 320px;
    position: relative;
}

.revenue-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1.25rem;
}

.revenue-summary-item {
    padding: 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

.revenue-summary-label {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
}

.revenue-summary-value {
    margin-top: 0.35rem;
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 800;
}

.content-area > .dashboard-page-header,
.dashboard-kpis,
.dashboard-workspace,
.content-area > .dashboard-table-section,
.dashboard-bottom-grid {
    width: min(100%, 1280px);
    margin-left: auto;
    margin-right: auto;
}

.dashboard-page-header {
    margin-bottom: 1.5rem;
}

.dashboard-page-subtitle {
    margin: 0.5rem 0 0;
    color: #64748b;
    font-size: 0.95rem;
}

.dashboard-kpis {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.dashboard-workspace {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(360px, 0.9fr);
    gap: 1.5rem;
    align-items: stretch;
}

.dashboard-workspace .section-card,
.dashboard-bottom-grid .section-card {
    width: 100%;
    margin-bottom: 0;
}

.dashboard-table-section {
    margin-top: 1.5rem;
}

.dashboard-bottom-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 1200px) {
    .dashboard-kpis,
    .dashboard-workspace,
    .dashboard-bottom-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-kpis,
    .dashboard-workspace,
    .dashboard-bottom-grid {
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
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="bi bi-house-door"></i><span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i><span>Projets</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="paiements.php" class="nav-link"><i class="bi bi-credit-card"></i><span>Paiements</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i><span>Taches</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-people"></i><span>Utilisateurs</span></a></li>
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
                <div class="navbar-breadcrumb"><img src="/gestion_projet/image/WhatsApp%20Image%202026-05-11%20at%2017.24.59.jpeg" alt="Logo Buildflow" width="24" height="24" style="object-fit:cover;border-radius:50%;margin-right:6px;vertical-align:middle;"> <span>Tableau de bord</span></div>
            </div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action=""><i class="bi bi-search"></i><input type="text" name="search" placeholder="Rechercher..."></form>
                <a href="notifications.php" class="navbar-icon" title="Notifications"><i class="bi bi-bell"></i></a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>

        <div class="content-area">
            <div class="page-header dashboard-page-header">
                <div>
                    <h1 class="page-title"><i class="bi bi-speedometer2"></i> Tableau de bord</h1>
                    <p class="dashboard-page-subtitle">Vue d'ensemble des projets, revenus et activites recentes.</p>
                </div>
                <div class="page-actions">
                    <a href="projet_create.php" class="btn-modern btn-success-modern"><i class="bi bi-plus-circle"></i> Nouveau projet</a>
                </div>
            </div>

            <div class="stats-grid dashboard-kpis">
                <a href="projets.php" class="stat-card fade-in text-decoration-none">
                    <div class="stat-card-icon primary"><span class="bi bi-folder2"></span></div>
                    <div class="stat-card-label">Total projets</div>
                    <div class="stat-card-value"><?= $stats['total_projets'] ?></div>
                    <div class="stat-card-trend"><span class="bi bi-arrow-right"></span> Voir la liste</div>
                </a>
                <a href="projets.php?statut=en_cours" class="stat-card fade-in text-decoration-none">
                    <div class="stat-card-icon warning"><span class="bi bi-gear-fill"></span></div>
                    <div class="stat-card-label">En cours</div>
                    <div class="stat-card-value"><?= $stats['projets_en_cours'] ?></div>
                    <div class="progress-modern" style="margin-top:8px;"><div class="progress-bar-modern" style="width: <?= $stats['total_projets'] ? min(100, round($stats['projets_en_cours'] / $stats['total_projets'] * 100)) : 0 ?>%"></div></div>
                </a>
                <a href="taches.php" class="stat-card fade-in text-decoration-none">
                    <div class="stat-card-icon danger"><span class="bi bi-exclamation-triangle"></span></div>
                    <div class="stat-card-label">Taches en retard</div>
                    <div class="stat-card-value"><?= $stats['taches_retard'] ?></div>
                    <div class="badge-modern badge-danger">A surveiller</div>
                </a>
                <a href="utilisateurs.php" class="stat-card fade-in text-decoration-none">
                    <div class="stat-card-icon success"><span class="bi bi-people-fill"></span></div>
                    <div class="stat-card-label">Membres actifs</div>
                    <div class="stat-card-value"><?= $stats['membres_actifs'] ?></div>
                    <div class="stat-card-trend"><span class="bi bi-person-check"></span> Equipe active</div>
                </a>
            </div>

            <div class="dashboard-workspace">
            <div class="section-card fade-in">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-graph-up-arrow"></i> Evolution du chiffre genere par annee</div>
                </div>
                <div class="revenue-chart-panel">
                    <canvas id="revenuAnneeChart"></canvas>
                </div>
                <div class="revenue-summary">
                    <div class="revenue-summary-item">
                        <div class="revenue-summary-label">Total genere</div>
                        <div class="revenue-summary-value"><?= number_format((float)$revenuTotal, 0, ',', ' ') ?></div>
                    </div>
                    <div class="revenue-summary-item">
                        <div class="revenue-summary-label">Moyenne annuelle</div>
                        <div class="revenue-summary-value"><?= number_format((float)$revenuMoyenne, 0, ',', ' ') ?></div>
                    </div>
                    <div class="revenue-summary-item">
                        <div class="revenue-summary-label">Meilleure annee</div>
                        <div class="revenue-summary-value"><?= htmlspecialchars($revenuBestYear) ?> - <?= number_format((float)$revenuMax, 0, ',', ' ') ?></div>
                    </div>
                </div>
            </div>

            <div class="section-card fade-in">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-bar-chart"></i> Flux d'avancement des projets</div>
                    <a href="projets.php" class="section-action"><i class="bi bi-arrow-right"></i> Voir tous</a>
                </div>
                <?php if ($chartProjects): ?>
                    <div class="project-chart-panel">
                        <canvas id="projectProgressChart"></canvas>
                    </div>
                    <div class="dashboard-chart-meta">
                        <span class="dashboard-chart-chip"><span class="dashboard-chart-dot" style="background:#3b82f6"></span> En cours</span>
                        <span class="dashboard-chart-chip"><span class="dashboard-chart-dot" style="background:#64748b"></span> En attente</span>
                        <span class="dashboard-chart-chip"><span class="dashboard-chart-dot" style="background:#10b981"></span> Termine</span>
                        <span class="dashboard-chart-chip"><span class="dashboard-chart-dot" style="background:#f59e0b"></span> Suspendu</span>
                        <span class="dashboard-chart-chip"><span class="dashboard-chart-dot" style="background:#ef4444"></span> Annule</span>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
                        <div class="empty-state-title">Aucun projet a afficher</div>
                        <a href="projet_create.php" class="btn-modern btn-success-modern">Creer un projet</a>
                    </div>
                <?php endif; ?>
            </div>
            </div>

            <div class="section-card fade-in dashboard-table-section">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-folder2"></i> Projets recents</div>
                    <a href="projet_create.php" class="section-action"><i class="bi bi-plus"></i> Nouveau projet</a>
                </div>
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr><th>Nom</th><th>Client</th><th>Statut</th><th>Avancement</th><th>Echeance</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$recentProjects): ?>
                                <tr><td colspan="6" class="text-center">Aucun projet recent.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($recentProjects as $projet): ?>
                                <?php $progress = (int)$projet['pourcentage_avancement']; ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($projet['nom']) ?></strong></td>
                                    <td><?= htmlspecialchars(trim(($projet['client_prenom'] ?? '') . ' ' . ($projet['client_nom'] ?? '')) ?: '-') ?></td>
                                    <td><span class="status-badge status-<?= htmlspecialchars(str_replace('_', '-', $projet['statut'])) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $projet['statut']))) ?></span></td>
                                    <td>
                                        <div class="progress-cell">
                                            <div class="progress-bar-container"><div class="progress-bar-fill" style="width: <?= $progress ?>%;"></div></div>
                                            <span class="progress-text"><?= $progress ?>%</span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($projet['date_fin_prevue']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="projet_detail.php?id=<?= (int)$projet['id'] ?>" class="btn-action btn-action-view" title="Voir"><i class="bi bi-eye"></i></a>
                                            <a href="projet_create.php?id=<?= (int)$projet['id'] ?>" class="btn-action btn-action-edit" title="Modifier"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn-action btn-action-delete btn-delete-project" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteProjectModal" data-id="<?= (int)$projet['id'] ?>" data-name="<?= htmlspecialchars($projet['nom'], ENT_QUOTES) ?>"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-bottom-grid">
                <div class="section-card fade-in">
                    <div class="section-header">
                        <div class="section-title"><i class="bi bi-exclamation-triangle"></i> Alertes recentes</div>
                        <a href="../ingenieur/alertes.php" class="section-action">Voir</a>
                    </div>
                    <ul class="admin-alert-list">
                        <?php if (!$alerts): ?>
                            <li class="admin-alert-item">Aucune alerte recente.</li>
                        <?php endif; ?>
                        <?php foreach ($alerts as $alert): ?>
                            <li class="admin-alert-item">
                                <span class="admin-alert-icon admin-alert-<?= $alert['niveau'] === 'critique' ? 'critical' : ($alert['niveau'] === 'avertissement' ? 'warning' : 'info') ?> bi bi-exclamation-circle-fill"></span>
                                <span class="admin-alert-title"><?= htmlspecialchars($alert['titre']) ?></span>
                                <span class="admin-alert-project"><?= htmlspecialchars($alert['projet_nom'] ?? '-') ?></span>
                                <span class="admin-alert-time"><?= date('d/m/Y', strtotime($alert['date_creation'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="section-card fade-in">
                    <div class="section-header">
                        <div class="section-title"><i class="bi bi-file-earmark-text"></i> Rapports en attente</div>
                        <a href="rapports.php" class="section-action">Voir</a>
                    </div>
                    <ul class="admin-report-list">
                        <?php if (!$reports): ?>
                            <li class="admin-report-item">Aucun rapport en attente.</li>
                        <?php endif; ?>
                        <?php foreach ($reports as $report): ?>
                            <li class="admin-report-item">
                                <img src="/gestion_projet/assets/img/default-user.png" class="admin-report-avatar" alt="">
                                <span class="admin-report-title"><?= htmlspecialchars($report['titre']) ?></span>
                                <span class="admin-report-date"><?= date('d/m/Y', strtotime($report['date_soumission'])) ?></span>
                                <a class="admin-report-btn btn-modern btn-outline-modern btn-sm" href="rapport_detail.php?id=<?= (int)$report['id'] ?>">Voir</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade modal-modern" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="projets.php">
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

<?php if ($chartProjects): ?>
const projectProgressChart = document.getElementById('projectProgressChart');
new Chart(projectProgressChart, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            {
                label: 'Realise',
                data: <?= json_encode($chartData) ?>,
                backgroundColor: <?= json_encode($chartColors) ?>,
                borderColor: <?= json_encode($chartColors) ?>,
                borderWidth: 1,
                borderRadius: { topLeft: 8, bottomLeft: 8 },
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.72
            },
            {
                label: 'Reste',
                data: <?= json_encode($chartRemainingData) ?>,
                backgroundColor: '#e2e8f0',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                borderRadius: { topRight: 8, bottomRight: 8 },
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.72
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    boxWidth: 8,
                    color: '#475569',
                    font: { weight: 700 }
                }
            },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 12,
                callbacks: {
                    title: function(items) {
                        return items[0].label + ' - ' + <?= json_encode($chartStatusLabels) ?>[items[0].dataIndex];
                    },
                    label: function(context) {
                        return context.dataset.label + ' : ' + context.raw + '%';
                    }
                }
            }
        },
        scales: {
            x: {
                min: 0,
                max: 100,
                stacked: true,
                grid: { color: '#e2e8f0' },
                ticks: {
                    color: '#64748b',
                    callback: function(value) { return value + '%'; }
                }
            },
            y: {
                stacked: true,
                grid: { display: false },
                ticks: {
                    color: '#475569',
                    font: { weight: 700 }
                }
            }
        }
    }
});
<?php endif; ?>

const revenuAnneeChart = document.getElementById('revenuAnneeChart');
if (revenuAnneeChart) {
new Chart(revenuAnneeChart, {
    type: 'line',
    data: {
        labels: <?= json_encode($revenuLabels) ?>,
        datasets: [{
            label: 'Chiffre genere',
            data: <?= json_encode($revenuData) ?>,
            fill: true,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.12)',
            borderWidth: 3,
            tension: 0.35,
            pointBackgroundColor: '#2563eb',
            pointBorderColor: '#fff',
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        return 'Genere : ' + Number(context.raw).toLocaleString('fr-FR') + ' DH';
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#475569', font: { weight: 700 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#e2e8f0' },
                ticks: {
                    color: '#64748b',
                    callback: function(value) { return Number(value).toLocaleString('fr-FR') + ' DH'; }
                }
            }
        }
    }
});
}
</script>

<?php require_once '../includes/footer.php'; ?>


