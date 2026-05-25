<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Rapports Validés</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Titre</th><th>Ingénieur</th><th>Date validation</th><th>Actions</th></tr>
            </thead>
            <tbody id="rapports-list">
                <!-- Rapports dynamiques -->
            </tbody>
        </table>
    </div>
    <div id="no-rapport-msg" class="alert alert-info d-none">Aucun rapport validé disponible pour le moment.</div>
    <!-- Modal lecture rapport -->
    <div class="modal fade" id="modalRapport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Lecture du Rapport</h5></div>
                <div class="modal-body" id="rapport-content"></div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button></div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
