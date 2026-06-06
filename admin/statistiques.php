<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';

$kpis = [
    'completion' => (float)$pdo->query('SELECT COALESCE(AVG(pourcentage_avancement), 0) FROM projets')->fetchColumn(),
    'projets_retard' => (int)$pdo->query("SELECT COUNT(*) FROM projets WHERE statut NOT IN ('termine', 'annule') AND date_fin_prevue < CURDATE()")->fetchColumn(),
    'taches_retard' => (int)$pdo->query("SELECT COUNT(*) FROM taches WHERE statut <> 'termine' AND date_echeance < CURDATE()")->fetchColumn(),
    'rapports_mois' => (int)$pdo->query('SELECT COUNT(*) FROM rapports WHERE YEAR(date_soumission) = YEAR(CURDATE()) AND MONTH(date_soumission) = MONTH(CURDATE())')->fetchColumn(),
    'utilisateurs' => (int)$pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn(),
    'clients' => (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'client'")->fetchColumn(),
];

$usersByRole = $pdo->query('
    SELECT role, COUNT(*) AS total
    FROM utilisateurs
    GROUP BY role
    ORDER BY FIELD(role, "admin", "ingenieur", "dessinateur", "client")
')->fetchAll();

$projectsByStatus = $pdo->query('
    SELECT statut, COUNT(*) AS total
    FROM projets
    GROUP BY statut
    ORDER BY FIELD(statut, "en_attente", "en_cours", "suspendu", "termine", "annule")
')->fetchAll();

$financeByClient = $pdo->query('
    SELECT
        CONCAT(u.prenom, " ", u.nom) AS client,
        COALESCE(SUM(f.montant_total), 0) AS facture,
        COALESCE((
            SELECT SUM(pa.montant)
            FROM paiements pa
            WHERE pa.client_id = u.id AND pa.statut = "valide"
        ), 0) AS paye
    FROM utilisateurs u
    LEFT JOIN factures f ON f.client_id = u.id
    WHERE u.role = "client"
    GROUP BY u.id, u.prenom, u.nom
    ORDER BY facture DESC, paye DESC, client
    LIMIT 8
')->fetchAll();

$financeTableRows = $pdo->query('
    SELECT
        CONCAT(u.prenom, " ", u.nom) AS client,
        u.email,
        COALESCE(SUM(f.montant_total), 0) AS facture,
        COALESCE((
            SELECT SUM(pa.montant)
            FROM paiements pa
            WHERE pa.client_id = u.id AND pa.statut = "valide"
        ), 0) AS paye
    FROM utilisateurs u
    LEFT JOIN factures f ON f.client_id = u.id
    WHERE u.role = "client"
    GROUP BY u.id, u.prenom, u.nom, u.email
    ORDER BY facture DESC, paye DESC, client
')->fetchAll();

$roleLabels = [
    'admin' => 'Admins',
    'ingenieur' => 'Ingenieurs',
    'dessinateur' => 'Dessinateurs',
    'client' => 'Clients',
];

$projectStatusLabels = [
    'en_attente' => 'En attente',
    'en_cours' => 'En cours',
    'suspendu' => 'Suspendu',
    'termine' => 'Termine',
    'annule' => 'Annule',
];

$userRoleLabels = array_map(fn($row) => $roleLabels[$row['role']] ?? ucfirst($row['role']), $usersByRole);
$userRoleData = array_map(fn($row) => (int)$row['total'], $usersByRole);
$projectStatusChartLabels = array_map(fn($row) => $projectStatusLabels[$row['statut']] ?? ucfirst($row['statut']), $projectsByStatus);
$projectStatusData = array_map(fn($row) => (int)$row['total'], $projectsByStatus);
$financeClientLabels = array_map(fn($row) => $row['client'], $financeByClient);
$financeFactureData = array_map(fn($row) => (float)$row['facture'], $financeByClient);
$financePayeData = array_map(fn($row) => (float)$row['paye'], $financeByClient);

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<style>
.content-area > .stats-page-header,
.stats-kpis,
.stats-charts-grid {
    width: min(100%, 1280px);
    margin-left: auto;
    margin-right: auto;
}

.stats-page-subtitle {
    margin: 0.5rem 0 0;
    color: #64748b;
    font-size: 0.95rem;
}

.stats-kpis {
    grid-template-columns: repeat(5, minmax(0, 1fr));
}

.stats-charts-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
}

.chart-panel {
    margin-bottom: 0;
}

.chart-panel-wide {
    grid-column: 1 / -1;
}

.chart-box {
    height: 280px;
}

.chart-panel-wide .chart-box {
    height: 340px;
}

.finance-table-section {
    margin-top: 1.25rem;
}

.finance-rate {
    min-width: 120px;
}

@media (max-width: 1200px) {
    .stats-kpis {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 992px) {
    .stats-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stats-charts-grid {
        grid-template-columns: 1fr;
    }

    .chart-panel-wide {
        grid-column: auto;
    }
}

@media (max-width: 768px) {
    .stats-kpis {
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
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i><span>Projets</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="paiements.php" class="nav-link"><i class="bi bi-credit-card"></i><span>Paiements</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i><span>Taches</span></a></li>
                <li class="nav-item"><a href="alertes.php" class="nav-link"><i class="bi bi-exclamation-triangle"></i><span>Alertes</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link"><i class="bi bi-person-gear"></i><span>Administrateur</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Rapports</span></a></li>
                <li class="nav-item"><a href="statistiques.php" class="nav-link active"><i class="bi bi-bar-chart"></i><span>Statistiques</span></a></li>
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
                <div class="navbar-breadcrumb"><i class="bi bi-bar-chart"></i><span>Statistiques</span></div>
            </div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action=""><i class="bi bi-search"></i><input type="text" name="search" placeholder="Rechercher..."></form>
                <a href="notifications.php" class="navbar-icon" title="Notifications"><i class="bi bi-bell"></i></a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>

        <div class="content-area">
            <div class="page-header stats-page-header">
                <div>
                    <h1 class="page-title"><i class="bi bi-bar-chart"></i> Statistiques & Indicateurs</h1>
                    <p class="stats-page-subtitle">Analyse des utilisateurs, projets, factures et paiements enregistres.</p>
                </div>
                <div class="page-actions">
                    <button class="btn-modern btn-primary-modern" onclick="window.print()"><i class="bi bi-file-earmark-pdf"></i> Exporter PDF</button>
                </div>
            </div>

            <div class="stats-grid stats-kpis">
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-pie-chart"></i></div>
                    <div class="stat-card-label">Taux de completion</div>
                    <div class="stat-card-value"><?= round($kpis['completion']) ?>%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon warning"><i class="bi bi-clock-history"></i></div>
                    <div class="stat-card-label">Projets en retard</div>
                    <div class="stat-card-value"><?= $kpis['projets_retard'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-card-label">Taches en retard</div>
                    <div class="stat-card-value"><?= $kpis['taches_retard'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success"><i class="bi bi-people"></i></div>
                    <div class="stat-card-label">Utilisateurs / clients</div>
                    <div class="stat-card-value"><?= $kpis['utilisateurs'] ?> / <?= $kpis['clients'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon primary"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-card-label">Rapports ce mois</div>
                    <div class="stat-card-value"><?= $kpis['rapports_mois'] ?></div>
                </div>
            </div>

            <div class="stats-charts-grid">
                <div class="section-card chart-panel">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-people"></i> Utilisateurs par role</div>
                </div>
                <div class="chart-box"><canvas id="chartUsersRole"></canvas></div>
                </div>

                <div class="section-card chart-panel">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-folder2"></i> Projets par statut</div>
                </div>
                <div class="chart-box"><canvas id="chartProjectsStatus"></canvas></div>
                </div>

                <div class="section-card chart-panel chart-panel-wide">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-cash-stack"></i> Facture vs paye par client</div>
                </div>
                <div class="chart-box"><canvas id="chartClientFinance"></canvas></div>
                </div>
            </div>

            <div class="table-container finance-table-section">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Email</th>
                                <th>Facture</th>
                                <th>Paye valide</th>
                                <th>Reste</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$financeTableRows): ?>
                                <tr><td colspan="6" class="text-center">Aucune donnee financiere.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($financeTableRows as $row): ?>
                                <?php
                                $facture = (float)$row['facture'];
                                $paye = (float)$row['paye'];
                                $reste = max(0, $facture - $paye);
                                $taux = $facture > 0 ? min(100, round($paye / $facture * 100)) : 0;
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['client']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= number_format($facture, 0, ',', ' ') ?></td>
                                    <td><?= number_format($paye, 0, ',', ' ') ?></td>
                                    <td><?= number_format($reste, 0, ',', ' ') ?></td>
                                    <td>
                                        <div class="progress-cell finance-rate">
                                            <div class="progress-bar-container">
                                                <div class="progress-bar-fill" style="width: <?= $taux ?>%;"></div>
                                            </div>
                                            <span class="progress-text"><?= $taux ?>%</span>
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

<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});

const moneyTick = function(value) {
    return Number(value).toLocaleString('fr-FR');
};

new Chart(document.getElementById('chartUsersRole'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($userRoleLabels) ?>,
        datasets: [{
            data: <?= json_encode($userRoleData) ?>,
            backgroundColor: ['#2563eb', '#0284c7', '#65a30d', '#f97316']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

new Chart(document.getElementById('chartProjectsStatus'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($projectStatusChartLabels) ?>,
        datasets: [{
            label: 'Projets',
            data: <?= json_encode($projectStatusData) ?>,
            backgroundColor: ['#64748b', '#2563eb', '#f59e0b', '#10b981', '#ef4444'],
            borderRadius: 8
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('chartClientFinance'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($financeClientLabels) ?>,
        datasets: [
            {
                label: 'Facture',
                data: <?= json_encode($financeFactureData) ?>,
                backgroundColor: '#2563eb',
                borderRadius: 8
            },
            {
                label: 'Paye valide',
                data: <?= json_encode($financePayeData) ?>,
                backgroundColor: '#10b981',
                borderRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, boxWidth: 8, color: '#475569', font: { weight: 700 } }
            },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ' : ' + moneyTick(context.raw);
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    color: '#475569',
                    maxRotation: 0,
                    callback: function(value) {
                        const label = this.getLabelForValue(value);
                        return label.length > 14 ? label.slice(0, 14) + '...' : label;
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#e2e8f0' },
                ticks: { callback: moneyTick, color: '#64748b' }
            }
        }
    }
});
</script>
<?php require_once '../includes/footer.php'; ?>

