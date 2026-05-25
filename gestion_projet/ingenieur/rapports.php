<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Mes Rapports</h2>
        <a href="rapport_create.php" class="btn btn-success">+ Nouveau Rapport</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Titre</th><th>Projet</th><th>Statut</th><th>Date soumission</th><th>Actions</th></tr>
            </thead>
            <tbody id="rapports-list">
                <!-- Rapports dynamiques -->
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
