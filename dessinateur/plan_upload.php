<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $titre = sanitize($_POST['titre'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $typePlan = $_POST['type_plan'] ?? 'autre';
    $statut = $_POST['statut'] ?? 'soumis';
    $partageClient = 0;

    if (!$projetId || $titre === '' || empty($_FILES['fichier']['name'])) {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $messageType = 'danger';
    } elseif (!userBelongsToProject($pdo, $user_id, $projetId)) {
        $message = 'Vous ne pouvez pas deposer un plan sur ce projet.';
        $messageType = 'danger';
    } else {
        $uploaded = uploadFichier($_FILES['fichier'], __DIR__ . '/../uploads/plans/', civilEngineeringFileExtensions(), 50 * 1024 * 1024);
        if ($uploaded === false) {
            $message = 'Le fichier du plan est invalide ou trop volumineux.';
            $messageType = 'danger';
        } else {
            $fichier = 'uploads/plans/' . $uploaded;
            $stmt = $pdo->prepare('INSERT INTO plans (projet_id, dessinateur_id, titre, description, type_plan, fichier, statut, partage_client) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$projetId, $user_id, $titre, $description, $typePlan, $fichier, $statut, $partageClient]);

            if ($statut === 'soumis') {
                $responsables = $pdo->prepare("
                    SELECT p.admin_id AS utilisateur_id, u.role
                    FROM projets p
                    JOIN utilisateurs u ON u.id = p.admin_id
                    WHERE p.id = ?
                    UNION
                    SELECT a.utilisateur_id, u.role
                    FROM affectations a
                    JOIN utilisateurs u ON u.id = a.utilisateur_id
                    WHERE a.projet_id = ? AND (a.role_projet = 'ingenieur' OR u.role = 'ingenieur')
                    UNION
                    SELECT t.assigne_a AS utilisateur_id, u.role
                    FROM taches t
                    JOIN utilisateurs u ON u.id = t.assigne_a
                    WHERE t.projet_id = ? AND u.role = 'ingenieur'
                ");
                $responsables->execute([$projetId, $projetId, $projetId]);
                foreach ($responsables->fetchAll() as $responsable) {
                    $link = $responsable['role'] === 'admin' ? '/admin/projet_detail.php?id=' . $projetId : '/ingenieur/documents.php';
                    createNotification($pdo, (int)$responsable['utilisateur_id'], 'Plan a valider', ($_SESSION['prenom'] ?? 'Un dessinateur') . ' a soumis le plan "' . $titre . '" pour validation.', 'info', $link);
                }
            }

            header('Location: plans.php?created=1');
            exit;
        }
    }
}

$projets = $pdo->prepare("
    SELECT DISTINCT p.id, p.nom
    FROM projets p
    JOIN affectations a ON a.projet_id = p.id
    WHERE a.utilisateur_id = ?
    ORDER BY p.nom
");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();
$typesPlans = ['architectural' => 'Architectural', 'structural' => 'Structural', 'electrique' => 'Electrique', 'plomberie' => 'Plomberie', 'autre' => 'Autre'];
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('plans', 'bi-file-earmark-arrow-up', 'Deposer un plan'); ?>
<div class="page-container">
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <h2 class="fw-bold mb-4">Deposer un plan</h2>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Projet *</label>
            <select class="form-select" name="projet_id" required>
                <option value="">Selectionner un projet</option>
                <?php foreach ($projets as $projet): ?>
                    <option value="<?= (int)$projet['id'] ?>" <?= (string)($old['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($projet['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre du plan *</label>
            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($old['titre'] ?? '') ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type de plan *</label>
            <select class="form-select" name="type_plan" required>
                <?php foreach ($typesPlans as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($old['type_plan'] ?? 'autre') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Fichier *</label>
            <input type="file" class="form-control" name="fichier" accept="<?= htmlspecialchars(civilEngineeringAcceptAttribute()) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Statut initial</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="brouillon" <?= ($old['statut'] ?? '') === 'brouillon' ? 'checked' : '' ?>>
                <label class="form-check-label">Brouillon</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="soumis" <?= ($old['statut'] ?? 'soumis') === 'soumis' ? 'checked' : '' ?>>
                <label class="form-check-label">Soumettre directement</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info mb-0 mt-4">
                Le client verra ce plan uniquement apres validation et partage par le chef projet ou l'ingenieur.
            </div>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2"><i class="bi bi-upload"></i> Deposer le plan</button>
            <a href="plans.php" class="btn btn-danger">Annuler</a>
        </div>
    </form>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
