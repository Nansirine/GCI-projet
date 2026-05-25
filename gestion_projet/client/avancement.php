<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Avancement du Projet</h2>
    <div class="card mb-4">
        <div class="card-body">
            <div class="progress" style="height: 2rem;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="projet-avancement" style="width: 0%; font-size:1.2rem;">0%</div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-white fw-bold">Tâches du Projet</div>
        <div class="card-body p-0">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Titre</th><th>Assigné à</th><th>Statut</th><th>%</th><th>Date fin</th></tr></thead>
                <tbody id="taches-list"></tbody>
            </table>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Jalons</div>
                <div class="card-body" id="jalons-timeline"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Historique des Statuts</div>
                <div class="card-body" id="statut-historique"></div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
