<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Mes Plans</h2>
        <a href="plan_upload.php" class="btn btn-success">+ Déposer un Plan</a>
    </div>
    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="statut">
                <option value="">Tous</option>
                <option value="brouillon">Brouillon</option>
                <option value="soumis">Soumis</option>
                <option value="valide">Validé</option>
                <option value="rejete">Rejeté</option>
                <option value="archive">Archivé</option>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="projet_id"><option value="">Tous Projets</option></select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="type_plan"><option value="">Tous Types</option></select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filtrer</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Titre</th><th>Type</th><th>Version</th><th>Statut</th><th>Partagé Client</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody id="plans-list">
                <!-- Plans dynamiques -->
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
