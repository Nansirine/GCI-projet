# 🎨 Guide de Design - GC Projet Manager

## ✅ Ce qui a été fait

### 1. **Correction des liens vers les assets**
- ✅ Tous les liens vers Bootstrap, FontAwesome et CSS personnalisés ont été corrigés
- ✅ Utilisation des fichiers locaux au lieu des CDN (sauf Chart.js et Frappe Gantt)
- ✅ Chemins absolus avec `/gestion_projet/` pour éviter les erreurs

### 2. **Fichiers CSS créés**

#### **app.css** - Styles de base
- Variables CSS modernes (couleurs, ombres, transitions)
- Typographie professionnelle avec Inter font
- Composants de base (cartes, boutons, badges, formulaires)
- Barres de progression animées
- Animations et effets

#### **admin-dashboard.css** - Dashboard administrateur
- Sidebar fixe avec navigation moderne
- Cartes de statistiques avec icônes et animations
- Tableaux stylisés
- Sections pour alertes et rapports
- Responsive complet

#### **login.css** - Page de connexion
- Design ultra-moderne avec effets de fond animés
- Formulaire élégant avec icônes
- Animations fluides
- Messages d'erreur/succès stylisés
- Mode sombre optionnel

#### **pages.css** - Pages générales (projets, utilisateurs, etc.)
- En-têtes de page avec actions
- Filtres et recherche avancée
- Tableaux modernes avec hover effects
- Badges de statut animés
- Barres de progression avec shimmer effect
- Pagination stylisée
- États vides

#### **dashboard-common.css** - Layout commun pour tous les dashboards
- Sidebar réutilisable pour tous les rôles
- Navigation avec badges de notification
- Top navbar avec recherche et icônes
- Layout responsive avec menu mobile
- Grilles de statistiques

#### **components.css** - Composants réutilisables
- Listes de tâches avec checkboxes
- Alertes avec niveaux de priorité
- Timeline pour historique
- Cartes de projet
- Cartes de membre d'équipe
- Widgets d'information
- Liste de fichiers/documents
- Dropdown de notifications

### 3. **Pages mises à jour**

✅ **login.php** - Design ultra-moderne avec animations
✅ **includes/header.php** - Liens corrigés vers tous les assets
✅ **includes/footer.php** - Script Bootstrap local
✅ **admin/dashboard.php** - Dashboard avec sidebar et statistiques
✅ **admin/projets.php** - Liste de projets avec filtres et tableau moderne

---

## 🎯 Comment appliquer le design aux autres pages

### **Structure HTML recommandée**

#### Pour les pages avec sidebar (dashboards):

```php
<?php
require_once '../includes/auth.php';
checkRole(['role']);
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/admin-dashboard.css">
<!-- OU pour les autres rôles -->
<link rel="stylesheet" href="/gestion_projet/assets/css/dashboard-common.css">

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <div class="sidebar-logo">
                    <i class="bi bi-building"></i>
                </div>
                <span class="sidebar-title">GC Manager</span>
            </a>
        </div>
        
        <div class="sidebar-user">
            <img src="<?= $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png' ?>" class="sidebar-avatar" alt="Avatar">
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= $_SESSION['prenom'] . ' ' . $_SESSION['nom'] ?></div>
                <div class="sidebar-user-role"><?= $_SESSION['role'] ?></div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="bi bi-house-door"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="projets.php" class="nav-link">
                        <i class="bi bi-folder2"></i>
                        <span>Projets</span>
                        <span class="nav-badge">3</span>
                    </a>
                </li>
                <!-- Autres liens -->
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="/gestion_projet/logout.php" class="sidebar-logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-left">
                <i class="bi bi-list menu-toggle" id="menuToggle"></i>
                <div class="navbar-breadcrumb">
                    <i class="bi bi-house"></i>
                    <span>Tableau de bord</span>
                </div>
            </div>
            <div class="navbar-right">
                <div class="navbar-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                <i class="bi bi-bell navbar-icon">
                    <span class="navbar-icon-badge">3</span>
                </i>
                <img src="<?= $_SESSION['photo'] ?? '/gestion_projet/assets/img/default-user.png' ?>" class="navbar-avatar" alt="Avatar">
            </div>
        </nav>
        
        <!-- Content Area -->
        <div class="content-area">
            <!-- Votre contenu ici -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-folder2"></i>
                    </div>
                    <div class="stat-card-label">Total Projets</div>
                    <div class="stat-card-value">12</div>
                    <div class="stat-card-trend">
                        <i class="bi bi-arrow-up"></i>
                        +2 ce mois
                    </div>
                </div>
                <!-- Autres stats -->
            </div>
            
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-folder2"></i>
                        Mes Projets
                    </h2>
                    <a href="#" class="section-action">
                        <i class="bi bi-plus"></i>
                        Nouveau
                    </a>
                </div>
                <!-- Contenu de la section -->
            </div>
        </div>
    </main>
</div>

<!-- Script pour le menu mobile -->
<script>
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>

<?php require_once '../includes/footer.php'; ?>
```

