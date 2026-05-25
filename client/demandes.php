<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$projets = $pdo->prepare("SELECT id, nom FROM projets WHERE client_id = ? ORDER BY nom");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();
require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('demandes', 'bi-chat', 'Demandes'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-chat"></i> Mes demandes</h1>
    </div>

    <div class="section-card mb-4">
        <div class="section-header"><div class="section-title"><i class="bi bi-send"></i> Nouvelle demande</div></div>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="filter-label">Titre de la demande *</label>
                <input type="text" class="form-control-modern" name="titre" required>
            </div>
            <div class="col-md-6">
                <label class="filter-label">Projet concerne *</label>
                <select class="filter-select" name="projet_id" required>
                    <option value="">Selectionner un projet</option>
                    <?php foreach ($projets as $projet): ?>
                        <option value="<?= (int)$projet['id'] ?>"><?= htmlspecialchars($projet['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12">
                <label class="filter-label">Description detaillee *</label>
                <textarea class="form-control-modern" name="description" rows="3" required></textarea>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn-modern btn-success-modern"><i class="bi bi-send"></i> Envoyer la demande</button>
            </div>
        </form>
        <div id="demande-success" class="alert alert-success d-none mt-3">Votre demande a ete envoyee. Vous recevrez une reponse bientot.</div>
    </div>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead><tr><th>Titre</th><th>Date</th><th>Statut</th><th>Reponse</th><th>Actions</th></tr></thead>
                <tbody id="demandes-list"></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade modal-modern" id="modalReponse" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Reponse de l'administrateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="reponse-content"></div>
                <div class="modal-footer"><button class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Fermer</button></div>
            </div>
        </div>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
