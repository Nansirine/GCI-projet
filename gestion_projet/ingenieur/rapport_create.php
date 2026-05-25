<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Soumettre un Rapport</h2>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required><!-- Projets dynamiques --></select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Tâche liée</label>
            <select class="form-select" name="tache_id"><!-- Tâches dynamiques --></select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Titre du rapport *</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Contenu détaillé *</label>
            <textarea class="form-control" name="contenu" rows="5" required></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Fichier joint (PDF/DOC/IMG, max 10MB)</label>
            <input type="file" class="form-control" name="fichier_joint">
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2">📤 Soumettre le Rapport</button>
            <button type="submit" class="btn btn-secondary me-2">💾 Enregistrer Brouillon</button>
            <a href="rapports.php" class="btn btn-danger">❌ Annuler</a>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>
