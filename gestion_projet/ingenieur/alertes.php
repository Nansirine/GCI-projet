<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Signaler un Problème</h2>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required><!-- Projets dynamiques --></select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tâche liée</label>
            <select class="form-select" name="tache_id"><!-- Tâches dynamiques --></select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Niveau *</label>
            <select class="form-select" name="niveau" required>
                <option value="info">Info</option>
                <option value="avertissement">Avertissement</option>
                <option value="critique">Critique</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre du problème *</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description détaillée *</label>
            <textarea class="form-control" name="description" rows="2" required></textarea>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-warning">⚠ Signaler le Problème</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Titre</th><th>Niveau</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody id="alertes-list">
                <!-- Alertes dynamiques -->
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
