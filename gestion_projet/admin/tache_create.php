<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Créer une Tâche</h2>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required><!-- Projets dynamiques --></select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Assigné à *</label>
            <select class="form-select" name="assigne_a" required><!-- Ingénieurs dynamiques --></select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Titre *</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Description *</label>
            <textarea class="form-control" name="description" rows="2" required></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date début *</label>
            <input type="date" class="form-control" name="date_debut" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date échéance *</label>
            <input type="date" class="form-control" name="date_echeance" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Priorité *</label>
            <select class="form-select" name="priorite" required>
                <option value="basse">Basse</option>
                <option value="moyenne">Moyenne</option>
                <option value="haute">Haute</option>
                <option value="urgente">Urgente</option>
            </select>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2">✅ Créer Tâche</button>
            <a href="projets.php" class="btn btn-secondary">❌ Annuler</a>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>
