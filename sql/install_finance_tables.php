<?php
require_once __DIR__ . '/../config/database.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS factures (
        id INT PRIMARY KEY AUTO_INCREMENT,
        numero VARCHAR(50) UNIQUE NOT NULL,
        projet_id INT NOT NULL,
        client_id INT NOT NULL,
        admin_id INT NOT NULL,
        montant_total DECIMAL(15,2) DEFAULT 0,
        montant_paye DECIMAL(15,2) DEFAULT 0,
        statut ENUM('brouillon','emise','partiellement_payee','payee','annulee','en_retard') DEFAULT 'brouillon',
        date_emission DATE NOT NULL,
        date_echeance DATE NOT NULL,
        date_paiement DATE NULL,
        notes TEXT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_factures_projet (projet_id),
        INDEX idx_factures_client (client_id),
        INDEX idx_factures_admin (admin_id),
        CONSTRAINT fk_factures_projet FOREIGN KEY (projet_id) REFERENCES projets(id),
        CONSTRAINT fk_factures_client FOREIGN KEY (client_id) REFERENCES utilisateurs(id),
        CONSTRAINT fk_factures_admin FOREIGN KEY (admin_id) REFERENCES utilisateurs(id)
    )",
    "CREATE TABLE IF NOT EXISTS lignes_facture (
        id INT PRIMARY KEY AUTO_INCREMENT,
        facture_id INT NOT NULL,
        designation VARCHAR(255) NOT NULL,
        description TEXT NULL,
        quantite DECIMAL(10,2) DEFAULT 1,
        prix_unitaire DECIMAL(15,2) NOT NULL,
        montant_ligne DECIMAL(15,2) NOT NULL,
        ordre INT DEFAULT 0,
        INDEX idx_lignes_facture (facture_id),
        CONSTRAINT fk_lignes_facture FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS paiements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        facture_id INT NOT NULL,
        client_id INT NOT NULL,
        montant DECIMAL(15,2) NOT NULL,
        mode_paiement ENUM('especes','virement','cheque','mobile_money','carte','autre') DEFAULT 'virement',
        reference VARCHAR(100) NULL,
        statut ENUM('en_attente','valide','rejete','annule') DEFAULT 'en_attente',
        date_paiement DATE NOT NULL,
        commentaire TEXT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_paiements_facture (facture_id),
        INDEX idx_paiements_client (client_id),
        CONSTRAINT fk_paiements_facture FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
        CONSTRAINT fk_paiements_client FOREIGN KEY (client_id) REFERENCES utilisateurs(id)
    )",
];

foreach ($queries as $query) {
    $pdo->exec($query);
}

echo "Tables financieres installees.\n";
