<?php
require_once __DIR__ . '/functions.php';

function renderMessagesPage(PDO $pdo, string $active = 'messages'): void {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $message = '';
    $messageType = 'success';

    if (!empty($_GET['sent'])) {
        $message = 'Message envoye avec succes.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $destId = (int)($_POST['destinataire_id'] ?? 0);
        $sujet = sanitize($_POST['sujet'] ?? '');
        $contenu = sanitize($_POST['contenu'] ?? '');
        $projetId = (int)($_POST['projet_id'] ?? 0) ?: null;

        if (!$destId || $destId === $userId || $contenu === '') {
            $message = 'Veuillez choisir un destinataire et saisir un message.';
            $messageType = 'danger';
        } else {
            $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ? AND statut = 'actif'");
            $check->execute([$destId]);
            if (!$check->fetchColumn()) {
                $message = 'Destinataire introuvable ou inactif.';
                $messageType = 'danger';
            } else {
                $stmt = $pdo->prepare('INSERT INTO messages (expediteur_id, destinataire_id, projet_id, sujet, contenu) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $destId, $projetId, $sujet, $contenu]);
                createNotification($pdo, $destId, 'Nouveau message', ($_SESSION['prenom'] ?? 'Un utilisateur') . ' vous a envoye un message.', 'info', userMessagesLink($pdo, $destId));
                header('Location: messages.php?with=' . $destId . '&sent=1');
                exit;
            }
        }
    }

    $destinatairesStmt = $pdo->prepare("SELECT id, nom, prenom, role, email FROM utilisateurs WHERE id <> ? AND statut = 'actif' ORDER BY role, nom, prenom");
    $destinatairesStmt->execute([$userId]);
    $destinataires = $destinatairesStmt->fetchAll();

    $projetsStmt = $pdo->prepare("
        SELECT DISTINCT p.id, p.nom
        FROM projets p
        LEFT JOIN affectations a ON a.projet_id = p.id
        WHERE p.admin_id = ? OR p.client_id = ? OR a.utilisateur_id = ?
        ORDER BY p.nom
    ");
    $projetsStmt->execute([$userId, $userId, $userId]);
    $projets = $projetsStmt->fetchAll();

    $conversationsStmt = $pdo->prepare("
        SELECT u.id, u.nom, u.prenom, u.role,
               MAX(m.date_envoi) AS dernier_message,
               SUM(CASE WHEN m.destinataire_id = ? AND m.lu = 0 THEN 1 ELSE 0 END) AS non_lus
        FROM utilisateurs u
        JOIN messages m ON (
            (m.expediteur_id = u.id AND m.destinataire_id = ?)
            OR (m.destinataire_id = u.id AND m.expediteur_id = ?)
        )
        WHERE u.id <> ?
        GROUP BY u.id, u.nom, u.prenom, u.role
        ORDER BY dernier_message DESC
    ");
    $conversationsStmt->execute([$userId, $userId, $userId, $userId]);
    $conversations = $conversationsStmt->fetchAll();

    $selectedId = (int)($_GET['with'] ?? 0);
    if (!$selectedId && $conversations) {
        $selectedId = (int)$conversations[0]['id'];
    }

    $selectedUser = null;
    $messages = [];
    if ($selectedId) {
        $selectedStmt = $pdo->prepare("SELECT id, nom, prenom, role FROM utilisateurs WHERE id = ? AND id <> ?");
        $selectedStmt->execute([$selectedId, $userId]);
        $selectedUser = $selectedStmt->fetch();
        if ($selectedUser) {
            $pdo->prepare('UPDATE messages SET lu = 1 WHERE expediteur_id = ? AND destinataire_id = ?')->execute([$selectedId, $userId]);
            $messagesStmt = $pdo->prepare("
                SELECT m.*, CONCAT(u.prenom, ' ', u.nom) AS expediteur_nom
                FROM messages m
                JOIN utilisateurs u ON u.id = m.expediteur_id
                WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
                   OR (m.expediteur_id = ? AND m.destinataire_id = ?)
                ORDER BY m.date_envoi ASC
            ");
            $messagesStmt->execute([$userId, $selectedId, $selectedId, $userId]);
            $messages = $messagesStmt->fetchAll();
        }
    }
?>
<?php renderAppLayoutStart($active, 'bi-chat', 'Messages'); ?>
<div class="page-container">
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Conversations</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMsg">
                    <i class="bi bi-envelope-plus"></i> Nouveau
                </button>
            </div>
            <div class="list-group">
                <?php if (!$conversations): ?>
                    <div class="list-group-item text-muted">Aucune conversation.</div>
                <?php endif; ?>
                <?php foreach ($conversations as $conversation): ?>
                    <a class="list-group-item list-group-item-action <?= (int)$conversation['id'] === $selectedId ? 'active' : '' ?>" href="messages.php?with=<?= (int)$conversation['id'] ?>">
                        <div class="d-flex justify-content-between">
                            <span><?= htmlspecialchars($conversation['prenom'] . ' ' . $conversation['nom']) ?></span>
                            <?php if ((int)$conversation['non_lus'] > 0): ?>
                                <span class="badge bg-danger"><?= (int)$conversation['non_lus'] ?></span>
                            <?php endif; ?>
                        </div>
                        <small><?= htmlspecialchars(ucfirst($conversation['role'])) ?> - <?= htmlspecialchars(formatDatetime($conversation['dernier_message'])) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <?= $selectedUser ? htmlspecialchars($selectedUser['prenom'] . ' ' . $selectedUser['nom'] . ' - ' . ucfirst($selectedUser['role'])) : 'Messages' ?>
                </div>
                <div class="card-body" style="height:360px; overflow-y:auto;">
                    <?php if (!$selectedUser): ?>
                        <div class="text-muted text-center py-5">Selectionnez une conversation ou envoyez un nouveau message.</div>
                    <?php endif; ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php $mine = (int)$msg['expediteur_id'] === $userId; ?>
                        <div class="d-flex mb-3 <?= $mine ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="p-3 rounded <?= $mine ? 'bg-primary text-white' : 'bg-light' ?>" style="max-width:75%;">
                                <?php if (!$mine): ?><div class="fw-semibold"><?= htmlspecialchars($msg['expediteur_nom']) ?></div><?php endif; ?>
                                <?php if ($msg['sujet']): ?><div class="small fw-semibold"><?= htmlspecialchars($msg['sujet']) ?></div><?php endif; ?>
                                <div><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                                <div class="small <?= $mine ? 'text-white-50' : 'text-muted' ?> mt-1"><?= htmlspecialchars(formatDatetime($msg['date_envoi'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <form method="post" class="row g-2">
                        <input type="hidden" name="destinataire_id" value="<?= (int)$selectedId ?>">
                        <div class="col-md-4"><input type="text" class="form-control" name="sujet" placeholder="Sujet"></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="contenu" placeholder="Votre message..." required <?= $selectedUser ? '' : 'disabled' ?>></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-success" <?= $selectedUser ? '' : 'disabled' ?>>Envoyer</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMsg" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post">
                <div class="modal-header"><h5 class="modal-title">Nouveau message</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <select class="form-select" name="destinataire_id" required>
                            <option value="">Destinataire</option>
                            <?php foreach ($destinataires as $destinataire): ?>
                                <option value="<?= (int)$destinataire['id'] ?>"><?= htmlspecialchars($destinataire['prenom'] . ' ' . $destinataire['nom'] . ' - ' . ucfirst($destinataire['role'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select class="form-select" name="projet_id">
                            <option value="">Aucun projet lie</option>
                            <?php foreach ($projets as $projet): ?>
                                <option value="<?= (int)$projet['id'] ?>"><?= htmlspecialchars($projet['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><input type="text" class="form-control" name="sujet" placeholder="Sujet"></div>
                    <div class="mb-2"><textarea class="form-control" rows="4" name="contenu" placeholder="Votre message..." required></textarea></div>
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
<?php
}
