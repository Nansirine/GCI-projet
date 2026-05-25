<?php
require_once __DIR__ . '/../config/database.php';

$adminId = (int)$pdo->query("SELECT id FROM utilisateurs WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
if (!$adminId) {
    throw new RuntimeException('Aucun administrateur trouve pour rattacher les projets.');
}

$clients = [
    [
        'nom' => 'Kouassi',
        'prenom' => 'Aminata',
        'email' => 'client.aminata@gc-demo.local',
        'telephone' => '+225 07 10 20 30 40',
        'projet' => ['Residence Laguna', 'Construction residentielle haut standing', 'Abidjan Cocody', 85000000, '2026-02-10', '2026-09-30', 'en_cours', 64],
    ],
    [
        'nom' => 'Diallo',
        'prenom' => 'Moussa',
        'email' => 'client.moussa@gc-demo.local',
        'telephone' => '+225 05 44 18 22 11',
        'projet' => ['Centre Medical Nord', 'Extension et renovation du plateau technique', 'Bouake', 124000000, '2026-01-15', '2026-11-20', 'en_attente', 18],
    ],
    [
        'nom' => 'Traore',
        'prenom' => 'Fatou',
        'email' => 'client.fatou@gc-demo.local',
        'telephone' => '+225 01 77 66 55 44',
        'projet' => ['Immeuble Horizon', 'Immeuble R+5 avec parking et commerces', 'Yamoussoukro', 210000000, '2025-11-05', '2026-08-15', 'en_cours', 79],
    ],
    [
        'nom' => 'Mensah',
        'prenom' => 'Eric',
        'email' => 'client.eric@gc-demo.local',
        'telephone' => '+225 07 88 24 31 19',
        'projet' => ['Villa Baobab', 'Villa familiale avec amenagement exterieur', 'Grand-Bassam', 47000000, '2025-10-01', '2026-04-25', 'termine', 100],
    ],
];

$pdo->beginTransaction();

try {
    $passwordHash = password_hash('Client@2026', PASSWORD_DEFAULT);
    $findUser = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? LIMIT 1');
    $insertUser = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, statut) VALUES (?, ?, ?, ?, "client", ?, "actif")');
    $findProject = $pdo->prepare('SELECT id FROM projets WHERE nom = ? LIMIT 1');
    $insertProject = $pdo->prepare('
        INSERT INTO projets (nom, description, localisation, budget, date_debut, date_fin_prevue, statut, pourcentage_avancement, admin_id, client_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    foreach ($clients as $client) {
        $findUser->execute([$client['email']]);
        $clientId = (int)$findUser->fetchColumn();

        if (!$clientId) {
            $insertUser->execute([$client['nom'], $client['prenom'], $client['email'], $passwordHash, $client['telephone']]);
            $clientId = (int)$pdo->lastInsertId();
        }

        $project = $client['projet'];
        $findProject->execute([$project[0]]);
        if (!$findProject->fetchColumn()) {
            $insertProject->execute([$project[0], $project[1], $project[2], $project[3], $project[4], $project[5], $project[6], $project[7], $adminId, $clientId]);
        }
    }

    $pdo->commit();
    echo "Clients et projets demo crees ou deja presents.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
