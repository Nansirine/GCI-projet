<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Mes Tâches En Cours</h6><h2 class="fw-bold" id="stat-taches-cours">0</h2></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Tâches Terminées</h6><h2 class="fw-bold" id="stat-taches-terminees">0</h2></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Rapports Soumis</h6><h2 class="fw-bold" id="stat-rapports">0</h2></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Alertes Ouvertes</h6><h2 class="fw-bold" id="stat-alertes">0</h2></div></div></div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Mes Tâches Assignées</div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Projet</th><th>Tâche</th><th>Priorité</th><th>Statut</th><th>Avancement</th><th>Échéance</th></tr></thead>
                        <tbody id="taches-list"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Plans Récents</div>
                <ul class="list-group list-group-flush" id="plans-recents"></ul>
            </div>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Dernières Notifications</div>
                <ul class="list-group list-group-flush" id="last-notifs"></ul>
            </div>
            <div class="mb-4 d-flex gap-2">
                <a href="rapport_create.php" class="btn btn-success flex-fill">+ Soumettre Rapport</a>
                <a href="alertes.php" class="btn btn-warning flex-fill">⚠ Signaler Problème</a>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
