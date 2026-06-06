<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
$messageType = '';
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = sanitize($_POST['titre'] ?? '');
    $projetId = (int)($_POST['projet_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');

    $projetStmt = $pdo->prepare('SELECT id, nom, admin_id FROM projets WHERE id = ? AND client_id = ?');
    $projetStmt->execute([$projetId, $user_id]);
    $projet = $projetStmt->fetch();

    if ($titre === '' || !$projetId || $description === '') {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $messageType = 'danger';
    } elseif (!$projet) {
        $message = 'Projet introuvable ou acces refuse.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('INSERT INTO demandes (client_id, projet_id, titre, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, $projetId, $titre, $description]);
        createNotification($pdo, (int)$projet['admin_id'], 'Nouvelle demande client', ($_SESSION['prenom'] ?? 'Un client') . ' a envoye une demande sur le projet "' . $projet['nom'] . '".', 'info', '/admin/projet_detail.php?id=' . $projetId);
        $message = 'Votre demande a ete envoyee avec succes.';
        $messageType = 'success';
        $old = [];
    }
}

$projets = $pdo->prepare("SELECT id, nom FROM projets WHERE client_id = ? ORDER BY nom");
$projets->execute([$user_id]);
$projets = $projets->fetchAll();

$demandesStmt = $pdo->prepare("
    SELECT d.*, p.nom AS projet_nom
    FROM demandes d
    JOIN projets p ON p.id = d.projet_id
    WHERE d.client_id = ?
    ORDER BY d.date_demande DESC
");
$demandesStmt->execute([$user_id]);
$demandes = $demandesStmt->fetchAll();

require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('demandes', 'bi-chat', 'Demandes'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-chat"></i> Mes demandes</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="section-card mb-4">
        <div class="section-header"><div class="section-title"><i class="bi bi-send"></i> Nouvelle demande</div></div>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="filter-label">Titre de la demande *</label>
                <input type="text" class="form-control-modern" name="titre" value="<?= htmlspecialchars($old['titre'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="filter-label">Projet concerne *</label>
                <select class="filter-select" name="projet_id" required>
                    <option value="">Selectionner un projet</option>
                    <?php foreach ($projets as $projet): ?>
                        <option value="<?= (int)$projet['id'] ?>" <?= (string)($old['projet_id'] ?? '') === (string)$projet['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($projet['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12">
                <label class="filter-label">Description detaillee *</label>
                <textarea class="form-control-modern" name="description" rows="3" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn-modern btn-success-modern"><i class="bi bi-send"></i> Envoyer la demande</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead><tr><th>Titre</th><th>Projet</th><th>Date</th><th>Statut</th><th>Reponse</th></tr></thead>
                <tbody>
                    <?php if (!$demandes): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune demande envoyee.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td><?= htmlspecialchars($demande['titre']) ?></td>
                            <td><?= htmlspecialchars($demande['projet_nom']) ?></td>
                            <td><?= htmlspecialchars(formatDatetime($demande['date_demande'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $demande['statut']))) ?></td>
                            <td><?= $demande['reponse'] ? nl2br(htmlspecialchars($demande['reponse'])) : '<span class="text-muted">En attente</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
