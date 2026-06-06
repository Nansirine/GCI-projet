<?php
function sanitize(string $input): string {
  return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function ensureUserAccountColumns(PDO $pdo): void {
  $columns = [
    'activation_token' => "ALTER TABLE utilisateurs ADD activation_token VARCHAR(128) NULL",
    'reset_token' => "ALTER TABLE utilisateurs ADD reset_token VARCHAR(128) NULL",
    'reset_token_expires' => "ALTER TABLE utilisateurs ADD reset_token_expires DATETIME NULL",
  ];

  foreach ($columns as $column => $sql) {
    try {
      $exists = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = ?");
      $exists->execute([$column]);
      if ((int)$exists->fetchColumn() === 0) {
        $pdo->exec($sql);
      }
    } catch (Exception $e) {
      error_log('Schema utilisateurs error: ' . $e->getMessage());
    }
  }
}

function ensureDocumentDecisionColumns(PDO $pdo): void {
  $tables = ['plans', 'rapports'];
  $columns = [
    'client_decision' => "ADD client_decision ENUM('en_attente','approuve','refuse') DEFAULT 'en_attente'",
    'commentaire_client' => "ADD commentaire_client TEXT NULL",
    'date_decision_client' => "ADD date_decision_client DATETIME NULL",
  ];

  foreach ($tables as $table) {
    foreach ($columns as $column => $alter) {
      try {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $exists->execute([$table, $column]);
        if ((int)$exists->fetchColumn() === 0) {
          $pdo->exec("ALTER TABLE {$table} {$alter}");
        }
      } catch (Exception $e) {
        error_log('Schema documents error: ' . $e->getMessage());
      }
    }
  }
}

function ensureTaskDependencyColumn(PDO $pdo): void {
  try {
    $exists = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'taches' AND COLUMN_NAME = ?");
    $exists->execute(['dependance_id']);
    if ((int)$exists->fetchColumn() === 0) {
      $pdo->exec("ALTER TABLE taches ADD dependance_id INT NULL AFTER cree_par");
    }
  } catch (Exception $e) {
    error_log('Schema taches dependency error: ' . $e->getMessage());
  }
}

function civilEngineeringFileExtensions(): array {
  return [
    'dwg', 'dxf', 'dgn',
    'rvt', 'rte', 'rfa', 'ifc', 'nwd', 'nwf', 'pln',
    'mpp', 'xer', 'gan',
    'stb', 'r3d', 'std', 'edb', 'sdb', 'gwb',
    'xlsx', 'csv',
    'pdf', 'docx', 'doc',
    'shp', 'kml', 'kmz', 'xyz',
  ];
}

function civilEngineeringAcceptAttribute(): string {
  return '.' . implode(',.', civilEngineeringFileExtensions());
}

function createNotification(PDO $pdo, int $user_id, string $titre,
                            string $message, string $type='info',
                            ?string $lien=null): bool {
  try {
    if ($lien && strpos($lien, '/') === 0 && strpos($lien, '/gestion_projet/') !== 0) {
      $lien = '/gestion_projet' . $lien;
    }

    $stmt = $pdo->prepare("INSERT INTO notifications 
                           (utilisateur_id, titre, message, type, lien)
                           VALUES (?,?,?,?,?)");
    return $stmt->execute([$user_id, $titre, $message, $type, $lien]);
  } catch(Exception $e) {
    error_log('Notif error: '.$e->getMessage());
    return false;
  }
}

function roleBasePath(string $role): string {
  return match ($role) {
    'admin' => '/admin',
    'ingenieur' => '/ingenieur',
    'dessinateur' => '/dessinateur',
    'client' => '/client',
    default => '',
  };
}

function userRole(PDO $pdo, int $userId): ?string {
  $stmt = $pdo->prepare('SELECT role FROM utilisateurs WHERE id = ?');
  $stmt->execute([$userId]);
  $role = $stmt->fetchColumn();
  return $role ?: null;
}

function userMessagesLink(PDO $pdo, int $userId): string {
  $role = userRole($pdo, $userId);
  $base = $role ? roleBasePath($role) : '';
  return $base ? $base . '/messages.php' : '/admin/notifications.php';
}

function userNotificationsLink(PDO $pdo, int $userId): string {
  $role = userRole($pdo, $userId);
  $base = $role ? roleBasePath($role) : '';
  return $base ? $base . '/notifications.php' : '/admin/notifications.php';
}

function updateProjectProgress(PDO $pdo, int $projet_id): void {
  try {
    $stmt = $pdo->prepare("SELECT AVG(pourcentage) as avg FROM taches 
                            WHERE projet_id = ? AND statut != 'bloque'");
    $stmt->execute([$projet_id]);
    $avg = (int)($stmt->fetchColumn() ?? 0);
    $avg = max(0, min(100, $avg));
    $pdo->prepare("UPDATE projets SET pourcentage_avancement = ? WHERE id = ?")
        ->execute([$avg, $projet_id]);
    // Auto-marquer jalons manqués
    $pdo->prepare("UPDATE jalons SET statut='manque' 
                   WHERE projet_id = ? AND date_prevue < CURDATE() AND statut='a_venir'")
        ->execute([$projet_id]);
  } catch(Exception $e) {
    error_log('Progress update error: '.$e->getMessage());
  }
}

function uploadFichier(array $file, string $dossier, 
                       array $types_autorises, int $taille_max): string|false {
  if ($file['error'] !== UPLOAD_ERR_OK) return false;
  if ($file['size'] > $taille_max) return false;
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $types_autorises, true)) return false;
  // Vérifier type MIME réel
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  $mimes_ok = ['application/pdf','image/png','image/jpeg',
               'application/msword',
               'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
               'application/vnd.ms-excel',
               'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
               'text/csv',
               'text/plain',
               'application/octet-stream',
               'application/zip'];
  $binary_extensions = [
    'dwg', 'dxf', 'dgn',
    'rvt', 'rte', 'rfa', 'ifc', 'nwd', 'nwf', 'pln',
    'mpp', 'xer', 'gan',
    'stb', 'r3d', 'std', 'edb', 'sdb', 'gwb',
    'shp', 'kml', 'kmz', 'xyz',
  ];
  if (!in_array($ext, $binary_extensions, true) && !in_array($mime, $mimes_ok, true)) return false;
  // Nom unique sécurisé
  $nom = bin2hex(random_bytes(16)).'.'.$ext;
  if (!is_dir($dossier)) mkdir($dossier, 0755, true);
  if (!move_uploaded_file($file['tmp_name'], $dossier.$nom)) return false;
  return $nom;
}

function getBadgeStatut(string $statut): string {
  $map = [
    'en_attente'=>['secondary','En Attente'], 'en_cours'=>['primary','En Cours'],
    'termine'=>['success','Terminé'], 'suspendu'=>['warning','Suspendu'],
    'annule'=>['danger','Annulé'], 'soumis'=>['info','Soumis'],
    'valide'=>['success','Validé'], 'rejete'=>['danger','Rejeté'],
    'a_faire'=>['secondary','À Faire'], 'en_revision'=>['warning','En Révision'],
    'bloque'=>['danger','Bloqué'], 'brouillon'=>['secondary','Brouillon'],
    'archive'=>['dark','Archivé'],
    'approuve'=>['success','Approuvé'], 'refuse'=>['danger','Refusé'],
  ];
  [$class, $label] = $map[$statut] ?? ['secondary', $statut];
  return "<span class='badge bg-{$class}'>".htmlspecialchars($label)."</span>";
}

function getPriorityBadge(string $priorite): string {
  $map = ['urgente'=>'danger','haute'=>'warning','moyenne'=>'info','basse'=>'secondary'];
  $class = $map[$priorite] ?? 'secondary';
  return "<span class='badge bg-{$class}'>".strtoupper(htmlspecialchars($priorite))."</span>";
}

function timeAgo(string $datetime): string {
  $diff = time() - strtotime($datetime);
  if ($diff < 60) return "À l'instant";
  if ($diff < 3600) return floor($diff/60).' min';
  if ($diff < 86400) return floor($diff/3600).' h';
  if ($diff < 2592000) return floor($diff/86400).' j';
  return date('d/m/Y', strtotime($datetime));
}

function formatMontant(float $montant): string {
  return number_format($montant, 0, ',', ' ').' FCFA';
}

function formatDate(string $date): string {
  return date('d/m/Y', strtotime($date));
}

function formatDatetime(string $dt): string {
  return date('d/m/Y à H:i', strtotime($dt));
}

function getProjectMembers(PDO $pdo, int $projet_id): array {
  $stmt = $pdo->prepare("SELECT u.id, u.nom, u.prenom, u.email, u.role, u.photo,
                          a.role_projet FROM utilisateurs u
                          JOIN affectations a ON a.utilisateur_id = u.id
                          WHERE a.projet_id = ?");
  $stmt->execute([$projet_id]);
  return $stmt->fetchAll();
}

function userBelongsToProject(PDO $pdo, int $user_id, int $projet_id): bool {
  $stmt = $pdo->prepare("SELECT 1 FROM affectations 
                          WHERE utilisateur_id = ? AND projet_id = ?
                          UNION
                          SELECT 1 FROM projets 
                          WHERE id = ? AND (admin_id = ? OR client_id = ?)");
  $stmt->execute([$user_id,$projet_id,$projet_id,$user_id,$user_id]);
  return (bool)$stmt->fetch();
}

function constructionDocumentTypes(): array {
  return [
    'DAO et conception CAD' => ['dwg', 'dxf', 'dgn'],
    'BIM et maquettes' => ['rvt', 'rte', 'rfa', 'ifc', 'nwd', 'nwf', 'pln'],
    'Planification' => ['mpp', 'xer', 'gan'],
    'Calcul structure et geotechnique' => ['stb', 'r3d', 'std', 'edb', 'sdb', 'gwb'],
    'Metres et estimations' => ['xlsx', 'csv'],
    'Contrats et administratif' => ['pdf', 'doc', 'docx'],
    'Topographie et SIG' => ['shp', 'kml', 'kmz', 'xyz'],
  ];
}

function fileExtension(?string $path): string {
  return strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
}

function isPreviewableFile(?string $path): bool {
  return in_array(fileExtension($path), ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt'], true);
}

function documentIcon(?string $path): string {
  return match (fileExtension($path)) {
    'pdf' => 'bi-file-earmark-pdf',
    'doc', 'docx' => 'bi-file-earmark-word',
    'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel',
    'png', 'jpg', 'jpeg', 'gif', 'webp' => 'bi-file-earmark-image',
    'dwg', 'dxf' => 'bi-file-earmark-ruled',
    'rvt', 'rte', 'rfa', 'ifc', 'nwd', 'nwf', 'pln' => 'bi-buildings',
    'mpp', 'xer', 'gan' => 'bi-calendar3',
    'shp', 'kml', 'kmz', 'xyz' => 'bi-geo-alt',
    default => 'bi-file-earmark',
  };
}

function renderDocumentActions(string $type, int $id, ?string $path, string $label = 'Document'): string {
  if (!$path) {
    return '<span class="text-muted">Aucun fichier</span>';
  }

  $label = htmlspecialchars($label);
  $viewUrl = '/gestion_projet/document.php?type=' . urlencode($type) . '&id=' . $id;
  $downloadUrl = $viewUrl . '&download=1';
  $preview = isPreviewableFile($path)
    ? '<a class="btn-action btn-action-view" href="' . $viewUrl . '" target="_blank" title="Lire"><i class="bi bi-eye"></i></a>'
    : '';

  return '<div class="action-buttons">'
    . $preview
    . '<a class="btn-action btn-action-edit" href="' . $downloadUrl . '" title="Telecharger ' . $label . '"><i class="bi bi-download"></i></a>'
    . '</div>';
}

function renderClientDocumentActions(string $type, int $id, ?string $path, string $decision, string $label = 'Document'): string {
  if (!$path) {
    return '<span class="text-muted">Aucun fichier</span>';
  }

  $label = htmlspecialchars($label);
  $viewUrl = '/gestion_projet/document.php?type=' . urlencode($type) . '&id=' . $id;
  $downloadUrl = $viewUrl . '&download=1';
  $download = $decision === 'approuve'
    ? '<a class="btn-action btn-action-edit" href="' . $downloadUrl . '" title="Telecharger ' . $label . '"><i class="bi bi-download"></i></a>'
    : '<button type="button" class="btn-action btn-action-disabled" title="Approuvez le fichier avant de telecharger" disabled><i class="bi bi-download"></i></button>';

  return '<div class="action-buttons">'
    . '<a class="btn-action btn-action-view" href="' . $viewUrl . '" target="_blank" title="Lire"><i class="bi bi-eye"></i></a>'
    . $download
    . '</div>';
}

function getRoleInterfaceMessage(?string $role): array {
  $messages = [
    'admin' => ['Chef projet', 'Interface de pilotage: utilisateurs, projets, validations et decisions.'],
    'ingenieur' => ['Ingenieur', 'Interface technique: taches, rapports, alertes et documents a valider.'],
    'dessinateur' => ['Dessinateur', 'Interface plans: depot, suivi des validations et documents graphiques.'],
    'client' => ['Client', 'Interface client: suivi, approbation et telechargement des fichiers de vos projets.'],
  ];

  return $messages[$role] ?? ['Utilisateur', 'Interface Buildflow'];
}

function renderDocumentPreview(string $type, int $id, ?string $path, string $title = 'Document'): string {
  if (!$path) {
    return '<div class="alert alert-info">Aucun fichier joint.</div>';
  }

  $url = '/gestion_projet/document.php?type=' . urlencode($type) . '&id=' . $id;
  $downloadUrl = $url . '&download=1';
  $title = htmlspecialchars($title);
  $ext = fileExtension($path);

  if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
    return '<div class="document-preview"><img src="' . $url . '" alt="' . $title . '" class="img-fluid rounded border"></div>'
      . '<a class="btn-modern btn-outline-modern mt-3" href="' . $downloadUrl . '"><i class="bi bi-download"></i> Telecharger</a>';
  }

  if ($ext === 'pdf') {
    return '<div class="document-preview" style="height:70vh;"><iframe src="' . $url . '" title="' . $title . '" style="width:100%;height:100%;border:1px solid #e2e8f0;border-radius:8px;"></iframe></div>'
      . '<a class="btn-modern btn-outline-modern mt-3" href="' . $downloadUrl . '"><i class="bi bi-download"></i> Telecharger</a>';
  }

  return '<div class="alert alert-info">Ce type de fichier ne se lit pas directement dans le navigateur. Vous pouvez le telecharger.</div>'
    . '<a class="btn-modern btn-outline-modern" href="' . $downloadUrl . '"><i class="bi bi-download"></i> Telecharger</a>';
}

/**
 * Génère un message de bienvenue personnalisé selon l'heure et le rôle
 */
function getWelcomeMessage(string $prenom, string $nom, string $role): array {
    $heure = (int)date('H');
    
    // Déterminer le moment de la journée
    if ($heure >= 5 && $heure < 12) {
        $salutation = 'Bonjour';
        $emoji = '';
    } elseif ($heure >= 12 && $heure < 18) {
        $salutation = 'Bon apres-midi';
        $emoji = '';
    } else {
        $salutation = 'Bonsoir';
        $emoji = '';
    }
    
    // Messages personnalisés selon le rôle
    $messages = [
        'admin' => 'Vue claire sur les projets, les equipes et les decisions du jour',
        'ingenieur' => 'Vos taches, rapports et alertes sont prets a etre suivis',
        'dessinateur' => 'Vos plans vous attendent',
        'client' => 'Suivez simplement l\'avancement, les plans et les rapports de vos projets'
    ];
    
    $roleMessage = $messages[$role] ?? 'Bienvenue sur la plateforme';
    $roleNames = [
        'admin' => 'Chef projet',
        'ingenieur' => 'Ingenieur',
        'dessinateur' => 'Dessinateur',
        'client' => trim($prenom),
    ];
    $displayName = $roleNames[$role] ?? trim($prenom . ' ' . $nom);
    
    return [
        'salutation' => $salutation,
        'emoji' => $emoji,
        'nom_complet' => $displayName,
        'message' => $roleMessage
    ];
}

