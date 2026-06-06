<?php
<?php
// Page désactivée côté client : accès supprimé
header('Location: dashboard.php');
exit;
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Lecture du rapport</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="rapport-content"></div>
                <div class="modal-footer"><button class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Fermer</button></div>
            </div>
        </div>
    </div>
<?php renderClientLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
