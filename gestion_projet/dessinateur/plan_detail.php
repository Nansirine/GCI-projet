<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Détail du Plan</h2>
    <div class="card mb-4">
        <div class="card-body">
            <!-- Infos plan, prévisualisation -->
            <div class="mb-3"><!-- Prévisualisation PDF/IMG --></div>
            <div class="mb-3">
                <a href="#" class="btn btn-primary me-2">📤 Nouvelle Version</a>
                <a href="#" class="btn btn-info me-2">👥 Partager avec Client</a>
                <a href="#" class="btn btn-secondary me-2">📁 Archiver</a>
                <a href="#" class="btn btn-warning">🔙 Demander Validation</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4"><div class="card-header">Versions</div><div class="card-body"><!-- Tableau versions --></div></div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4"><div class="card-header">Annotations / Commentaires</div><div class="card-body"><!-- Liste commentaires + formulaire --></div></div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
