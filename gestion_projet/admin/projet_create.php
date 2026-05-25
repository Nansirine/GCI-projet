<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <h2 class="fw-bold mb-4">Créer un Nouveau Projet</h2>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nom du projet *</label>
            <input type="text" class="form-control" name="nom" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Client *</label>
            <select class="form-select" name="client_id" required><!-- Clients dynamiques --></select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Description *</label>
            <textarea class="form-control" name="description" rows="2" required></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Localisation *</label>
            <input type="text" class="form-control" name="localisation" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Budget (FCFA) *</label>
            <input type="number" class="form-control" name="budget" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date début *</label>
            <input type="date" class="form-control" name="date_debut" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date fin prévue *</label>
            <input type="date" class="form-control" name="date_fin_prevue" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ingénieurs affectés</label>
            <select class="form-select" name="ingenieurs[]" multiple><!-- Ingénieurs dynamiques --></select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Dessinateurs affectés</label>
            <select class="form-select" name="dessinateurs[]" multiple><!-- Dessinateurs dynamiques --></select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Jalons</label>
            <div id="jalons-list"></div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addJalon">+ Ajouter Jalon</button>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success me-2">✅ Créer le Projet</button>
            <a href="projets.php" class="btn btn-secondary">❌ Annuler</a>
        </div>
    </form>
</div>
<script>
// JS pour ajouter dynamiquement des jalons
let jalonIdx = 0;
document.getElementById('addJalon').onclick = function() {
    const list = document.getElementById('jalons-list');
    const div = document.createElement('div');
    div.className = 'row g-2 align-items-end mb-2';
    div.innerHTML = `<div class="col-md-6"><input type="text" name="jalons[${jalonIdx}][titre]" class="form-control" placeholder="Titre du jalon" required></div><div class="col-md-4"><input type="date" name="jalons[${jalonIdx}][date_prevue]" class="form-control" required></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()">Supprimer</button></div>`;
    list.appendChild(div);
    jalonIdx++;
};
</script>
<?php require_once '../includes/footer.php'; ?>
