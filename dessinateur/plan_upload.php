<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $titre = sanitize($_POST['titre'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $typePlan = $_POST['type_plan'] ?? 'autre';
    $statut = $_POST['statut'] ?? 'brouillon';
    $partageClient = 0;

    if (!$projetId || $titre === '' || empty($_FILES['fichier']['name'])) {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $messageType = 'danger';
    } elseif (!userBelongsToProject($pdo, $user_id, $projetId)) {
        $message = 'Vous ne pouvez pas deposer un plan sur ce projet.';
        $messageType = 'danger';
    } else {
        $uploaded = uploadFichier($_FILES['fichier'], __DIR__ . '/../uploads/plans/', ['pdf', 'dwg', 'dxf', 'png', 'jpg', 'jpeg'], 15 * 1024 * 1024);
        if ($uploaded === false) {
            $message = 'Le fichier du plan est invalide ou trop volumineux.';
            $messageType = 'danger';
        } else {
            $fichier = 'uploads/plans/' . $uploaded;
            $stmt = $pdo->prepare('INSERT INTO plans (projet_id, dessinateur_id, titre, description, type_plan, fichier, statut, partage_client) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$projetId, $user_id, $titre, $description, $typePlan, $fichier, $statut, $partageClient]);
            if ($statut === 'soumis') {
                $responsables = $pdo->prepare("
                    SELECT admin_id AS utilisateur_id FROM projets WHERE id = ?
                    UNION
                    SELECT utilisateur_id FROM affectations WHERE projet_id = ? AND role_projet = 'ingenieur'
                ");
                $responsables->execute([$projetId, $projetId]);
                foreach ($responsables->fetchAll() as $responsable) {
                    createNotification($pdo, (int)$responsable['utilisateur_id'], 'Plan a valider', $_SESSION['prenom'] . ' a soumis le plan "' . $titre . '" pour validation.', 'info', '/ingenieur/documents.php');
                }
            }
            header('Location: plans.php?created=1');
            exit;
        }
    }
}

$projets = $pdo->prepare("SELECT DISTINCT p.id, p.nom FROM projets p JOIN affectations a ON a.projet_id = p.id WHERE a.utilisateur_id = ? ORDER BY p.nom");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('plans', 'bi-file-earmark-arrow-up', 'Deposer un plan'); ?>
<div class="page-container">
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <h2 class="fw-bold mb-4">Déposer un Plan</h2>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required>
                <option value="">Selectionner un projet</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>"><?= htmlspecialchars($projet['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre du plan *</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="2"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type de plan *</label>
            <select class="form-select" name="type_plan" required>
                <option value="architectural">Architectural</option>
                <option value="structural">Structural</option>
                <option value="electrique">Électrique</option>
                <option value="plomberie">Plomberie</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Fichier *</label>
            <input type="file" class="form-control" name="fichier" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Statut initial</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="brouillon" checked>
                <label class="form-check-label">Brouillon</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="soumis">
                <label class="form-check-label">Soumettre directement</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info mb-0 mt-4">
                Le client verra ce plan uniquement apres validation et partage par le chef projet ou l'ingenieur.
            </div>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2">📤 Déposer le Plan</button>
            <a href="plans.php" class="btn btn-danger">❌ Annuler</a>
        </div>
    </form>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>

