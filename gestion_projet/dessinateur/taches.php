<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Tâches liées aux Plans</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Tâche</th><th>Assigné à</th><th>Statut</th><th>Priorité</th><th>Actions</th></tr>
            </thead>
            <tbody id="taches-list">
                <!-- Tâches dynamiques -->
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
