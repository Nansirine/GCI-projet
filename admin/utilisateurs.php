<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/mailer.php';
require_once '../includes/functions.php';
ensureUserAccountColumns($pdo);

$roles = ['admin', 'ingenieur', 'dessinateur', 'client'];
$roleLabels = [
    'admin' => 'Admin',
    'ingenieur' => 'Ingenieur',
    'dessinateur' => 'Dessinateur',
    'client' => 'Client',
];
$statuts = ['actif', 'inactif'];
$statutLabels = [
    'actif' => 'Actif',
    'inactif' => 'Inactif',
];
$erreur_creation = '';
$message = '';

if (isset($_POST['new_nom'], $_POST['new_prenom'], $_POST['new_email'], $_POST['new_role'])) {
    $nom = trim($_POST['new_nom']);
    $prenom = trim($_POST['new_prenom']);
    $email = trim($_POST['new_email']);
    $role = $_POST['new_role'];
    $telephone = trim($_POST['new_telephone'] ?? '');

    if ($nom === '' || $prenom === '' || $email === '') {
        $erreur_creation = 'Tous les champs obligatoires doivent etre renseignes.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur_creation = 'Adresse email invalide.';
    } elseif (!in_array($role, $roles, true)) {
        $erreur_creation = 'Role invalide.';
    } else {
        try {
            $activation_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut, telephone, activation_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $temp_password = bin2hex(random_bytes(4));
            $stmt->execute([
                $nom, $prenom, $email, password_hash($temp_password, PASSWORD_DEFAULT), $role, 'inactif', $telephone, $activation_token
            ]);


            $activation_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/gestion_projet/activation.php?token=' . $activation_token;
            $subject = 'Activation de votre compte Buildflow';
            $roleLabel = $roleLabels[$role] ?? ucfirst($role);
            $message_mail = "Bonjour " . htmlspecialchars($prenom . ' ' . $nom) . ",<br><br>"
                . "Votre compte Buildflow vient d'etre cree avec le role <strong>" . htmlspecialchars($roleLabel) . "</strong>.<br>"
                . "Cliquez sur ce lien pour activer votre compte et definir votre mot de passe :<br>"
                . "<a href='" . htmlspecialchars($activation_link) . "'>" . htmlspecialchars($activation_link) . "</a><br><br>"
                . "Apres activation, vous pourrez vous connecter a votre interface.";
            $mailSent = sendMailSMTP($email, $subject, $message_mail, "$prenom $nom");

            header('Location: utilisateurs.php?created=1' . ($mailSent ? '' : '&mail=0'));
            exit();
        } catch (PDOException $e) {
            $erreur_creation = $e->getCode() == 23000 ? 'Cet email existe deja.' : 'Erreur lors de la creation : ' . $e->getMessage();
        }
    }
}

if (isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $nom = trim($_POST['edit_nom'] ?? '');
    $prenom = trim($_POST['edit_prenom'] ?? '');
    $email = trim($_POST['edit_email'] ?? '');
    $role = $_POST['edit_role'] ?? '';
    $statut = $_POST['edit_statut'] ?? '';
    $telephone = trim($_POST['edit_telephone'] ?? '');
    $password = $_POST['edit_password'] ?? '';

    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($nom === '' || $prenom === '' || $email === '') {
            throw new RuntimeException('Tous les champs obligatoires doivent etre renseignes.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Adresse email invalide.');
        }

        if ($password !== '' && strlen($password) < 6) {
            throw new RuntimeException('Le mot de passe doit contenir au moins 6 caracteres.');
        }

        if (!in_array($role, $roles, true) || !in_array($statut, $statuts, true)) {
            throw new RuntimeException('Role ou statut invalide.');
        }

        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, role = ?, statut = ?, telephone = ?, mot_de_passe = ? WHERE id = ?');
            $stmt->execute([$nom, $prenom, $email, $role, $statut, $telephone, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, role = ?, statut = ?, telephone = ? WHERE id = ?');
            $stmt->execute([$nom, $prenom, $email, $role, $statut, $telephone, $id]);
        }

        if ($isAjax) {
            echo json_encode(['success' => true, 'message' => 'Modification enregistree avec succes.']);
            exit();
        }

        header('Location: utilisateurs.php?updated=1');
        exit();
    } catch (Throwable $e) {
        $msg = $e instanceof PDOException && $e->getCode() == 23000 ? 'Cet email existe deja.' : $e->getMessage();
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        }
        $message = $msg;
    }
}

