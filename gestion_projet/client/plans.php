<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Plans Disponibles</h2>
    <form class="row g-2 mb-3">
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
                <tr><th>Titre</th><th>Type</th><th>Version</th><th>Date de partage</th><th>Actions</th></tr>
            </thead>
            <tbody id="plans-list">
                <!-- Plans dynamiques -->
            </tbody>
        </table>
    </div>
    <div id="preview-plan" class="mt-4"><!-- Prévisualisation inline --></div>
    <div id="no-plan-msg" class="alert alert-info d-none">Aucun plan n'est encore disponible. Revenez bientôt.</div>
</div>
<?php require_once '../includes/footer.php'; ?>
