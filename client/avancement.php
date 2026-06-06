<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$stmt = $pdo->prepare("SELECT id, nom, pourcentage_avancement, statut FROM projets WHERE client_id = ? ORDER BY date_debut DESC");
$stmt->execute([$user_id]);
$projets = $stmt->fetchAll();
$projetIds = array_map(fn($projet) => (int)$projet['id'], $projets);
$avancementGlobal = $projets ? (int)round(array_sum(array_column($projets, 'pourcentage_avancement')) / count($projets)) : 0;

$taches = [];
$jalons = [];
if ($projetIds) {
    $placeholders = implode(',', array_fill(0, count($projetIds), '?'));

    $stmt = $pdo->prepare("SELECT t.titre, t.statut, t.pourcentage, t.date_echeance,
                                  p.nom AS projet_nom,
                                  u.nom AS assigne_nom, u.prenom AS assigne_prenom
                           FROM taches t
                           JOIN projets p ON p.id = t.projet_id
                           JOIN utilisateurs u ON u.id = t.assigne_a
                           WHERE t.projet_id IN ($placeholders)
                           ORDER BY t.date_echeance ASC");
    $stmt->execute($projetIds);
    $taches = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT j.titre, j.statut, j.date_prevue, p.nom AS projet_nom
                           FROM jalons j
                           JOIN projets p ON p.id = j.projet_id
                           WHERE j.projet_id IN ($placeholders)
                           ORDER BY j.date_prevue ASC");
    $stmt->execute($projetIds);
    $jalons = $stmt->fetchAll();
}
require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('avancement', 'bi-graph-up', 'Avancement'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-graph-up"></i> Avancement du projet</h1>
    </div>

    <div class="section-card mb-4">
        <div class="progress" style="height: 2rem;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="projet-avancement" style="width: <?= $avancementGlobal ?>%; font-size:1.2rem;"><?= $avancementGlobal ?>%</div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="section-header"><div class="section-title"><i class="bi bi-list-task"></i> Taches du projet</div></div>
        <div class="table-wrapper">
            <table class="modern-table">
                <thead><tr><th>Projet</th><th>Titre</th><th>Assigne a</th><th>Statut</th><th>%</th><th>Date fin</th></tr></thead>
                <tbody id="taches-list">
                    <?php if (!$taches): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune tache disponible.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($taches as $tache): ?>
                        <tr>
                            <td><?= htmlspecialchars($tache['projet_nom']) ?></td>
                            <td><?= htmlspecialchars($tache['titre']) ?></td>
                            <td><?= htmlspecialchars(trim($tache['assigne_prenom'] . ' ' . $tache['assigne_nom'])) ?></td>
                            <td><?= getBadgeStatut($tache['statut']) ?></td>
                            <td><?= (int)$tache['pourcentage'] ?>%</td>
                            <td><?= formatDate($tache['date_echeance']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="section-card mb-4">
                <div class="section-header"><div class="section-title"><i class="bi bi-flag"></i> Jalons</div></div>
                <div id="jalons-timeline">
                    <?php if (!$jalons): ?>
                        <p class="text-muted mb-0">Aucun jalon defini.</p>
                    <?php endif; ?>
                    <?php foreach ($jalons as $jalon): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?= htmlspecialchars($jalon['titre']) ?><br><small class="text-muted"><?= htmlspecialchars($jalon['projet_nom']) ?></small></span>
                            <span><?= getBadgeStatut($jalon['statut']) ?> <?= formatDate($jalon['date_prevue']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-card mb-4">
                <div class="section-header"><div class="section-title"><i class="bi bi-clock-history"></i> Historique des statuts</div></div>
                <div id="statut-historique">
                    <?php if (!$projets): ?>
                        <p class="text-muted mb-0">Aucun projet associe a votre compte.</p>
                    <?php endif; ?>
                    <?php foreach ($projets as $projet): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?= htmlspecialchars($projet['nom']) ?></span>
                            <span><?= getBadgeStatut($projet['statut']) ?> <?= (int)$projet['pourcentage_avancement'] ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
