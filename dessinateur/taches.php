<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$stmt = $pdo->prepare("SELECT t.id, t.titre, t.statut, t.priorite, t.pourcentage,
                              p.nom AS projet_nom,
                              u.nom AS assigne_nom, u.prenom AS assigne_prenom
                       FROM taches t
                       JOIN projets p ON p.id = t.projet_id
                       JOIN affectations a ON a.projet_id = p.id
                       JOIN utilisateurs u ON u.id = t.assigne_a
                       WHERE a.utilisateur_id = ?
                       ORDER BY t.date_echeance ASC");
$stmt->execute([$user_id]);
$taches = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('taches', 'bi-list-task', 'Taches'); ?>
<div class="page-container">
    <h2 class="fw-bold mb-4">Taches liees aux projets</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Projet</th><th>Tache</th><th>Assigne a</th><th>Statut</th><th>Priorite</th><th>Avancement</th><th>Actions</th></tr>
            </thead>
            <tbody id="taches-list">
                <?php if (!$taches): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune tache liee a vos projets.</td></tr>
                <?php endif; ?>
                <?php foreach ($taches as $tache): ?>
                    <tr>
                        <td><?= htmlspecialchars($tache['projet_nom']) ?></td>
                        <td><?= htmlspecialchars($tache['titre']) ?></td>
                        <td><?= htmlspecialchars(trim($tache['assigne_prenom'] . ' ' . $tache['assigne_nom'])) ?></td>
                        <td><?= getBadgeStatut($tache['statut']) ?></td>
                        <td><?= getPriorityBadge($tache['priorite']) ?></td>
                        <td><?= (int)$tache['pourcentage'] ?>%</td>
                        <td><a href="plans.php" class="btn-modern btn-outline-modern"><i class="bi bi-file-earmark"></i> Plans</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
