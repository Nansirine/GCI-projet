<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/layout.php';

$statuts = ['soumis', 'valide', 'rejete'];
$statut = $_GET['statut'] ?? '';
$projetId = (int)($_GET['projet_id'] ?? 0);
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if ($statut !== '' && in_array($statut, $statuts, true)) {
    $where[] = 'r.statut = :statut';
    $params[':statut'] = $statut;
}

if ($projetId > 0) {
    $where[] = 'r.projet_id = :projet_id';
    $params[':projet_id'] = $projetId;
}

if ($search !== '') {
    $where[] = '(r.titre LIKE :search OR p.nom LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$sql = "SELECT r.id, r.titre, r.statut, r.date_soumission, r.fichier_joint,
               p.nom AS projet_nom,
               u.nom AS ingenieur_nom, u.prenom AS ingenieur_prenom
        FROM rapports r
        JOIN projets p ON p.id = r.projet_id
        JOIN utilisateurs u ON u.id = r.ingenieur_id";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY r.date_soumission DESC, r.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rapports = $stmt->fetchAll();

$projets = $pdo->query('SELECT id, nom FROM projets ORDER BY nom')->fetchAll();
?>
<?php renderAppLayoutStart('rapports', 'bi-file-earmark-text', 'Rapports'); ?>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-file-earmark-text"></i> Rapports</h1>
            <p class="page-subtitle">Validation et suivi des rapports transmis par les ingenieurs.</p>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Rapport mis a jour avec succes.</div>
    <?php endif; ?>

    <div class="filters-section mb-3">
        <form class="filters-row" method="get">
            <div class="filter-group">
                <label class="filter-label" for="search">Recherche</label>
                <input class="filter-input" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Titre, projet, ingenieur">
            </div>
            <div class="filter-group">
                <label class="filter-label" for="statut">Statut</label>
                <select class="filter-select" id="statut" name="statut">
                    <option value="">Tous</option>
                    <?php foreach ($statuts as $value): ?>
                        <option value="<?= $value ?>" <?= $statut === $value ? 'selected' : '' ?>>
                            <?= strip_tags(getBadgeStatut($value)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="projet_id">Projet</label>
                <select class="filter-select" id="projet_id" name="projet_id">
                    <option value="0">Tous les projets</option>
                    <?php foreach ($projets as $projet): ?>
                        <option value="<?= (int)$projet['id'] ?>" <?= $projetId === (int)$projet['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($projet['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn-modern btn-primary-modern" type="submit"><i class="bi bi-search"></i> Filtrer</button>
        </form>
    </div>

    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Projet</th>
                        <th>Ingenieur</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Document</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rapports): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun rapport trouve.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rapports as $rapport): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($rapport['titre']) ?></td>
                            <td><?= htmlspecialchars($rapport['projet_nom']) ?></td>
                            <td><?= htmlspecialchars(trim($rapport['ingenieur_prenom'] . ' ' . $rapport['ingenieur_nom'])) ?></td>
                            <td><?= formatDatetime($rapport['date_soumission']) ?></td>
                            <td><?= getBadgeStatut($rapport['statut']) ?></td>
                            <td><?= renderDocumentActions('rapport', (int)$rapport['id'], $rapport['fichier_joint'], 'Rapport') ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="rapport_detail.php?id=<?= (int)$rapport['id'] ?>" class="btn-action btn-action-view" title="Voir"><i class="bi bi-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php renderAppLayoutEnd(); ?>
<?php require_once '../includes/footer.php'; ?>
