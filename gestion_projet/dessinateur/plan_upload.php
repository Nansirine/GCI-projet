<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Déposer un Plan</h2>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required><!-- Projets dynamiques --></select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre du plan *</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="2"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type de plan *</label>
            <select class="form-select" name="type_plan" required>
                <option value="architectural">Architectural</option>
                <option value="structural">Structural</option>
                <option value="electrique">Électrique</option>
                <option value="plomberie">Plomberie</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Fichier *</label>
            <input type="file" class="form-control" name="fichier" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Statut initial</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="brouillon" checked>
                <label class="form-check-label">Brouillon</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="soumis">
                <label class="form-check-label">Soumettre directement</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="partage_client" value="1">
                <label class="form-check-label">Partager immédiatement avec le client</label>
            </div>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2">📤 Déposer le Plan</button>
            <a href="plans.php" class="btn btn-danger">❌ Annuler</a>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>
