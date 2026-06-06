<?php
require_once __DIR__ . '/../config/database.php';

$adminId = (int)$pdo->query("SELECT id FROM utilisateurs WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
$clientId = (int)$pdo->query("SELECT id FROM utilisateurs WHERE role = 'client' ORDER BY id LIMIT 1")->fetchColumn();

if (!$adminId || !$clientId) {
    throw new RuntimeException('Admin ou client introuvable pour le jeu de donnees budgetaire.');
}

$projectStmt = $pdo->prepare("SELECT id FROM projets WHERE nom = 'Alerte budget 90 - Extension bureau' LIMIT 1");
$projectStmt->execute();
$projectId = (int)$projectStmt->fetchColumn();

if (!$projectId) {
    $stmt = $pdo->prepare("INSERT INTO projets (nom, description, localisation, budget, date_debut, date_fin_prevue, statut, pourcentage_avancement, admin_id, client_id)
        VALUES ('Alerte budget 90 - Extension bureau', 'Jeu de donnees pour verifier une alerte budgetaire a 90 pourcent du provisionnel.', 'Abidjan', 100000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'en_cours', 35, ?, ?)");
    $stmt->execute([$adminId, $clientId]);
    $projectId = (int)$pdo->lastInsertId();
} else {
    $pdo->prepare("UPDATE projets SET budget = 100000, statut = 'en_cours' WHERE id = ?")->execute([$projectId]);
}

$factureStmt = $pdo->prepare("SELECT id FROM factures WHERE numero = 'FAC-BUDGET-90-001' LIMIT 1");
$factureStmt->execute();
$factureId = (int)$factureStmt->fetchColumn();

if (!$factureId) {
    $stmt = $pdo->prepare("INSERT INTO factures (numero, projet_id, client_id, admin_id, montant_total, montant_paye, statut, date_emission, date_echeance, notes)
        VALUES ('FAC-BUDGET-90-001', ?, ?, ?, 90000, 0, 'emise', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Jeu de donnees: realise budgetaire a 90% du provisionnel.')");
    $stmt->execute([$projectId, $clientId, $adminId]);
    $factureId = (int)$pdo->lastInsertId();
} else {
    $pdo->prepare("UPDATE factures SET projet_id = ?, client_id = ?, admin_id = ?, montant_total = 90000, statut = 'emise' WHERE id = ?")
        ->execute([$projectId, $clientId, $adminId, $factureId]);
}

$lineStmt = $pdo->prepare("SELECT id FROM lignes_facture WHERE facture_id = ? AND designation = 'Realise budgetaire 90%' LIMIT 1");
$lineStmt->execute([$factureId]);
if (!$lineStmt->fetchColumn()) {
    $stmt = $pdo->prepare("INSERT INTO lignes_facture (facture_id, designation, description, quantite, prix_unitaire, montant_ligne, ordre)
        VALUES (?, 'Realise budgetaire 90%', 'Ligne de demonstration pour declencher une alerte budgetaire.', 1, 90000, 90000, 1)");
    $stmt->execute([$factureId]);
}

echo "Jeu de donnees budgetaire 90% cree ou mis a jour.\n";
echo "Projet ID: {$projectId}\n";