if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    if ($id !== (int)$_SESSION['user_id']) {
        try {
            $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
            $stmt->execute([$id]);
            header('Location: utilisateurs.php?deleted=1');
            exit();
        } catch (PDOException $e) {
            $message = $e->getCode() == 23000
                ? 'Impossible de supprimer cet utilisateur car il est lie a des projets, taches, rapports ou factures.'
                : 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    } else {
        $message = 'Vous ne pouvez pas supprimer votre propre compte.';
    }
}

if (isset($_POST['toggle_status_id']) && is_numeric($_POST['toggle_status_id'])) {
    $id = (int)$_POST['toggle_status_id'];
    $newStatut = $_POST['new_statut'] ?? '';
    
    if ($id !== (int)$_SESSION['user_id'] && in_array($newStatut, ['actif', 'inactif'], true)) {
        try {
            $stmt = $pdo->prepare('UPDATE utilisateurs SET statut = ? WHERE id = ?');
            $stmt->execute([$newStatut, $id]);
            header('Location: utilisateurs.php?status_changed=1');
            exit();
        } catch (PDOException $e) {
            $message = 'Erreur lors du changement de statut : ' . $e->getMessage();
        }
    } else {
        $message = 'Operation non autorisee.';
    }
}

$where = [];
$params = [];

if (!empty($_GET['role']) && in_array($_GET['role'], $roles, true)) {
    $where[] = 'role = :role';
    $params[':role'] = $_GET['role'];
}

if (!empty($_GET['statut']) && in_array($_GET['statut'], $statuts, true)) {
    $where[] = 'statut = :statut';
    $params[':statut'] = $_GET['statut'];
}

