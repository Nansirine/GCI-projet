<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';

$projets = $pdo->prepare("SELECT DISTINCT p.id, p.nom FROM projets p LEFT JOIN affectations a ON a.projet_id = p.id LEFT JOIN taches t ON t.projet_id = p.id WHERE a.utilisateur_id = ? OR t.assigne_a = ? ORDER BY p.nom");
$projets->execute([$user_id, $user_id]);
$projets = $projets->fetchAll();
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('taches', 'bi-list-task', 'Mes taches'); ?>
<div class="page-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Mes Tâches</h2>
    </div>
    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="statut">
                <option value="">Tous</option>
                <option value="a_faire">À Faire</option>
                <option value="en_cours">En Cours</option>
                <option value="termine">Terminé</option>
                <option value="bloque">Bloqué</option>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="projet_id">
                <option value="">Tous projets</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= (string)($_GET['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>><?= htmlspecialchars($projet['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filtrer</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Titre</th><th>Priorité</th><th>Statut</th><th>%</th><th>Échéance</th><th>Actions</th></tr>
            </thead>
            <tbody id="taches-list">
                <!-- Tâches dynamiques -->
            </tbody>
        </table>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
