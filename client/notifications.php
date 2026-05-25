<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
require_once '_client_layout.php';
?>
<?php renderClientLayoutStart('notifications', 'bi-bell', 'Notifications'); ?>
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-bell"></i> Mes notifications</h1>
    </div>

    <div class="filters-section">
        <form class="filters-row">
            <div class="filter-group">
                <label class="filter-label" for="filtre">Filtre</label>
                <select class="filter-select" id="filtre" name="filtre">
                    <option value="">Toutes</option>
                    <option value="non_lues">Non lues</option>
                    <option value="lues">Lues</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn-filter"><i class="bi bi-funnel"></i> Filtrer</button>
                <button class="btn-modern btn-success-modern" type="button"><i class="bi bi-check2-circle"></i> Marquer tout comme lu</button>
            </div>
        </form>
    </div>

    <div class="section-card">
        <ul class="list-group" id="notifs-list"></ul>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
