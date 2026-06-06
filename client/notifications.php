<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = (int)$_SESSION['user_id'];
require_once '../config/database.php';
require_once '../includes/functions.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['notification_action'] ?? '';
    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare('UPDATE notifications SET lu = 1 WHERE utilisateur_id = ?');
        $stmt->execute([$user_id]);
        $message = 'Toutes les notifications ont ete marquees comme lues.';
    } elseif ($action === 'mark_read') {
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE notifications SET lu = 1 WHERE id = ? AND utilisateur_id = ?');
        $stmt->execute([$notificationId, $user_id]);
        $message = 'Notification marquee comme lue.';
    }
}

$filtre = $_GET['filtre'] ?? '';
$where = ['utilisateur_id = :user_id'];
$params = [':user_id' => $user_id];
if ($filtre === 'non_lues') {
    $where[] = 'lu = 0';
} elseif ($filtre === 'lues') {
    $where[] = 'lu = 1';
}

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE ' . implode(' AND ', $where) . ' ORDER BY date_creation DESC LIMIT 100');
$stmt->execute($params);
$notifications = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('notifications', 'bi-bell', 'Notifications'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-bell"></i> Mes notifications</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="filters-section">
        <form class="filters-row">
            <div class="filter-group">
                <label class="filter-label" for="filtre">Filtre</label>
                <select class="filter-select" id="filtre" name="filtre">
                    <option value="">Toutes</option>
                    <option value="non_lues" <?= $filtre === 'non_lues' ? 'selected' : '' ?>>Non lues</option>
                    <option value="lues" <?= $filtre === 'lues' ? 'selected' : '' ?>>Lues</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn-filter"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
        </form>
        <form method="post" class="mt-2">
            <button class="btn-modern btn-success-modern" name="notification_action" value="mark_all_read">
                <i class="bi bi-check2-circle"></i> Marquer tout comme lu
            </button>
        </form>
    </div>

    <div class="section-card">
        <div class="list-group">
            <?php if (!$notifications): ?>
                <div class="list-group-item text-center text-muted py-4">Aucune notification.</div>
            <?php endif; ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="list-group-item <?= (int)$notification['lu'] === 0 ? 'list-group-item-primary' : '' ?>">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">
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
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
