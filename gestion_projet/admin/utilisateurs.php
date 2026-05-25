<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
// Création d'un nouvel utilisateur
$erreur_creation = '';
if (isset($_POST['new_nom'], $_POST['new_prenom'], $_POST['new_email'], $_POST['new_role'], $_POST['new_password'])) {
    $nom = trim($_POST['new_nom']);
    $prenom = trim($_POST['new_prenom']);
    $email = trim($_POST['new_email']);
    $role = $_POST['new_role'];
    $statut = 'actif';
    $telephone = trim($_POST['new_telephone'] ?? '');
    $password = $_POST['new_password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut, telephone) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prenom, $email, $hash, $role, $statut, $telephone]);
        header('Location: utilisateurs.php');
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $erreur_creation = "Cet email existe déjà.";
        } else {
            $erreur_creation = "Erreur lors de la création : " . $e->getMessage();
        }
    }
}
// Suppression d'un utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Ne pas supprimer son propre compte
    if ($id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: utilisateurs.php');
        exit();
    }
}
// Modification d'un utilisateur (AJAX)
if (isset($_POST['edit_id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $id = (int)$_POST['edit_id'];
    $nom = trim($_POST['edit_nom']);
    $prenom = trim($_POST['edit_prenom']);
    $email = trim($_POST['edit_email']);
    $role = $_POST['edit_role'];
    $statut = $_POST['edit_statut'];
    $telephone = trim($_POST['edit_telephone']);
    $msg = '';
    try {
        $sql = "UPDATE utilisateurs SET nom=?, prenom=?, email=?, role=?, statut=?, telephone=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prenom, $email, $role, $statut, $telephone, $id]);
        $msg = 'Modification enregistrée avec succès.';
        echo json_encode(['success'=>true, 'message'=>$msg]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $msg = "Cet email existe déjà.";
        } else {
            $msg = "Erreur lors de la modification : " . $e->getMessage();
        }
        echo json_encode(['success'=>false, 'message'=>$msg]);
    }
    exit();
}
require_once '../includes/header.php';
$stmt = $pdo->query('SELECT * FROM utilisateurs ORDER BY nom, prenom');
$utilisateurs = $stmt->fetchAll();
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Gestion des Utilisateurs</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUser">+ Nouvel Utilisateur</button>
    </div>
    <div class="mb-3">
        <select class="form-select w-auto d-inline" style="min-width:180px;">
            <option value="">Tous les rôles</option>
            <option value="admin">Admin</option>
            <option value="ingenieur">Ingénieur</option>
            <option value="dessinateur">Dessinateur</option>
            <option value="client">Client</option>
        </select>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th><th>Actions</th></tr>
            </thead>
            <tbody id="users-list">
<?php foreach ($utilisateurs as $user): ?>
<tr>
    <td><?= htmlspecialchars($user['nom'].' '.$user['prenom']) ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td><?= htmlspecialchars($user['role']) ?></td>
    <td><?= htmlspecialchars($user['statut']) ?></td>
    <td><?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : '-' ?></td>
    <td>
        <button type="button" class="btn btn-sm btn-primary me-1 btn-edit-user" title="Modifier"
            data-bs-toggle="modal" data-bs-target="#editUserModal"
            data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>'>
            <i class="bi bi-pencil"></i> Modifier
        </button>
        <a href="utilisateurs.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer cet utilisateur ?');"><i class="bi bi-trash"></i> Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>


<!-- Modal édition utilisateur (unique, dynamique, en dehors de la boucle) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="editUserForm" method="post">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="modal-header"><h5 class="modal-title">Modifier Utilisateur</h5></div>
            <div class="modal-body">
                <div id="editUserMsg"></div>
                <div class="mb-2"><input type="text" class="form-control" name="edit_nom" id="edit_nom" placeholder="Nom" required></div>
                <div class="mb-2"><input type="text" class="form-control" name="edit_prenom" id="edit_prenom" placeholder="Prénom" required></div>
                <div class="mb-2"><input type="email" class="form-control" name="edit_email" id="edit_email" placeholder="Email" required></div>
                <div class="mb-2"><input type="text" class="form-control" name="edit_telephone" id="edit_telephone" placeholder="Téléphone"></div>
                <div class="mb-2">
                    <select class="form-select" name="edit_role" id="edit_role" required>
                        <option value="admin">Admin</option>
                        <option value="ingenieur">Ingénieur</option>
                        <option value="dessinateur">Dessinateur</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div class="mb-2">
                    <select class="form-select" name="edit_statut" id="edit_statut" required>
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>
            </tbody>
        </table>
    </div>
    <!-- Modal Création Utilisateur -->
    <div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post">
                <div class="modal-header"><h5 class="modal-title">Nouvel Utilisateur</h5></div>
                <div class="modal-body">
                    <?php if (!empty($erreur_creation)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erreur_creation) ?></div>
                    <?php endif; ?>
                    <div class="mb-2"><input type="text" class="form-control" name="new_nom" placeholder="Nom" required></div>
                    <div class="mb-2"><input type="text" class="form-control" name="new_prenom" placeholder="Prénom" required></div>
                    <div class="mb-2"><input type="email" class="form-control" name="new_email" placeholder="Email" required></div>
                    <div class="mb-2"><input type="text" class="form-control" name="new_telephone" placeholder="Téléphone"></div>
                    <div class="mb-2">
                        <select class="form-select" name="new_role" required>
                            <option value="">Rôle</option>
                            <option value="admin">Admin</option>
                            <option value="ingenieur">Ingénieur</option>
                            <option value="dessinateur">Dessinateur</option>
                            <option value="client">Client</option>
                        </select>
                    </div>
                    <div class="mb-2"><input type="password" class="form-control" name="new_password" placeholder="Mot de passe temporaire" required></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Créer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
<script>
// Remplir dynamiquement la modale de modification
document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const user = JSON.parse(this.getAttribute('data-user'));
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_nom').value = user.nom;
        document.getElementById('edit_prenom').value = user.prenom;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_telephone').value = user.telephone || '';
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_statut').value = user.statut;
        document.getElementById('editUserMsg').innerHTML = '';
        // Rendre tous les champs éditables
        document.getElementById('edit_nom').readOnly = false;
        document.getElementById('edit_prenom').readOnly = false;
        document.getElementById('edit_email').readOnly = false;
        document.getElementById('edit_telephone').readOnly = false;
        document.getElementById('edit_role').disabled = false;
        document.getElementById('edit_statut').disabled = false;
    });
});

// Soumission AJAX du formulaire de modification
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    fetch('utilisateurs.php', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editUserMsg').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            setTimeout(() => { location.reload(); }, 900);
        } else {
            document.getElementById('editUserMsg').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(() => {
        document.getElementById('editUserMsg').innerHTML = '<div class="alert alert-danger">Erreur lors de la modification.</div>';
    });
});
</script>
<script>
// Remplir dynamiquement la modale de modification
document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const user = JSON.parse(this.getAttribute('data-user'));
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_nom').value = user.nom;
        document.getElementById('edit_prenom').value = user.prenom;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_telephone').value = user.telephone || '';
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_statut').value = user.statut;
        document.getElementById('editUserMsg').innerHTML = '';
    });
});

// Soumission AJAX du formulaire de modification
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    fetch('utilisateurs.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(html => {
        // Recharge le tableau sans recharger toute la page
        location.reload();
    })
    .catch(() => {
        document.getElementById('editUserMsg').innerHTML = '<div class="alert alert-danger">Erreur lors de la modification.</div>';
    });
});
</script>
