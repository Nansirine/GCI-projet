<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

function firstUserId(PDO $pdo, string $role): int {
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE role = ? ORDER BY id LIMIT 1");
    $stmt->execute([$role]);
    return (int)$stmt->fetchColumn();
}

$adminId = firstUserId($pdo, 'admin');
$ingenieurId = firstUserId($pdo, 'ingenieur');
$dessinateurId = firstUserId($pdo, 'dessinateur');
$clientId = firstUserId($pdo, 'client');

if (!$adminId || !$ingenieurId || !$dessinateurId || !$clientId) {
    throw new RuntimeException('Il faut au moins un admin, un ingenieur, un dessinateur et un client actifs pour creer le flux demo.');
}

$projectStmt = $pdo->prepare("SELECT id FROM projets WHERE client_id = ? ORDER BY id LIMIT 1");
$projectStmt->execute([$clientId]);
$projectId = (int)$projectStmt->fetchColumn();

if (!$projectId) {
    $insertProject = $pdo->prepare("INSERT INTO projets (nom, description, localisation, budget, date_debut, date_fin_prevue, statut, pourcentage_avancement, admin_id, client_id)
        VALUES ('Projet demo flux Buildflow', 'Projet cree pour tester le flux complet.', 'Abidjan', 50000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'en_cours', 25, ?, ?)");
    $insertProject->execute([$adminId, $clientId]);
    $projectId = (int)$pdo->lastInsertId();
}

$affectation = $pdo->prepare("INSERT INTO affectations (projet_id, utilisateur_id, role_projet)
    SELECT ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM affectations WHERE projet_id = ? AND utilisateur_id = ?)");
$affectation->execute([$projectId, $ingenieurId, 'ingenieur', $projectId, $ingenieurId]);
$affectation->execute([$projectId, $dessinateurId, 'dessinateur', $projectId, $dessinateurId]);

$findTask = $pdo->prepare("SELECT id FROM taches WHERE projet_id = ? AND titre = ? LIMIT 1");
$findTask->execute([$projectId, 'Preparation des plans DAO pour validation']);
$taskDessinateurId = (int)$findTask->fetchColumn();
if (!$taskDessinateurId) {
    $stmt = $pdo->prepare("INSERT INTO taches (projet_id, titre, description, assigne_a, cree_par, priorite, statut, pourcentage, date_debut, date_echeance)
        VALUES (?, 'Preparation des plans DAO pour validation', 'Tache demo affectee au dessinateur pour verifier le flux de travail.', ?, ?, 'haute', 'en_cours', 30, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 DAY))");
    $stmt->execute([$projectId, $dessinateurId, $adminId]);
    $taskDessinateurId = (int)$pdo->lastInsertId();
}

$findTask->execute([$projectId, 'Controle technique et rapport de validation']);
$taskIngenieurId = (int)$findTask->fetchColumn();
if (!$taskIngenieurId) {
    $stmt = $pdo->prepare("INSERT INTO taches (projet_id, titre, description, assigne_a, cree_par, priorite, statut, pourcentage, date_debut, date_echeance)
        VALUES (?, 'Controle technique et rapport de validation', 'Tache demo affectee a l ingenieur pour generer un rapport visible par le chef projet.', ?, ?, 'moyenne', 'en_revision', 80, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))");
    $stmt->execute([$projectId, $ingenieurId, $adminId]);
    $taskIngenieurId = (int)$pdo->lastInsertId();
}

$findReport = $pdo->prepare("SELECT id FROM rapports WHERE projet_id = ? AND titre = ? LIMIT 1");
$findReport->execute([$projectId, 'Rapport demo - controle technique']);
if (!$findReport->fetchColumn()) {
    $stmt = $pdo->prepare("INSERT INTO rapports (projet_id, tache_id, ingenieur_id, titre, contenu, statut, commentaire_admin, date_validation)
        VALUES (?, ?, ?, 'Rapport demo - controle technique', 'Rapport cree automatiquement pour verifier le flux ingenieur vers chef projet, puis partage client apres validation.', 'valide', 'Rapport valide pour demonstration du flux.', NOW())");
    $stmt->execute([$projectId, $taskIngenieurId, $ingenieurId]);
}

$findInvoice = $pdo->prepare("SELECT id FROM factures WHERE numero = 'FAC-DEMO-FLUX-001' LIMIT 1");
$findInvoice->execute();
$invoiceId = (int)$findInvoice->fetchColumn();
if (!$invoiceId) {
    $stmt = $pdo->prepare("INSERT INTO factures (numero, projet_id, client_id, admin_id, montant_total, montant_paye, statut, date_emission, date_echeance, notes)
        VALUES ('FAC-DEMO-FLUX-001', ?, ?, ?, 1500000, 0, 'emise', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Facture demo pour tester le formulaire de paiement client.')");
    $stmt->execute([$projectId, $clientId, $adminId]);
    $invoiceId = (int)$pdo->lastInsertId();

    $line = $pdo->prepare("INSERT INTO lignes_facture (facture_id, designation, description, quantite, prix_unitaire, montant_ligne, ordre)
        VALUES (?, 'Phase demo', 'Ligne de facture demo pour flux paiement client', 1, 1500000, 1500000, 1)");
    $line->execute([$invoiceId]);
}

$findPayment = $pdo->prepare("SELECT id FROM paiements WHERE facture_id = ? AND reference = 'PAY-DEMO-CLIENT-001' LIMIT 1");
$findPayment->execute([$invoiceId]);
if (!$findPayment->fetchColumn()) {
    $stmt = $pdo->prepare("INSERT INTO paiements (facture_id, client_id, montant, mode_paiement, reference, statut, date_paiement, commentaire)
        VALUES (?, ?, 500000, 'virement', 'PAY-DEMO-CLIENT-001', 'en_attente', CURDATE(), 'Paiement demo soumis cote client.')");
    $stmt->execute([$invoiceId, $clientId]);
}

createNotification($pdo, $dessinateurId, 'Tache demo affectee', 'Une tache demo vous a ete affectee pour tester le flux.', 'info', '/dessinateur/taches.php');
createNotification($pdo, $ingenieurId, 'Rapport demo disponible', 'Un rapport demo a ete cree pour tester le flux ingenieur.', 'info', '/ingenieur/rapports.php');
createNotification($pdo, $adminId, 'Flux demo pret', 'Tache dessinateur, rapport ingenieur et paiement client de demonstration sont disponibles.', 'succes', '/admin/dashboard.php');
createNotification($pdo, $clientId, 'Facture demo disponible', 'Une facture demo avec paiement en attente est disponible.', 'info', '/client/factures.php');

echo "Flux demo cree ou deja present.\n";
echo "Projet ID: {$projectId}\n";
echo "Tache dessinateur ID: {$taskDessinateurId}\n";
echo "Tache ingenieur ID: {$taskIngenieurId}\n";
echo "Facture demo ID: {$invoiceId}\n";
