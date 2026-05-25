<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Plans Déposés</h6><h2 class="fw-bold" id="stat-plans-deposes">0</h2></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Plans Validés</h6><h2 class="fw-bold" id="stat-plans-valides">0</h2></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Plans En Attente</h6><h2 class="fw-bold" id="stat-plans-attente">0</h2></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Plans Partagés Client</h6><h2 class="fw-bold" id="stat-plans-client">0</h2></div></div></div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Plans Récents</div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Projet</th><th>Titre</th><th>Version</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody id="plans-list"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Mes Projets Assignés</div>
                <ul class="list-group list-group-flush" id="projets-assignes"></ul>
            </div>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Dernières Notifications</div>
                <ul class="list-group list-group-flush" id="last-notifs"></ul>
            </div>
            <div class="mb-4 d-flex gap-2">
                <a href="plan_upload.php" class="btn btn-success flex-fill">+ Déposer un Plan</a>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
