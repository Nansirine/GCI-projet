<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/functions.php';

$rapportId = (int)($_GET['id'] ?? 0);
$message = '';
$messageType = '';

if (!$rapportId) {
    header('Location: rapports.php');
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, p.nom AS projet_nom, p.client_id,
                              t.titre AS tache_titre,
                              u.nom AS ingenieur_nom, u.prenom AS ingenieur_prenom
                       FROM rapports r
                       JOIN projets p ON p.id = r.projet_id
                       LEFT JOIN taches t ON t.id = r.tache_id
                       JOIN utilisateurs u ON u.id = r.ingenieur_id
                       WHERE r.id = ?");
$stmt->execute([$rapportId]);
$rapport = $stmt->fetch();

if (!$rapport) {
    header('Location: rapports.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';
    $commentaire = sanitize($_POST['commentaire_admin'] ?? '');

    if (!in_array($action, ['valide', 'rejete'], true)) {
        $message = 'Action invalide.';
        $messageType = 'danger';
    } elseif ($action === 'rejete' && strlen($commentaire) < 5) {
        $message = 'Un commentaire est requis pour rejeter un rapport.';
        $messageType = 'danger';
    } else {
        $dateValidation = $action === 'valide' ? date('Y-m-d H:i:s') : null;
        $update = $pdo->prepare('UPDATE rapports SET statut = ?, commentaire_admin = ?, date_validation = ? WHERE id = ?');
        $update->execute([$action, $commentaire, $dateValidation, $rapportId]);

        $notificationMessage = $action === 'valide'
            ? 'Votre rapport "' . $rapport['titre'] . '" a ete valide.'
            : 'Votre rapport "' . $rapport['titre'] . '" a ete rejete : ' . $commentaire;

        createNotification($pdo, (int)$rapport['ingenieur_id'], 'Rapport ' . ($action === 'valide' ? 'valide' : 'rejete'), $notificationMessage, $action === 'valide' ? 'succes' : 'erreur', '/ingenieur/rapports.php');

        if ($action === 'valide') {
            createNotification($pdo, (int)$rapport['client_id'], 'Nouveau rapport disponible', 'Un rapport valide est disponible pour votre projet "' . $rapport['projet_nom'] . '".', 'info', '/client/rapports.php');
        }

        header('Location: rapports.php?updated=1');
        exit;
    }
}

require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('rapports', 'bi-file-earmark-text', 'Detail du rapport'); ?>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($rapport['titre']) ?></h1>
            <p class="page-subtitle"><?= htmlspecialchars($rapport['projet_nom']) ?> - <?= htmlspecialchars(trim($rapport['ingenieur_prenom'] . ' ' . $rapport['ingenieur_nom'])) ?></p>
        </div>
        <div class="page-actions">
            <a href="rapports.php" class="btn-modern btn-outline-modern"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="section-card">
        <div class="row g-4">
            <div class="col-lg-8">
                <h2 class="section-title mb-3"><i class="bi bi-journal-text"></i> Contenu</h2>
                <div class="text-muted mb-3">
                    Soumis le <?= formatDatetime($rapport['date_soumission']) ?>
                    <?php if ($rapport['tache_titre']): ?>
                        - Tache : <?= htmlspecialchars($rapport['tache_titre']) ?>
                    <?php endif; ?>
                </div>
                <div class="border rounded p-3 bg-light">
                    <?= nl2br(htmlspecialchars($rapport['contenu'])) ?>
                </div>
            </div>
            <div class="col-lg-4">
                <h2 class="section-title mb-3"><i class="bi bi-check2-circle"></i> Validation</h2>
                <p>Statut actuel : <?= getBadgeStatut($rapport['statut']) ?></p>
                <div class="mb-3">
                    <?= renderDocumentActions('rapport', (int)$rapport['id'], $rapport['fichier_joint'], 'Rapport') ?>
                </div>

                <?php if ($rapport['commentaire_admin']): ?>
                    <div class="alert alert-info"><?= nl2br(htmlspecialchars($rapport['commentaire_admin'])) ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div class="mb-3">
                        <label class="form-label-modern" for="commentaire_admin">Commentaire admin</label>
                        <textarea class="form-control-modern" id="commentaire_admin" name="commentaire_admin" rows="3"><?= htmlspecialchars($rapport['commentaire_admin'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="action" value="valide" class="btn-modern btn-success-modern">Valider</button>
                        <button type="submit" name="action" value="rejete" class="btn-modern btn-danger-modern">Rejeter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
