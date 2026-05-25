-- TABLE UTILISATEURS
CREATE TABLE utilisateurs (
	id INT PRIMARY KEY AUTO_INCREMENT,
	nom VARCHAR(100) NOT NULL,
	prenom VARCHAR(100) NOT NULL,
	email VARCHAR(150) UNIQUE NOT NULL,
	mot_de_passe VARCHAR(255) NOT NULL,
	role ENUM('admin','ingenieur','dessinateur','client') NOT NULL,
	telephone VARCHAR(20),
	photo VARCHAR(255),
	statut ENUM('actif','inactif') DEFAULT 'actif',
	date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	derniere_connexion TIMESTAMP NULL
);

-- TABLE PROJETS
CREATE TABLE projets (
	id INT PRIMARY KEY AUTO_INCREMENT,
	nom VARCHAR(200) NOT NULL,
	description TEXT,
	localisation VARCHAR(255),
	budget DECIMAL(15,2),
	date_debut DATE NOT NULL,
	date_fin_prevue DATE NOT NULL,
	date_fin_reelle DATE NULL,
	statut ENUM('en_attente','en_cours','suspendu','termine','annule') DEFAULT 'en_attente',
	pourcentage_avancement INT DEFAULT 0,
	admin_id INT NOT NULL,
	client_id INT NOT NULL,
	date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (admin_id) REFERENCES utilisateurs(id),
	FOREIGN KEY (client_id) REFERENCES utilisateurs(id)
);

-- TABLE AFFECTATIONS (membres d'un projet)
CREATE TABLE affectations (
	id INT PRIMARY KEY AUTO_INCREMENT,
	projet_id INT NOT NULL,
	utilisateur_id INT NOT NULL,
	role_projet VARCHAR(100),
	date_affectation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
	FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- TABLE TACHES
CREATE TABLE taches (
	id INT PRIMARY KEY AUTO_INCREMENT,
	projet_id INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	description TEXT,
	assigne_a INT NOT NULL,
	cree_par INT NOT NULL,
	priorite ENUM('basse','moyenne','haute','urgente') DEFAULT 'moyenne',
	statut ENUM('a_faire','en_cours','en_revision','termine','bloque') DEFAULT 'a_faire',
	pourcentage INT DEFAULT 0,
	date_debut DATE,
	date_echeance DATE NOT NULL,
	date_completion DATE NULL,
	date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
	FOREIGN KEY (assigne_a) REFERENCES utilisateurs(id),
	FOREIGN KEY (cree_par) REFERENCES utilisateurs(id)
);

-- TABLE RAPPORTS
CREATE TABLE rapports (
	id INT PRIMARY KEY AUTO_INCREMENT,
	projet_id INT NOT NULL,
	tache_id INT NULL,
	ingenieur_id INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	contenu TEXT NOT NULL,
	fichier_joint VARCHAR(255) NULL,
	statut ENUM('soumis','valide','rejete') DEFAULT 'soumis',
	commentaire_admin TEXT NULL,
	date_soumission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	date_validation TIMESTAMP NULL,
	FOREIGN KEY (projet_id) REFERENCES projets(id),
	FOREIGN KEY (ingenieur_id) REFERENCES utilisateurs(id)
);

-- TABLE PLANS (documents dessinateur)
CREATE TABLE plans (
	id INT PRIMARY KEY AUTO_INCREMENT,
	projet_id INT NOT NULL,
	dessinateur_id INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	description TEXT,
	type_plan ENUM('architectural','structural','electrique','plomberie','autre') DEFAULT 'autre',
	fichier VARCHAR(255) NOT NULL,
	version INT DEFAULT 1,
	statut ENUM('brouillon','soumis','valide','rejete','archive') DEFAULT 'brouillon',
	partage_client TINYINT(1) DEFAULT 0,
	commentaire TEXT NULL,
	date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (projet_id) REFERENCES projets(id),
	FOREIGN KEY (dessinateur_id) REFERENCES utilisateurs(id)
);

-- TABLE VERSIONS PLANS
CREATE TABLE plan_versions (
	id INT PRIMARY KEY AUTO_INCREMENT,
	plan_id INT NOT NULL,
	version INT NOT NULL,
	fichier VARCHAR(255) NOT NULL,
	commentaire TEXT,
	date_version TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
);

-- TABLE ALERTES / PROBLÈMES
CREATE TABLE alertes (
	id INT PRIMARY KEY AUTO_INCREMENT,
	projet_id INT NOT NULL,
	tache_id INT NULL,
	signale_par INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	description TEXT NOT NULL,
	niveau ENUM('info','avertissement','critique') DEFAULT 'info',
	statut ENUM('ouvert','en_traitement','resolu') DEFAULT 'ouvert',
	date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	date_resolution TIMESTAMP NULL,
	FOREIGN KEY (projet_id) REFERENCES projets(id),
	FOREIGN KEY (signale_par) REFERENCES utilisateurs(id)
);

-- TABLE MESSAGES
CREATE TABLE messages (
	id INT PRIMARY KEY AUTO_INCREMENT,
	expediteur_id INT NOT NULL,
	destinataire_id INT NOT NULL,
	projet_id INT NULL,
	sujet VARCHAR(200),
	contenu TEXT NOT NULL,
	lu TINYINT(1) DEFAULT 0,
	date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id),
	FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id)
);

