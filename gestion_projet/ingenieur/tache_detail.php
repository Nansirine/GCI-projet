<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Détail de la Tâche</h2>
    <div class="card mb-4">
        <div class="card-body">
            <!-- Infos tâche -->
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Avancement (%)</label>
                    <input type="range" class="form-range" min="0" max="100" name="pourcentage" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="statut">
                        <option value="a_faire">À faire</option>
                        <option value="en_cours">En cours</option>
                        <option value="en_revision">En révision</option>
                        <option value="termine">Terminé</option>
                        <option value="bloque">Bloqué</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">💾 Sauvegarder</button>
            </form>
            <div class="mt-4">
                <a href="#" class="btn btn-success me-2">📝 Soumettre un Rapport pour cette Tâche</a>
                <a href="#" class="btn btn-warning">⚠ Signaler un Problème sur cette Tâche</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4"><div class="card-header">Documents liés</div><div class="card-body"><!-- Plans liés --></div></div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4"><div class="card-header">Rapport lié</div><div class="card-body"><!-- Rapport lié --></div></div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
