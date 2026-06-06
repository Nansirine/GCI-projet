<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $tacheId = (int)($_POST['tache_id'] ?? 0) ?: null;
    $titre = sanitize($_POST['titre'] ?? '');
    $contenu = sanitize($_POST['contenu'] ?? '');
    $fichierJoint = null;

    if (!$projetId || $titre === '' || $contenu === '') {
        $message = 'Veuillez renseigner le projet, le titre et le contenu.';
        $messageType = 'danger';
    } elseif (!userBelongsToProject($pdo, $user_id, $projetId)) {
        $message = 'Vous ne pouvez pas soumettre un rapport sur ce projet.';
        $messageType = 'danger';
    } else {
        if (!empty($_FILES['fichier_joint']['name'])) {
            $uploaded = uploadFichier($_FILES['fichier_joint'], __DIR__ . '/../uploads/rapports/', civilEngineeringFileExtensions(), 50 * 1024 * 1024);
            if ($uploaded === false) {
                $message = 'Le fichier joint est invalide ou trop volumineux.';
                $messageType = 'danger';
            } else {
                $fichierJoint = 'uploads/rapports/' . $uploaded;
            }
        }

        if (!$message) {
            $stmt = $pdo->prepare('INSERT INTO rapports (projet_id, tache_id, ingenieur_id, titre, contenu, fichier_joint) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$projetId, $tacheId, $user_id, $titre, $contenu, $fichierJoint]);

            $responsables = $pdo->prepare("
                SELECT p.admin_id AS utilisateur_id, u.role
                FROM projets p
                JOIN utilisateurs u ON u.id = p.admin_id
                WHERE p.id = ?
                UNION
                SELECT a.utilisateur_id, u.role
                FROM affectations a
                JOIN utilisateurs u ON u.id = a.utilisateur_id
                WHERE a.projet_id = ? AND u.role = 'ingenieur' AND a.utilisateur_id <> ?
            ");
            $responsables->execute([$projetId, $projetId, $user_id]);
            foreach ($responsables->fetchAll() as $responsable) {
                $link = $responsable['role'] === 'admin' ? '/admin/rapports.php' : '/ingenieur/documents.php';
                createNotification($pdo, (int)$responsable['utilisateur_id'], 'Nouveau rapport soumis', ($_SESSION['prenom'] ?? 'Un ingenieur') . ' a soumis le rapport "' . $titre . '".', 'info', $link);
            }

            header('Location: rapports.php?created=1');
            exit;
        }
    }
}

$projets = $pdo->prepare("
    SELECT DISTINCT p.id, p.nom
    FROM projets p
    LEFT JOIN affectations a ON a.projet_id = p.id
    WHERE a.utilisateur_id = ? OR p.admin_id = ?
    ORDER BY p.nom
");
$projets->execute([$user_id, $user_id]);
$projets = $projets->fetchAll();
$stmtTaches = $pdo->prepare("SELECT id, titre, projet_id FROM taches WHERE assigne_a = ? ORDER BY date_echeance, titre");
$stmtTaches->execute([$user_id]);
$taches = $stmtTaches->fetchAll();
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('rapports', 'bi-file-earmark-text', 'Soumettre un rapport'); ?>
<div class="page-container">
    <h2 class="fw-bold mb-4">Soumettre un rapport</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" id="projet_id" required>
                <option value="">Selectionner un projet</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= (string)($old['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($projet['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Tache liee</label>
            <select class="form-select" name="tache_id" id="tache_id">
                <option value="">Aucune tache liee</option>
                <?php foreach ($taches as $tache): ?>
                    <option value="<?= (int)$tache['id'] ?>" data-projet-id="<?= (int)$tache['projet_id'] ?>" <?= (string)($old['tache_id'] ?? '') === (string)$tache['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tache['titre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Titre du rapport *</label>
            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($old['titre'] ?? '') ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Contenu detaille *</label>
            <textarea class="form-control" name="contenu" rows="5" required><?= htmlspecialchars($old['contenu'] ?? '') ?></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Fichier joint</label>
            <input type="file" class="form-control" name="fichier_joint" accept="<?= htmlspecialchars(civilEngineeringAcceptAttribute()) ?>">
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2"><i class="bi bi-upload"></i> Soumettre le rapport</button>
            <a href="rapports.php" class="btn btn-danger">Annuler</a>
        </div>
    </form>
</div>
<script>
const projectSelect = document.getElementById('projet_id');
const taskSelect = document.getElementById('tache_id');
function filterTasks() {
    const projectId = projectSelect?.value || '';
    taskSelect?.querySelectorAll('option[data-projet-id]').forEach(function(option) {
        option.hidden = projectId && option.dataset.projetId !== projectId;
    });
    if (taskSelect?.selectedOptions[0]?.hidden) {
        taskSelect.value = '';
    }
}
projectSelect?.addEventListener('change', filterTasks);
filterTasks();
</script>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