-- TABLE NOTIFICATIONS
CREATE TABLE notifications (
	id INT PRIMARY KEY AUTO_INCREMENT,
	utilisateur_id INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	message TEXT NOT NULL,
	type ENUM('info','succes','avertissement','erreur') DEFAULT 'info',
	lien VARCHAR(255) NULL,
	lu TINYINT(1) DEFAULT 0,
	date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- TABLE DEMANDES CLIENT
CREATE TABLE demandes (
	id INT PRIMARY KEY AUTO_INCREMENT,
	client_id INT NOT NULL,
	projet_id INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	description TEXT NOT NULL,
	statut ENUM('en_attente','en_cours','traite','refuse') DEFAULT 'en_attente',
	reponse TEXT NULL,
	date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	date_reponse TIMESTAMP NULL,
	FOREIGN KEY (client_id) REFERENCES utilisateurs(id),
	FOREIGN KEY (projet_id) REFERENCES projets(id)
);

-- TABLE JALONS
CREATE TABLE jalons (
	id INT PRIMARY KEY AUTO_INCREMENT,
	projet_id INT NOT NULL,
	titre VARCHAR(200) NOT NULL,
	description TEXT,
	date_prevue DATE NOT NULL,
	date_reelle DATE NULL,
	statut ENUM('a_venir','atteint','manque') DEFAULT 'a_venir',
	FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- TABLE FACTURES
CREATE TABLE factures (
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
	FOREIGN KEY (projet_id) REFERENCES projets(id),
	FOREIGN KEY (client_id) REFERENCES utilisateurs(id),
	FOREIGN KEY (admin_id) REFERENCES utilisateurs(id)
);

-- TABLE LIGNES FACTURE
CREATE TABLE lignes_facture (
	id INT PRIMARY KEY AUTO_INCREMENT,
	facture_id INT NOT NULL,
	designation VARCHAR(255) NOT NULL,
	description TEXT NULL,
	quantite DECIMAL(10,2) DEFAULT 1,
	prix_unitaire DECIMAL(15,2) NOT NULL,
	montant_ligne DECIMAL(15,2) NOT NULL,
	ordre INT DEFAULT 0,
	FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE
);

-- TABLE PAIEMENTS
CREATE TABLE paiements (
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
	FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
	FOREIGN KEY (client_id) REFERENCES utilisateurs(id)
);

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Dupont', 'Jean', 'admin@gc.com', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'admin'),
('Martin', 'Paul', 'ingenieur@gc.com', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'ingenieur'),
('Bernard', 'Alice', 'dessinateur@gc.com', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'dessinateur'),
('Client', 'Marc', 'client@gc.com', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'client');

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, statut) VALUES
('Kouassi', 'Aminata', 'client.aminata@gc-demo.local', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'client', '+225 07 10 20 30 40', 'actif'),
('Diallo', 'Moussa', 'client.moussa@gc-demo.local', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'client', '+225 05 44 18 22 11', 'actif'),
('Traore', 'Fatou', 'client.fatou@gc-demo.local', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'client', '+225 01 77 66 55 44', 'actif'),
('Mensah', 'Eric', 'client.eric@gc-demo.local', '$2y$10$zfXZcIkuzM5AbcGnjaUzR.QL0bCyDRTpcVnNcJQVRJaOnoamJHrCa', 'client', '+225 07 88 24 31 19', 'actif');

INSERT INTO projets (nom, description, localisation, budget, date_debut, date_fin_prevue, statut, pourcentage_avancement, admin_id, client_id) VALUES
('Residence Laguna', 'Construction residentielle haut standing', 'Abidjan Cocody', 85000000, '2026-02-10', '2026-09-30', 'en_cours', 64, (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), (SELECT id FROM utilisateurs WHERE email = 'client.aminata@gc-demo.local')),
('Centre Medical Nord', 'Extension et renovation du plateau technique', 'Bouake', 124000000, '2026-01-15', '2026-11-20', 'en_attente', 18, (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), (SELECT id FROM utilisateurs WHERE email = 'client.moussa@gc-demo.local')),
('Immeuble Horizon', 'Immeuble R+5 avec parking et commerces', 'Yamoussoukro', 210000000, '2025-11-05', '2026-08-15', 'en_cours', 79, (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), (SELECT id FROM utilisateurs WHERE email = 'client.fatou@gc-demo.local')),
('Villa Baobab', 'Villa familiale avec amenagement exterieur', 'Grand-Bassam', 47000000, '2025-10-01', '2026-04-25', 'termine', 100, (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), (SELECT id FROM utilisateurs WHERE email = 'client.eric@gc-demo.local'));

INSERT INTO factures (numero, projet_id, client_id, admin_id, montant_total, montant_paye, statut, date_emission, date_echeance, date_paiement, notes) VALUES
('FAC-2024-001', (SELECT id FROM projets WHERE nom = 'Villa Baobab'), (SELECT id FROM utilisateurs WHERE email = 'client.eric@gc-demo.local'), (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), 32000000, 32000000, 'payee', '2024-12-12', '2025-01-12', '2025-01-08', 'Acompte et solde regles pour le projet Villa Baobab.'),
('FAC-2025-001', (SELECT id FROM projets WHERE nom = 'Immeuble Horizon'), (SELECT id FROM utilisateurs WHERE email = 'client.fatou@gc-demo.local'), (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), 78500000, 54000000, 'partiellement_payee', '2025-12-05', '2026-01-15', NULL, 'Paiements partiels associes manuellement au projet Immeuble Horizon.'),
('FAC-2026-001', (SELECT id FROM projets WHERE nom = 'Residence Laguna'), (SELECT id FROM utilisateurs WHERE email = 'client.aminata@gc-demo.local'), (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), 42500000, 42500000, 'payee', '2026-03-18', '2026-04-18', '2026-04-10', 'Situation de travaux reglee pour Residence Laguna.'),
('FAC-2026-002', (SELECT id FROM projets WHERE nom = 'Centre Medical Nord'), (SELECT id FROM utilisateurs WHERE email = 'client.moussa@gc-demo.local'), (SELECT id FROM utilisateurs WHERE email = 'admin@gc.com'), 26000000, 0, 'emise', '2026-05-12', '2026-06-12', NULL, 'Facture emise en attente de paiement.');

INSERT INTO lignes_facture (facture_id, designation, description, quantite, prix_unitaire, montant_ligne, ordre) VALUES
((SELECT id FROM factures WHERE numero = 'FAC-2024-001'), 'Travaux gros oeuvre', 'Solde travaux Villa Baobab', 1, 32000000, 32000000, 1),
((SELECT id FROM factures WHERE numero = 'FAC-2025-001'), 'Avancement structure', 'Situation intermediaire Immeuble Horizon', 1, 78500000, 78500000, 1),
((SELECT id FROM factures WHERE numero = 'FAC-2026-001'), 'Situation mensuelle', 'Avancement Residence Laguna', 1, 42500000, 42500000, 1),
((SELECT id FROM factures WHERE numero = 'FAC-2026-002'), 'Etudes et preparation', 'Demarrage Centre Medical Nord', 1, 26000000, 26000000, 1);

INSERT INTO paiements (facture_id, client_id, montant, mode_paiement, reference, statut, date_paiement, commentaire) VALUES
((SELECT id FROM factures WHERE numero = 'FAC-2024-001'), (SELECT id FROM utilisateurs WHERE email = 'client.eric@gc-demo.local'), 32000000, 'virement', 'VIR-BAOBAB-20250108', 'valide', '2025-01-08', 'Paiement manuel associe au projet Villa Baobab.'),
((SELECT id FROM factures WHERE numero = 'FAC-2025-001'), (SELECT id FROM utilisateurs WHERE email = 'client.fatou@gc-demo.local'), 30000000, 'cheque', 'CHQ-HORIZON-20260120', 'valide', '2026-01-20', 'Premier paiement manuel pour Immeuble Horizon.'),
((SELECT id FROM factures WHERE numero = 'FAC-2025-001'), (SELECT id FROM utilisateurs WHERE email = 'client.fatou@gc-demo.local'), 24000000, 'virement', 'VIR-HORIZON-20260315', 'valide', '2026-03-15', 'Deuxieme paiement manuel pour Immeuble Horizon.'),
((SELECT id FROM factures WHERE numero = 'FAC-2026-001'), (SELECT id FROM utilisateurs WHERE email = 'client.aminata@gc-demo.local'), 42500000, 'mobile_money', 'MM-LAGUNA-20260410', 'valide', '2026-04-10', 'Paiement manuel associe a Residence Laguna.'),
((SELECT id FROM factures WHERE numero = 'FAC-2026-002'), (SELECT id FROM utilisateurs WHERE email = 'client.moussa@gc-demo.local'), 8000000, 'virement', 'VIR-CMN-20260520', 'en_attente', '2026-05-20', 'Paiement en attente pour tester le rendu.');
