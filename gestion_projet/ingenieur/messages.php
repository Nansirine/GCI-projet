<?php
require_once '../includes/auth.php';
checkRole(['ingenieur']);
$user_id = $_SESSION['user_id'];
require_once '../includes/header.php';
?>
<div class="container-fluid mt-4">
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
            <form class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Nouveau Message</h5></div>
                <div class="modal-body">
                    <div class="mb-2"><select class="form-select"><option>Destinataire</option></select></div>
                    <div class="mb-2"><input type="text" class="form-control" placeholder="Sujet"></div>
                    <div class="mb-2"><textarea class="form-control" rows="3" placeholder="Votre message..."></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Envoyer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
