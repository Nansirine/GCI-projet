<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Statistiques & Indicateurs</h2>
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <canvas id="chartStatut"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <canvas id="chartAvancement"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <canvas id="chartRapports"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <canvas id="chartTaches"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted">Taux de complétion moyen</h6>
                    <h3 class="fw-bold" id="kpi-completion">0%</h3>
                </div>
            </div>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted">Projets en retard</h6>
                    <h3 class="fw-bold" id="kpi-projets-retard">0</h3>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Tâches en retard</h6>
                    <h3 class="fw-bold" id="kpi-taches-retard">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-8 d-flex align-items-end justify-content-end">
            <button class="btn btn-primary" onclick="window.print()">📊 Exporter PDF</button>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart.js à compléter côté serveur
</script>
<?php require_once '../includes/footer.php'; ?>
