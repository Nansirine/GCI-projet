<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$role = $_SESSION['role'] ?? null;
$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$photo = $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png';
$user_id = $_SESSION['user_id'] ?? null;
require_once __DIR__ . '/../config/database.php';
$pdo = $pdo ?? require __DIR__ . '/../config/database.php';
$notif_count = 0;
if ($user_id) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0');
    $stmt->execute([$user_id]);
    $notif_count = $stmt->fetchColumn();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#1a2d4f;">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="/gestion_projet/">
      <img src="/gestion_projet/assets/img/logo.png" alt="Logo" width="36" class="me-2">
      <span>Système de Gestion de Projets</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/admin/dashboard.php">Tableau de bord</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/admin/projets.php">Projets</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/admin/taches.php">Tâches</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/admin/utilisateurs.php">Utilisateurs</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/admin/rapports.php">Rapports</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/admin/statistiques.php">Statistiques</a></li>
        <?php elseif ($role === 'ingenieur'): ?>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/ingenieur/dashboard.php">Tableau de bord</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/ingenieur/taches.php">Mes Tâches</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/ingenieur/rapports.php">Rapports</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/ingenieur/documents.php">Documents</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/ingenieur/alertes.php">Alertes</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/ingenieur/messages.php">Messages</a></li>
        <?php elseif ($role === 'dessinateur'): ?>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/dessinateur/dashboard.php">Tableau de bord</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/dessinateur/plans.php">Plans</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/dessinateur/taches.php">Tâches</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/dessinateur/messages.php">Messages</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/dessinateur/notifications.php">Notifications</a></li>
        <?php elseif ($role === 'client'): ?>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/client/dashboard.php">Mon Projet</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/client/avancement.php">Avancement</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/client/plans.php">Plans</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/client/rapports.php">Rapports</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/client/planning.php">Planning</a></li>
          <li class="nav-item"><a class="nav-link" href="/gestion_projet/client/demandes.php">Demandes</a></li>
        <?php endif; ?>
      </ul>
      <?php if ($role): ?>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item me-3">
          <a class="nav-link position-relative" href="/gestion_projet/<?php echo $role; ?>/notifications.php">
            <i class="bi bi-bell" style="font-size:1.3rem;"></i>
            <?php if ($notif_count > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo $notif_count; ?>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?php echo htmlspecialchars($photo); ?>" alt="Profil" class="rounded-circle me-2" width="32" height="32">
            <span><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><span class="dropdown-item-text"><strong><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($role); ?></small></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/gestion_projet/<?php echo $role; ?>/profil.php">Mon Profil</a></li>
            <li><a class="dropdown-item" href="/gestion_projet/logout.php">Se Déconnecter</a></li>
          </ul>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>
