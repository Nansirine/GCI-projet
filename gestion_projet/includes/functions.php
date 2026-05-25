<?php
// === FONCTIONS NOTIFICATIONS ===
function createNotification($pdo, $user_id, $titre, $message, $type='info', $lien=null) {
    $sql = "INSERT INTO notifications (utilisateur_id, titre, message, type, lien) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $titre, $message, $type, $lien]);
}
function countUnreadNotifications($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE utilisateur_id=? AND lu=0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// === FONCTIONS UPLOAD ===
function uploadFichier($file, $dossier, $types_autorises, $taille_max) {
    if (!isset($_FILES[$file]) || $_FILES[$file]['error'] !== UPLOAD_ERR_OK) return false;
    $ext = strtolower(pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $types_autorises)) return false;
    if ($_FILES[$file]['size'] > $taille_max) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$file]['tmp_name']);
    finfo_close($finfo);
    $mimes = [
        'pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','dwg'=>'application/acad',
    ];
    if (isset($mimes[$ext]) && $mime !== $mimes[$ext]) return false;
    $nom = uniqid().'_'.basename($_FILES[$file]['name']);
    if (!move_uploaded_file($_FILES[$file]['tmp_name'], $dossier.'/'.$nom)) return false;
    return $nom;
}

// === FONCTIONS PROJET ===
function updateProjectProgress($pdo, $projet_id) {
    $stmt = $pdo->prepare("SELECT AVG(pourcentage) FROM taches WHERE projet_id=?");
    $stmt->execute([$projet_id]);
    $avg = (int) $stmt->fetchColumn();
    $pdo->prepare("UPDATE projets SET pourcentage_avancement=? WHERE id=?")->execute([$avg, $projet_id]);
    $pdo->prepare("UPDATE jalons SET statut='manque' WHERE projet_id=? AND date_prevue < NOW() AND statut='a_venir'")->execute([$projet_id]);
}
function getProjectMembers($pdo, $projet_id) {
    $sql = "SELECT u.* FROM utilisateurs u JOIN affectations a ON u.id=a.utilisateur_id WHERE a.projet_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projet_id]);
    return $stmt->fetchAll();
}

// === FONCTIONS FORMATAGE ===
function formatDate($date) { return date('d/m/Y', strtotime($date)); }
function formatDatetime($datetime) { return date('d/m/Y à H:i', strtotime($datetime)); }
function getBadgeStatut($statut) {
    $colors = [
        'en_attente'=>'secondary','en_cours'=>'primary','suspendu'=>'warning','termine'=>'success','annule'=>'danger',
        'a_faire'=>'secondary','en_revision'=>'info','bloque'=>'danger','valide'=>'success','rejete'=>'danger','soumis'=>'primary','brouillon'=>'secondary','archive'=>'dark','atteint'=>'success','manque'=>'danger','a_venir'=>'info','en_cours'=>'primary','termine'=>'success','en_retard'=>'danger','traite'=>'success','refuse'=>'danger','en_attente'=>'warning'
    ];
    $color = $colors[$statut] ?? 'secondary';
    return '<span class="badge bg-'.$color.'">'.htmlspecialchars($statut).'</span>';
}
function getPriorityBadge($priorite) {
    $colors = ['basse'=>'secondary','moyenne'=>'info','haute'=>'warning','urgente'=>'danger'];
    $color = $colors[$priorite] ?? 'secondary';
    return '<span class="badge bg-'.$color.'">'.htmlspecialchars($priorite).'</span>';
}
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'il y a '.$diff.' sec';
    if ($diff < 3600) return 'il y a '.floor($diff/60).' min';
    if ($diff < 86400) return 'il y a '.floor($diff/3600).' heures';
    if ($diff < 2592000) return 'il y a '.floor($diff/86400).' jours';
    return formatDate($datetime);
}
function formatMontant($montant) {
    return number_format($montant, 0, ',', ' ').' FCFA';
}

// === FONCTIONS SÉCURITÉ ===
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// === FONCTIONS PDF ===
function generateReportPDF($pdo, $projet_id) {
    // Nécessite mPDF ou TCPDF (à installer via Composer ou CDN)
    // Exemple avec mPDF (à adapter selon installation)
    require_once __DIR__.'/../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('<h1>Rapport Projet</h1>');
    // Ajouter logo, infos projet, tâches, rapports, graphique...
    $mpdf->Output('rapport_projet.pdf', 'D');
}

