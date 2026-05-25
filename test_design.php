<?php
// Page de test pour visualiser tous les composants du design
session_start();
// Simuler une session pour le test
$_SESSION['user_id'] = 1;
$_SESSION['nom'] = 'Dupont';
$_SESSION['prenom'] = 'Jean';
$_SESSION['role'] = 'admin';
$_SESSION['photo'] = '/gestion_projet/assets/img/default-user.png';

require_once 'includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/dashboard-common.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">
<link rel="stylesheet" href="/gestion_projet/assets/css/components.css">

<style>
    .test-section {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
    }
    .test-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .component-demo {
        margin-bottom: 2rem;
    }
    .component-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-palette"></i>
            Test des Composants Design
        </h1>
        <div class="page-actions">
            <button class="btn-modern btn-primary-modern" onclick="GCManager.showToast('Test réussi!', 'success')">
                <i class="bi bi-check-circle"></i>
                Tester Toast
            </button>
        </div>
    </div>

    <!-- BOUTONS -->
    <div class="test-section">
        <h2 class="test-title">Boutons</h2>
        <div class="component-demo">
            <div class="component-label">Boutons modernes</div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn-modern btn-primary-modern">
                    <i class="bi bi-plus"></i>
                    Primary
                </button>
                <button class="btn-modern btn-success-modern">
                    <i class="bi bi-check"></i>
                    Success
                </button>
                <button class="btn-modern btn-danger-modern">
                    <i class="bi bi-trash"></i>
                    Danger
                </button>
                <button class="btn-modern btn-outline-modern">
                    <i class="bi bi-pencil"></i>
                    Outline
                </button>
            </div>
        </div>
    </div>

    <!-- BADGES -->
    <div class="test-section">
        <h2 class="test-title">Badges de Statut</h2>
        <div class="component-demo">
            <div class="component-label">Statuts de projet</div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <span class="status-badge status-en-cours">En cours</span>
                <span class="status-badge status-termine">Terminé</span>
                <span class="status-badge status-suspendu">Suspendu</span>
                <span class="status-badge status-en-attente">En attente</span>
                <span class="status-badge status-annule">Annulé</span>
            </div>
        </div>
        <div class="component-demo">
            <div class="component-label">Priorités de tâche</div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <span class="task-priority high">Haute</span>
                <span class="task-priority medium">Moyenne</span>
                <span class="task-priority low">Basse</span>
            </div>
        </div>
    </div>

    <!-- CARTES DE STATISTIQUES -->
    <div class="test-section">
        <h2 class="test-title">Cartes de Statistiques</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon primary">
                    <i class="bi bi-folder2"></i>
                </div>
                <div class="stat-card-label">Total Projets</div>
                <div class="stat-card-value">24</div>
                <div class="stat-card-trend">
                    <i class="bi bi-arrow-up"></i>
                    +12% ce mois
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-card-label">Tâches Complétées</div>
                <div class="stat-card-value">156</div>
                <div class="stat-card-trend">
                    <i class="bi bi-arrow-up"></i>
                    +8% ce mois
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon warning">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stat-card-label">En Attente</div>
                <div class="stat-card-value">12</div>
                <div class="stat-card-trend" style="color: #f59e0b;">
                    <i class="bi bi-dash"></i>
                    Stable
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-card-label">Alertes</div>
                <div class="stat-card-value">3</div>
                <div class="stat-card-trend" style="color: #ef4444;">
                    <i class="bi bi-arrow-down"></i>
                    -2 aujourd'hui
                </div>
            </div>
        </div>
    </div>

    <!-- BARRES DE PROGRESSION -->
    <div class="test-section">
        <h2 class="test-title">Barres de Progression</h2>
        <div class="component-demo">
            <div class="component-label">Progression standard</div>
            <div class="progress-cell">
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: 75%"></div>
                </div>
                <span class="progress-text">75%</span>
            </div>
        </div>
        <div class="component-demo">
            <div class="component-label">Progression avec couleurs</div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="progress-cell">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 90%; background: linear-gradient(90deg, #10b981 0%, #059669 100%)"></div>
                    </div>
                    <span class="progress-text">90%</span>
                </div>
                <div class="progress-cell">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 50%; background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%)"></div>
                    </div>
                    <span class="progress-text">50%</span>
                </div>
                <div class="progress-cell">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 25%; background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%)"></div>
                    </div>
                    <span class="progress-text">25%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTE DE TÂCHES -->
    <div class="test-section">
        <h2 class="test-title">Liste de Tâches</h2>
        <ul class="task-list">
            <li class="task-item">
                <input type="checkbox" class="task-checkbox">
                <div class="task-content">
                    <div class="task-title">Finaliser les plans d'architecture</div>
                    <div class="task-meta">
                        <span class="task-meta-item">
                            <i class="bi bi-calendar"></i>
                            25 Mai 2026
                        </span>
                        <span class="task-meta-item">
                            <i class="bi bi-person"></i>
                            Jean Dupont
                        </span>
                    </div>
                </div>
                <span class="task-priority high">Haute</span>
            </li>
            <li class="task-item">
                <input type="checkbox" class="task-checkbox" checked>
                <div class="task-content">
                    <div class="task-title">Révision des documents techniques</div>
                    <div class="task-meta">
                        <span class="task-meta-item">
                            <i class="bi bi-calendar"></i>
                            20 Mai 2026
                        </span>
                        <span class="task-meta-item">
                            <i class="bi bi-person"></i>
                            Marie Martin
                        </span>
                    </div>
                </div>
                <span class="task-priority medium">Moyenne</span>
            </li>
            <li class="task-item">
                <input type="checkbox" class="task-checkbox">
                <div class="task-content">
                    <div class="task-title">Préparation de la réunion client</div>
                    <div class="task-meta">
                        <span class="task-meta-item">
                            <i class="bi bi-calendar"></i>
                            28 Mai 2026
                        </span>
                        <span class="task-meta-item">
                            <i class="bi bi-person"></i>
                            Pierre Dubois
                        </span>
                    </div>
                </div>
                <span class="task-priority low">Basse</span>
            </li>
        </ul>
    </div>

    <!-- ALERTES -->
    <div class="test-section">
        <h2 class="test-title">Alertes</h2>
        <ul class="alert-list">
            <li class="alert-item critical">
                <div class="alert-icon">
                    <i class="bi bi-x-octagon-fill"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Incident critique sur le chantier</div>
                    <div class="alert-description">Un problème de sécurité a été détecté sur le site A</div>
                    <div class="alert-meta">
                        <span><i class="bi bi-clock"></i> Il y a 2 heures</span>
                        <span><i class="bi bi-geo-alt"></i> Chantier A</span>
                    </div>
                </div>
            </li>
            <li class="alert-item warning">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Retard de livraison matériaux</div>
                    <div class="alert-description">Les matériaux pour le projet B sont en retard</div>
                    <div class="alert-meta">
                        <span><i class="bi bi-clock"></i> Il y a 1 jour</span>
                        <span><i class="bi bi-box"></i> Projet B</span>
                    </div>
                </div>
            </li>
            <li class="alert-item info">
                <div class="alert-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Nouveau rapport disponible</div>
                    <div class="alert-description">Le rapport mensuel d'avancement est prêt</div>
                    <div class="alert-meta">
                        <span><i class="bi bi-clock"></i> Il y a 3 jours</span>
                        <span><i class="bi bi-file-text"></i> Rapports</span>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <!-- FORMULAIRES -->
    <div class="test-section">
        <h2 class="test-title">Formulaires</h2>
        <div class="component-demo">
            <form style="max-width: 600px;">
                <div class="form-group-modern">
                    <label class="form-label-modern">Nom du projet</label>
                    <input type="text" class="form-control-modern" placeholder="Entrez le nom du projet">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">Description</label>
                    <textarea class="form-control-modern" rows="4" placeholder="Description du projet"></textarea>
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern">Statut</label>
                    <select class="form-control-modern">
                        <option>En cours</option>
                        <option>Terminé</option>
                        <option>Suspendu</option>
                    </select>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="bi bi-check"></i>
                        Enregistrer
                    </button>
                    <button type="button" class="btn-modern btn-outline-modern">
                        <i class="bi bi-x"></i>
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BOUTONS D'ACTION -->
    <div class="test-section">
        <h2 class="test-title">Boutons d'Action</h2>
        <div class="component-demo">
            <div class="component-label">Actions de tableau</div>
            <div class="action-buttons">
                <button class="btn-action btn-action-view" title="Voir">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn-action btn-action-edit" title="Modifier">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn-action btn-action-delete" title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- TESTS JAVASCRIPT -->
    <div class="test-section">
        <h2 class="test-title">Tests JavaScript</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button class="btn-modern btn-success-modern" onclick="GCManager.showToast('Opération réussie!', 'success')">
                <i class="bi bi-check-circle"></i>
                Toast Success
            </button>
            <button class="btn-modern btn-danger-modern" onclick="GCManager.showToast('Une erreur est survenue', 'error')">
                <i class="bi bi-x-circle"></i>
                Toast Error
            </button>
            <button class="btn-modern btn-outline-modern" onclick="GCManager.showToast('Attention!', 'warning')">
                <i class="bi bi-exclamation-triangle"></i>
                Toast Warning
            </button>
            <button class="btn-modern btn-primary-modern" onclick="GCManager.showToast('Information', 'info')">
                <i class="bi bi-info-circle"></i>
                Toast Info
            </button>
            <button class="btn-modern btn-outline-modern" onclick="GCManager.showLoading(); setTimeout(() => GCManager.hideLoading(), 2000)">
                <i class="bi bi-hourglass"></i>
                Test Loading
            </button>
            <button class="btn-modern btn-outline-modern" onclick="GCManager.copyToClipboard('Texte copié!')">
                <i class="bi bi-clipboard"></i>
                Test Copier
            </button>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
