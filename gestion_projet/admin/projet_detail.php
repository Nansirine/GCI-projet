<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Détail du Projet</h2>
    <ul class="nav nav-tabs mb-3" id="projetTabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">Vue Générale</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-taches" data-bs-toggle="tab" data-bs-target="#taches" type="button" role="tab">Tâches</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-jalons" data-bs-toggle="tab" data-bs-target="#jalons" type="button" role="tab">Jalons</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-rapports" data-bs-toggle="tab" data-bs-target="#rapports" type="button" role="tab">Rapports</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-plans" data-bs-toggle="tab" data-bs-target="#plans" type="button" role="tab">Plans</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-alertes" data-bs-toggle="tab" data-bs-target="#alertes" type="button" role="tab">Alertes</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-messages" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">Messages</button></li>
    </ul>
    <div class="tab-content" id="projetTabsContent">
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <!-- Vue Générale : infos projet, membres, boutons -->
        </div>
        <div class="tab-pane fade" id="taches" role="tabpanel">
            <!-- Liste des tâches du projet -->
        </div>
        <div class="tab-pane fade" id="jalons" role="tabpanel">
            <!-- Timeline des jalons -->
        </div>
        <div class="tab-pane fade" id="rapports" role="tabpanel">
            <!-- Liste des rapports -->
        </div>
        <div class="tab-pane fade" id="plans" role="tabpanel">
            <!-- Liste des plans -->
        </div>
        <div class="tab-pane fade" id="alertes" role="tabpanel">
            <!-- Liste des alertes -->
        </div>
        <div class="tab-pane fade" id="messages" role="tabpanel">
            <!-- Messagerie interne -->
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
