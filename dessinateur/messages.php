<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
$user_id = $_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destId = (int)($_POST['destinataire_id'] ?? 0);
    $sujet = sanitize($_POST['sujet'] ?? '');
    $contenu = sanitize($_POST['contenu'] ?? '');
    if ($destId && $destId !== (int)$user_id && $contenu !== '') {
        $stmt = $pdo->prepare('INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, $destId, $sujet, $contenu]);
        createNotification($pdo, $destId, 'Nouveau message', $_SESSION['prenom'] . ' vous a envoye un message.', 'info', '/dessinateur/messages.php');
        header('Location: messages.php?sent=1');
        exit;
    }
}

$destinataires = $pdo->prepare("SELECT id, nom, prenom, role, email FROM utilisateurs WHERE id <> ? AND statut = 'actif' ORDER BY role, nom, prenom");
$destinataires->execute([$user_id]);
$destinataires = $destinataires->fetchAll();
require_once '../includes/header.php';
require_once '../includes/layout.php';
?>
<?php renderAppLayoutStart('messages', 'bi-chat', 'Messages'); ?>
<div class="page-container">
    <div class="row">
        <div class="col-md-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5>Conversations</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMsg">✉ Nouveau Message</button>
            </div>
            <ul class="list-group" id="conversations-list"><!-- Conversations dynamiques --></ul>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Messages</div>
                <div class="card-body" id="messages-list" style="height:350px; overflow-y:auto;"><!-- Messages dynamiques --></div>
                <div class="card-footer">
                    <form class="d-flex gap-2">
                        <input type="text" class="form-control" placeholder="Votre message...">
                        <button class="btn btn-success">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Nouveau Message -->
    <div class="modal fade" id="modalMsg" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post">
                <div class="modal-header"><h5 class="modal-title">Nouveau Message</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <select class="form-select" name="destinataire_id" required>
                            <option value="">Destinataire</option>
                            <?php foreach ($destinataires as $destinataire): ?>
                                <option value="<?= (int)$destinataire['id'] ?>"><?= htmlspecialchars($destinataire['prenom'] . ' ' . $destinataire['nom'] . ' - ' . ucfirst($destinataire['role'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><input type="text" class="form-control" name="sujet" placeholder="Sujet"></div>
                    <div class="mb-2"><textarea class="form-control" rows="3" name="contenu" placeholder="Votre message..." required></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Envoyer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
