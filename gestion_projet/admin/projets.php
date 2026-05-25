<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Gestion des Projets</h2>
        <a href="projet_create.php" class="btn btn-success">+ Nouveau Projet</a>
    </div>
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Rechercher un projet...">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="statut"><option value="">Tous Statuts</option></select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="client"><option value="">Tous Clients</option></select>
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="date_debut">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="date_fin">
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th><th>Client</th><th>Statut</th><th>Avancement</th><th>Date Début</th><th>Date Fin</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="projets-list">
                <!-- Projets dynamiques -->
            </tbody>
        </table>
    </div>
    <nav><ul class="pagination justify-content-center mt-3"><!-- Pagination dynamique --></ul></nav>
</div>
<!-- Modal Suppression -->
<div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Confirmer la suppression</h5></div>
            <div class="modal-body">Voulez-vous vraiment supprimer ce projet ?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger">Supprimer</button>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