if (!empty($_GET['search'])) {
    $where[] = '(nom LIKE :search OR prenom LIKE :search OR email LIKE :search OR telephone LIKE :search)';
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$sql = 'SELECT * FROM utilisateurs';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY nom, prenom';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$utilisateurs = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">

<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <img src="/gestion_projet/image/WhatsApp%20Image%202026-05-11%20at%2017.24.59.jpeg" alt="Logo Buildflow" width="36" height="36" class="sidebar-logo rounded-circle" style="object-fit:cover;">
                <span class="sidebar-title">Buildflow</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i><span>Tableau de bord</span></a></li>
                <li class="nav-item"><a href="projets.php" class="nav-link"><i class="bi bi-folder2"></i><span>Projets</span></a></li>
                <li class="nav-item"><a href="factures.php" class="nav-link"><i class="bi bi-receipt"></i><span>Factures</span></a></li>
                <li class="nav-item"><a href="paiements.php" class="nav-link"><i class="bi bi-credit-card"></i><span>Paiements</span></a></li>
                <li class="nav-item"><a href="taches.php" class="nav-link"><i class="bi bi-list-task"></i><span>Taches</span></a></li>
                <li class="nav-item"><a href="alertes.php" class="nav-link"><i class="bi bi-exclamation-triangle"></i><span>Alertes</span></a></li>
                <li class="nav-item"><a href="utilisateurs.php" class="nav-link active"><i class="bi bi-person-gear"></i><span>Administrateur</span></a></li>
                <li class="nav-item"><a href="rapports.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Rapports</span></a></li>
                <li class="nav-item"><a href="statistiques.php" class="nav-link"><i class="bi bi-bar-chart"></i><span>Statistiques</span></a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="/gestion_projet/logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i><span>Deconnexion</span></a>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <nav class="top-navbar">
            <div class="navbar-left">
                <i class="bi bi-list menu-toggle" id="menuToggle"></i>
                <div class="navbar-breadcrumb"><i class="bi bi-person-gear"></i><span>Administrateur</span></div>
            </div>
            <div class="navbar-right">
                <form class="navbar-search" method="get" action=""><i class="bi bi-search"></i><input type="text" name="search" placeholder="Rechercher..."></form>
                <a href="notifications.php" class="navbar-icon" title="Notifications"><i class="bi bi-bell"></i></a>
                <img src="<?= htmlspecialchars($_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png') ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>

        <div class="content-area">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="bi bi-person-gear"></i> Administrateur</h1>
                    <p class="page-subtitle">Gestion des utilisateurs, activation des comptes et parametrage manuel de la base de donnees.</p>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn-modern btn-success-modern" data-bs-toggle="modal" data-bs-target="#modalUser">
                        <i class="bi bi-plus-circle"></i> Nouvel utilisateur
                    </button>
                </div>
            </div>

            <?php if ($erreur_creation || $message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreur_creation ?: $message) ?></div>
            <?php elseif (isset($_GET['created']) || isset($_GET['updated']) || isset($_GET['deleted']) || isset($_GET['status_changed'])): ?>
                <div class="alert alert-success">
                    Operation effectuee avec succes.
                    <?php if (isset($_GET['mail'])): ?>
                        <br><strong>Attention :</strong> le compte est cree, mais l'email n'a pas pu etre envoye. Verifiez la configuration SMTP.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="section-card mb-3">
                <div class="section-header">
                    <div class="section-title"><i class="bi bi-database-gear"></i> Parametrage manuel</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card-modern p-3 h-100">
                            <div class="text-muted small">Base de donnees</div>
                            <strong><?= htmlspecialchars(DB_NAME) ?></strong>
                            <div class="text-muted small mt-1">Serveur: <?= htmlspecialchars(DB_HOST) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-modern p-3 h-100">
                            <div class="text-muted small">Utilisateurs actifs</div>
                            <strong><?= count(array_filter($utilisateurs, fn($u) => $u['statut'] === 'actif')) ?></strong>
                            <div class="text-muted small mt-1">Desactivation possible depuis les actions.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-modern p-3 h-100">
                            <div class="text-muted small">Parametres email</div>
                            <strong><?= defined('SMTP_HOST') && SMTP_HOST ? htmlspecialchars(SMTP_HOST) : 'SMTP non configure' ?></strong>
                            <div class="text-muted small mt-1">A renseigner dans config/config.php.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filters-section">
                <form class="filters-row" method="get">
                    <div class="filter-group">
                        <label class="filter-label" for="role">Role</label>
                        <select class="filter-select" id="role" name="role">
                            <option value="">Tous les roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>" <?= ($_GET['role'] ?? '') === $role ? 'selected' : '' ?>><?= htmlspecialchars($roleLabels[$role]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="statut">Statut</label>
                        <select class="filter-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts as $statut): ?>
                                <option value="<?= $statut ?>" <?= ($_GET['statut'] ?? '') === $statut ? 'selected' : '' ?>><?= htmlspecialchars($statutLabels[$statut]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="search">Recherche</label>
                        <input type="text" class="filter-input" id="search" name="search" placeholder="Nom, prenom, email, telephone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter"><i class="bi bi-search"></i> Rechercher</button>
                    </div>
                    <div class="filter-group">
                        <a href="utilisateurs.php" class="btn-reset text-center text-decoration-none">Reinitialiser</a>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Statut</th>
                                <th>Derniere connexion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$utilisateurs): ?>
                                <tr><td colspan="6" class="text-center">Aucun utilisateur trouve.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($utilisateurs as $user): ?>
                                <?php
                                $roleClass = str_replace('_', '-', $user['role']);
                                $statutClass = $user['statut'] === 'actif' ? 'termine' : 'annule';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
                                        <div class="text-muted small"><?= htmlspecialchars($user['telephone'] ?: 'Telephone non renseigne') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="role-badge role-<?= htmlspecialchars($roleClass) ?>"><?= htmlspecialchars($roleLabels[$user['role']] ?? ucfirst($user['role'])) ?></span></td>
                                    <td><span class="status-badge status-<?= $statutClass ?>"><?= htmlspecialchars($statutLabels[$user['statut']] ?? ucfirst($user['statut'])) ?></span></td>
                                    <td><?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : '-' ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-action btn-action-edit btn-edit-user" title="Modifier" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                                <button type="button" class="btn-action <?= $user['statut'] === 'actif' ? 'btn-action-warning' : 'btn-action-view' ?> btn-toggle-status" title="<?= $user['statut'] === 'actif' ? 'Désactiver' : 'Activer' ?>" data-bs-toggle="modal" data-bs-target="#toggleStatusModal" data-id="<?= (int)$user['id'] ?>" data-name="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'], ENT_QUOTES) ?>" data-statut="<?= $user['statut'] ?>">
                                                    <i class="bi <?= $user['statut'] === 'actif' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                                </button>
                                                <button type="button" class="btn-action btn-action-delete btn-delete-user" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-id="<?= (int)$user['id'] ?>" data-name="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'], ENT_QUOTES) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade modal-modern" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post">
            <div class="modal-header">
                <h5 class="modal-title">Nouvel utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><input type="text" class="form-control-modern" name="new_prenom" placeholder="Prenom" required></div>
                    <div class="col-md-6"><input type="text" class="form-control-modern" name="new_nom" placeholder="Nom" required></div>
                    <div class="col-12"><input type="email" class="form-control-modern" name="new_email" placeholder="Email" required></div>
                    <div class="col-md-6"><input type="text" class="form-control-modern" name="new_telephone" placeholder="Telephone"></div>
                    <div class="col-md-6">
                        <select class="filter-select w-100" name="new_role" required>
                            <option value="">Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= htmlspecialchars($roleLabels[$role]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">L'utilisateur recevra un email pour activer son compte et definir lui-meme son mot de passe.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-modern btn-success-modern">Creer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="editUserForm" method="post">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="modal-header">
                <h5 class="modal-title">Modifier utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editUserMsg"></div>
                <div class="row g-3">
                    <div class="col-md-6"><input type="text" class="form-control-modern" name="edit_prenom" id="edit_prenom" placeholder="Prenom" required></div>
                    <div class="col-md-6"><input type="text" class="form-control-modern" name="edit_nom" id="edit_nom" placeholder="Nom" required></div>
                    <div class="col-12"><input type="email" class="form-control-modern" name="edit_email" id="edit_email" placeholder="Email" required></div>
                    <div class="col-md-6"><input type="text" class="form-control-modern" name="edit_telephone" id="edit_telephone" placeholder="Telephone"></div>
                    <div class="col-md-3">
                        <select class="filter-select w-100" name="edit_role" id="edit_role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= htmlspecialchars($roleLabels[$role]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="filter-select w-100" name="edit_statut" id="edit_statut" required>
                            <?php foreach ($statuts as $statut): ?>
                                <option value="<?= $statut ?>"><?= htmlspecialchars($statutLabels[$statut]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><input type="password" class="form-control-modern" name="edit_password" id="edit_password" placeholder="Nouveau mot de passe (laisser vide pour conserver)"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-modern btn-success-modern">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post">
            <input type="hidden" name="delete_id" id="delete_id">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Supprimer <strong id="delete_user_name"></strong> ? Cette action est definitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-modern btn-danger-modern">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" id="toggleStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post">
            <input type="hidden" name="toggle_status_id" id="toggle_status_id">
            <input type="hidden" name="new_statut" id="new_statut">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-info-circle text-primary"></i> Changer le statut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="toggle_status_message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-modern btn-primary-modern" id="toggle_status_btn">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});

document.querySelectorAll('.btn-edit-user').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const user = JSON.parse(this.dataset.user);
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_nom').value = user.nom;
        document.getElementById('edit_prenom').value = user.prenom;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_telephone').value = user.telephone || '';
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_statut').value = user.statut;
        document.getElementById('edit_password').value = '';
        document.getElementById('editUserMsg').innerHTML = '';
    });
});

