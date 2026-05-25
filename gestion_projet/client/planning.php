<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Planning du Projet</h2>
    <div class="card mb-4">
        <div class="card-body">
            <div id="gantt"></div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <strong>Légende :</strong>
            <span class="badge bg-primary">En cours</span>
            <span class="badge bg-success">Terminé</span>
            <span class="badge bg-danger">En retard</span>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.min.js"></script>
<?php require_once '../includes/footer.php'; ?>
