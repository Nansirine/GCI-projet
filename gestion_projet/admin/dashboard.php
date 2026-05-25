<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Projets</h6>
                    <h2 class="fw-bold" id="stat-total-projets">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Projets En Cours</h6>
                    <h2 class="fw-bold" id="stat-projets-cours">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Tâches En Retard</h6>
                    <h2 class="fw-bold" id="stat-taches-retard">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Membres Actifs</h6>
                    <h2 class="fw-bold" id="stat-membres-actifs">0</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Avancement des Projets</div>
                <div class="card-body">
                    <canvas id="chartAvancement"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">5 Derniers Projets</div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Nom</th><th>Statut</th><th>Avancement</th><th>Date Fin</th><th>Actions</th></tr></thead>
                        <tbody id="last-projects"></tbody>
                    </table>
                </div>
            </div>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">5 Dernières Alertes</div>
                <ul class="list-group list-group-flush" id="last-alerts"></ul>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">5 Derniers Rapports à Valider</div>
                <ul class="list-group list-group-flush" id="last-reports"></ul>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col">
            <a href="projet_create.php" class="btn btn-success me-2">+ Nouveau Projet</a>
            <a href="tache_create.php" class="btn btn-primary me-2">+ Créer Tâche</a>
            <a href="rapports.php" class="btn btn-secondary">Voir Tous les Rapports</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart.js et JS dynamique à compléter côté serveur
</script>
<?php require_once '../includes/footer.php'; ?>