document.querySelectorAll('.btn-delete-user').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('delete_id').value = this.dataset.id;
        document.getElementById('delete_user_name').textContent = this.dataset.name;
    });
});

document.querySelectorAll('.btn-toggle-status').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        const currentStatut = this.dataset.statut;
        const newStatut = currentStatut === 'actif' ? 'inactif' : 'actif';
        const action = newStatut === 'inactif' ? 'désactiver' : 'activer';
        
        document.getElementById('toggle_status_id').value = id;
        document.getElementById('new_statut').value = newStatut;
        document.getElementById('toggle_status_message').innerHTML = 
            'Voulez-vous <strong>' + action + '</strong> le compte de <strong>' + name + '</strong> ?';
        document.getElementById('toggle_status_btn').textContent = action.charAt(0).toUpperCase() + action.slice(1);
    });
});

document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('editUserMsg');
    fetch('utilisateurs.php', {
        method: 'POST',
        body: new FormData(this),
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        msg.innerHTML = '<div class="alert alert-' + (data.success ? 'success' : 'danger') + '">' + data.message + '</div>';
        if (data.success) {
            setTimeout(function() { window.location.reload(); }, 700);
        }
    })
    .catch(function() {
        msg.innerHTML = '<div class="alert alert-danger">Erreur lors de la modification.</div>';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
