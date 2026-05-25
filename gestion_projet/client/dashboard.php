<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
$prenom = $_SESSION['prenom'] ?? '';
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-3">Bonjour <?php echo htmlspecialchars($prenom); ?>, voici l'état de votre projet</h2>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h4 id="projet-nom">Nom du projet</h4>
                    <span class="badge bg-primary" id="projet-statut">Statut</span>
                </div>
                <div class="col-md-5">
                    <div class="progress" style="height: 2rem;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="projet-avancement" style="width: 0%; font-size:1.2rem;">0%</div>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <div>Date début : <span id="projet-date-debut"></span></div>
                    <div>Date fin prévue : <span id="projet-date-fin"></span></div>
                    <div>Jours restants : <span id="projet-jours-restants"></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Tâches Terminées / Total</h6><h2 class="fw-bold" id="stat-taches">0/0</h2></div></div></div>
        <div class="col-md-4"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Plans Disponibles</h6><h2 class="fw-bold" id="stat-plans">0</h2></div></div></div>
        <div class="col-md-4"><div class="card shadow-sm border-0"><div class="card-body text-center"><h6 class="text-muted">Rapports Validés</h6><h2 class="fw-bold" id="stat-rapports">0</h2></div></div></div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Prochain Jalon</div>
                <div class="card-body" id="prochain-jalon"></div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Dernières Mises à Jour</div>
                <ul class="list-group list-group-flush" id="last-notifs"></ul>
            </div>
        </div>
        <div class="col-md-6 d-flex flex-column gap-3 align-items-end">
            <a href="avancement.php" class="btn btn-primary w-100">📊 Voir Avancement</a>
            <a href="rapports.php" class="btn btn-secondary w-100">📋 Mes Rapports</a>
            <a href="demandes.php" class="btn btn-success w-100">💬 Faire une Demande</a>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
