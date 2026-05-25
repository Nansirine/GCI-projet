<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Mes Demandes</h2>
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Titre de la demande *</label>
                    <input type="text" class="form-control" name="titre" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description détaillée *</label>
                    <textarea class="form-control" name="description" rows="3" required></textarea>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-success">📨 Envoyer la Demande</button>
                </div>
            </form>
            <div id="demande-success" class="alert alert-success d-none mt-3">Votre demande a été envoyée. Vous recevrez une réponse bientôt.</div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-white fw-bold">Historique de mes demandes</div>
        <div class="card-body p-0">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Titre</th><th>Date</th><th>Statut</th><th>Réponse</th><th>Actions</th></tr></thead>
                <tbody id="demandes-list"></tbody>
            </table>
        </div>
    </div>
    <!-- Modal réponse -->
    <div class="modal fade" id="modalReponse" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Réponse de l'Administrateur</h5></div>
                <div class="modal-body" id="reponse-content"></div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button></div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
