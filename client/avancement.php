<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('avancement', 'bi-graph-up', 'Avancement'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-graph-up"></i> Avancement du projet</h1>
    </div>

    <div class="section-card mb-4">
        <div class="progress" style="height: 2rem;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="projet-avancement" style="width: 0%; font-size:1.2rem;">0%</div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="section-header"><div class="section-title"><i class="bi bi-list-task"></i> Taches du projet</div></div>
        <div class="table-wrapper">
            <table class="modern-table">
                <thead><tr><th>Titre</th><th>Assigne a</th><th>Statut</th><th>%</th><th>Date fin</th></tr></thead>
                <tbody id="taches-list"></tbody>
            </table>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="section-card mb-4">
                <div class="section-header"><div class="section-title"><i class="bi bi-flag"></i> Jalons</div></div>
                <div id="jalons-timeline"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-card mb-4">
                <div class="section-header"><div class="section-title"><i class="bi bi-clock-history"></i> Historique des statuts</div></div>
                <div id="statut-historique"></div>
            </div>
        </div>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
