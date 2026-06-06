<?php
require_once __DIR__ . '/functions.php';

function renderNotificationsPage(PDO $pdo, string $active = 'notifications'): void {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $message = '';
    $messageType = 'success';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['notification_action'] ?? '';
        if ($action === 'mark_all_read') {
            $stmt = $pdo->prepare('UPDATE notifications SET lu = 1 WHERE utilisateur_id = ?');
            $stmt->execute([$userId]);
            $message = 'Toutes les notifications ont ete marquees comme lues.';
        } elseif ($action === 'mark_read') {
            $notificationId = (int)($_POST['notification_id'] ?? 0);
            $stmt = $pdo->prepare('UPDATE notifications SET lu = 1 WHERE id = ? AND utilisateur_id = ?');
            $stmt->execute([$notificationId, $userId]);
            $message = 'Notification marquee comme lue.';
        }
    }

    $filtre = $_GET['filtre'] ?? '';
    $where = ['utilisateur_id = :user_id'];
    $params = [':user_id' => $userId];
    if ($filtre === 'non_lues') {
        $where[] = 'lu = 0';
    } elseif ($filtre === 'lues') {
        $where[] = 'lu = 1';
    }

    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE ' . implode(' AND ', $where) . ' ORDER BY date_creation DESC LIMIT 100');
    $stmt->execute($params);
    $notifications = $stmt->fetchAll();
?>
<?php renderAppLayoutStart($active, 'bi-bell', 'Notifications'); ?>
<div class="page-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold mb-0">Notifications</h2>
        <form method="post">
            <button class="btn btn-outline-primary btn-sm" name="notification_action" value="mark_all_read">
                <i class="bi bi-check2-circle"></i> Tout marquer comme lu
            </button>
        </form>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select class="form-select" name="filtre">
                <option value="">Toutes</option>
                <option value="non_lues" <?= $filtre === 'non_lues' ? 'selected' : '' ?>>Non lues</option>
                <option value="lues" <?= $filtre === 'lues' ? 'selected' : '' ?>>Lues</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filtrer</button>
        </div>
    </form>

    <div class="list-group">
        <?php if (!$notifications): ?>
            <div class="list-group-item text-center text-muted py-4">Aucune notification.</div>
        <?php endif; ?>
        <?php foreach ($notifications as $notification): ?>
            <?php
                $classes = (int)$notification['lu'] === 0 ? 'list-group-item-primary' : '';
                $icon = match ($notification['type']) {
                    'succes' => 'bi-check-circle',
                    'avertissement' => 'bi-exclamation-triangle',
                    'erreur' => 'bi-x-circle',
                    default => 'bi-info-circle',
                };
            ?>
            <div class="list-group-item <?= $classes ?>">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-semibold">
                            <i class="bi <?= $icon ?>"></i>
                            <?= htmlspecialchars($notification['titre']) ?>
                            <?php if ((int)$notification['lu'] === 0): ?>
                                <span class="badge bg-primary ms-1">Nouveau</span>
                            <?php endif; ?>
                        </div>
                        <div><?= nl2br(htmlspecialchars($notification['message'])) ?></div>
                        <small class="text-muted"><?= htmlspecialchars(formatDatetime($notification['date_creation'])) ?></small>
                    </div>
                    <div class="d-flex flex-column gap-2 align-items-end">
                        <?php if (!empty($notification['lien'])): ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($notification['lien']) ?>">Ouvrir</a>
                        <?php endif; ?>
                        <?php if ((int)$notification['lu'] === 0): ?>
                            <form method="post">
                                <input type="hidden" name="notification_id" value="<?= (int)$notification['id'] ?>">
                                <button class="btn btn-sm btn-outline-secondary" name="notification_action" value="mark_read">Lu</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php
}
