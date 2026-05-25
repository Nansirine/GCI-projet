<?php
require_once '../includes/auth.php';
checkRole(['client']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">Mes Notifications</h2>
    <form class="mb-3">
        <select class="form-select w-auto d-inline" name="filtre" style="min-width:180px;">
            <option value="">Toutes</option>
            <option value="non_lues">Non lues</option>
            <option value="lues">Lues</option>
        </select>
        <button class="btn btn-primary ms-2">Filtrer</button>
        <button class="btn btn-success ms-2" type="button">✓ Marquer tout comme lu</button>
    </form>
    <ul class="list-group" id="notifs-list">
        <!-- Notifications dynamiques -->
    </ul>
</div>
<?php require_once '../includes/footer.php'; ?>
