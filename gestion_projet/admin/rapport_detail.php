<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Détail du Rapport</h2>
    <div class="card mb-4">
        <div class="card-body">
            <!-- Titre, contenu, ingénieur, projet, tâche, date, fichier joint -->
            <a href="#" class="btn btn-outline-primary mb-2">📥 Télécharger Fichier Joint</a>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Commentaire Admin</label>
                    <textarea class="form-control" name="commentaire_admin" rows="2"></textarea>
                </div>
                <button type="submit" name="valider" class="btn btn-success me-2">✅ Valider le Rapport</button>
                <button type="submit" name="rejeter" class="btn btn-danger">❌ Rejeter avec Commentaire</button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