#### Pour les pages sans sidebar (listes, formulaires):

```php
<?php
require_once '../includes/auth.php';
checkRole(['role']);
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/gestion_projet/assets/css/pages.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-folder2-open"></i>
            Titre de la page
        </h1>
        <div class="page-actions">
            <a href="#" class="btn-modern btn-success-modern">
                <i class="bi bi-plus-circle"></i>
                Nouveau
            </a>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters-section">
        <form class="filters-row" method="get">
            <div class="filter-group">
                <label class="filter-label">Rechercher</label>
                <input type="text" class="filter-input" name="search" placeholder="Rechercher...">
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    Filtrer
                </button>
            </div>
        </form>
    </div>
    
    <!-- Tableau -->
    <div class="table-container">
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Colonne 1</th>
                        <th>Colonne 2</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Donnée 1</td>
                        <td>Donnée 2</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-action-view">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-action btn-action-edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action btn-action-delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
```

---

## 🎨 Classes CSS disponibles

### **Boutons**
- `.btn-modern` - Bouton de base
- `.btn-primary-modern` - Bouton bleu principal
- `.btn-success-modern` - Bouton vert succès
- `.btn-danger-modern` - Bouton rouge danger
- `.btn-outline-modern` - Bouton avec bordure

### **Badges de statut**
- `.status-badge.status-en-cours` - En cours (bleu)
- `.status-badge.status-termine` - Terminé (vert)
- `.status-badge.status-suspendu` - Suspendu (orange)
- `.status-badge.status-en-attente` - En attente (gris)
- `.status-badge.status-annule` - Annulé (rouge)

### **Cartes**
- `.stat-card` - Carte de statistique
- `.section-card` - Carte de section
- `.project-card` - Carte de projet
- `.card-modern` - Carte générique

### **Formulaires**
- `.form-control-modern` - Input moderne
- `.form-label-modern` - Label de formulaire
- `.filter-input` - Input de filtre
- `.filter-select` - Select de filtre

### **Tableaux**
- `.modern-table` - Tableau moderne
- `.table-container` - Conteneur de tableau

### **Composants**
- `.task-list` / `.task-item` - Liste de tâches
- `.alert-list` / `.alert-item` - Liste d'alertes
- `.timeline` / `.timeline-item` - Timeline
- `.member-card` - Carte de membre
- `.file-list` / `.file-item` - Liste de fichiers

---

## 🎯 Pages à mettre à jour

### **Admin**
- ✅ dashboard.php
- ✅ projets.php
- ⏳ projet_create.php
- ⏳ projet_detail.php
- ⏳ taches.php
- ⏳ tache_create.php
- ⏳ utilisateurs.php
- ⏳ rapports.php
- ⏳ rapport_detail.php
- ⏳ statistiques.php
- ⏳ notifications.php

### **Ingénieur**
- ⏳ dashboard.php
- ⏳ taches.php
- ⏳ tache_detail.php
- ⏳ rapports.php
- ⏳ rapport_create.php
- ⏳ alertes.php
- ⏳ documents.php
- ⏳ messages.php

### **Dessinateur**
- ⏳ dashboard.php
- ⏳ plans.php
- ⏳ plan_detail.php
- ⏳ plan_upload.php
- ⏳ taches.php
- ⏳ messages.php
- ⏳ notifications.php

### **Client**
- ⏳ dashboard.php
- ⏳ avancement.php
- ⏳ planning.php
- ⏳ plans.php
- ⏳ demandes.php
- ⏳ rapports.php
- ⏳ notifications.php

---

## 📱 Responsive

Tous les designs sont **100% responsive** avec des breakpoints à:
- **1024px** - Tablettes (sidebar devient mobile)
- **768px** - Mobiles (layout simplifié)

Le menu mobile s'active automatiquement avec un bouton hamburger.

---

## 🚀 Prochaines étapes

1. Appliquer le design aux autres pages admin
2. Créer les dashboards pour ingénieur, dessinateur et client
3. Ajouter les interactions JavaScript (modals, dropdowns, etc.)
4. Intégrer les données dynamiques depuis la base de données
5. Tester sur différents navigateurs et appareils

---

## 💡 Conseils

- Toujours utiliser les classes CSS existantes avant d'en créer de nouvelles
- Respecter la palette de couleurs définie dans `app.css`
- Utiliser les icônes Bootstrap Icons pour la cohérence
- Tester le responsive sur mobile après chaque modification
- Garder le code HTML propre et bien indenté

---

**Design créé par Kiro AI** 🎨✨
