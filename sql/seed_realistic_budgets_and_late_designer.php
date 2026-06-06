<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$adminId = (int)$pdo->query("SELECT id FROM utilisateurs WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
$clientId = (int)$pdo->query("SELECT id FROM utilisateurs WHERE role = 'client' ORDER BY id LIMIT 1")->fetchColumn();
$dessinateurId = (int)$pdo->query("SELECT id FROM utilisateurs WHERE role = 'dessinateur' ORDER BY id LIMIT 1")->fetchColumn();

if (!$adminId || !$clientId || !$dessinateurId) {
    throw new RuntimeException('Admin, client ou dessinateur introuvable.');
}

$pdo->exec("UPDATE projets SET budget = 85000 WHERE nom = 'Residence Laguna'");
$pdo->exec("UPDATE projets SET budget = 124000 WHERE nom = 'Centre Medical Nord'");
$pdo->exec("UPDATE projets SET budget = 210000 WHERE nom = 'Immeuble Horizon'");
$pdo->exec("UPDATE projets SET budget = 65000 WHERE nom = 'Villa Baobab'");
$pdo->exec("UPDATE projets SET budget = 60000 WHERE nom = 'Projet demo flux Buildflow'");
$pdo->exec("UPDATE projets SET budget = 120000 WHERE nom = 'Projet en retard - plans structure'");
$pdo->exec("UPDATE projets SET budget = 60000 WHERE budget IS NULL OR budget < 60000");
$pdo->exec("UPDATE factures SET montant_total = 60000 WHERE numero = 'FAC-DEMO-FLUX-001'");
$pdo->exec("UPDATE lignes_facture SET prix_unitaire = 60000, montant_ligne = 60000 WHERE facture_id = (SELECT id FROM factures WHERE numero = 'FAC-DEMO-FLUX-001')");

$findProject = $pdo->prepare("SELECT id FROM projets WHERE nom = 'Projet en retard - plans structure' LIMIT 1");
$findProject->execute();
$projectId = (int)$findProject->fetchColumn();

if (!$projectId) {
    $stmt = $pdo->prepare("INSERT INTO projets (nom, description, localisation, budget, date_debut, date_fin_prevue, statut, pourcentage_avancement, admin_id, client_id)
        VALUES ('Projet en retard - plans structure', 'Projet demo en retard pour verifier le suivi dessinateur.', 'Abidjan Plateau', 120000, DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'en_cours', 45, ?, ?)");
    $stmt->execute([$adminId, $clientId]);
    $projectId = (int)$pdo->lastInsertId();
}

$aff = $pdo->prepare("INSERT INTO affectations (projet_id, utilisateur_id, role_projet)
    SELECT ?, ?, 'dessinateur' WHERE NOT EXISTS (SELECT 1 FROM affectations WHERE projet_id = ? AND utilisateur_id = ?)");
$aff->execute([$projectId, $dessinateurId, $projectId, $dessinateurId]);

$findTask = $pdo->prepare("SELECT id FROM taches WHERE projet_id = ? AND titre = 'Plans de coffrage en retard' LIMIT 1");
$findTask->execute([$projectId]);
if (!$findTask->fetchColumn()) {
    $stmt = $pdo->prepare("INSERT INTO taches (projet_id, titre, description, assigne_a, cree_par, priorite, statut, pourcentage, date_debut, date_echeance)
        VALUES (?, 'Plans de coffrage en retard', 'Tache demo en retard affectee au dessinateur.', ?, ?, 'urgente', 'en_cours', 35, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY))");
    $stmt->execute([$projectId, $dessinateurId, $adminId]);
}

createNotification($pdo, $dessinateurId, 'Tache en retard', 'La tache "Plans de coffrage en retard" est en retard sur le projet demo.', 'avertissement', '/dessinateur/taches.php');

echo "Budgets realistes appliques et projet/tache en retard crees.\n";
echo "Projet en retard ID: {$projectId}\n";
