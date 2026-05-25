<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Documents & Plans</h2>
    <form class="row g-2 mb-3">
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
                <tr><th>Plan</th><th>Projet</th><th>Dessinateur</th><th>Version</th><th>Type</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody id="plans-list">
                <!-- Plans dynamiques -->
            </tbody>
        </table>
    </div>
    <!-- Modal commentaire -->
    <div class="modal fade" id="modalComment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Commenter le Plan</h5></div>
                <div class="modal-body">
                    <textarea class="form-control" rows="3" placeholder="Votre commentaire..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Envoyer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
