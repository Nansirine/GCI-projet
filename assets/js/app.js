/**
 * GC Projet Manager - JavaScript Principal
 * Interactions et animations communes
 */

// ============================================
// SIDEBAR MOBILE TOGGLE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent');
    
    // Toggle sidebar mobile
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            
            // Créer overlay si nécessaire
            let overlay = document.querySelector('.sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
                
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                });
            }
            overlay.classList.toggle('active');
        });
    }
    
    // Toggle sidebar desktop (admin dashboard)
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
});

// ============================================
// NOTIFICATIONS DROPDOWN
// ============================================
function initNotificationsDropdown() {
    const notifIcon = document.querySelector('.navbar-icon.notifications');
    const notifDropdown = document.querySelector('.notifications-dropdown');
    
    if (notifIcon && notifDropdown) {
        notifIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });
        
        // Fermer en cliquant ailleurs
        document.addEventListener('click', function() {
            notifDropdown.classList.remove('show');
        });
        
        notifDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// ============================================
// MARQUER NOTIFICATIONS COMME LUES
// ============================================
function markNotificationAsRead(notificationId) {
    fetch('/gestion_projet/api/mark_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notifItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notifItem) {
                notifItem.classList.remove('unread');
            }
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// ============================================
// MARQUER TOUTES LES NOTIFICATIONS COMME LUES
// ============================================
function markAllNotificationsAsRead() {
    fetch('/gestion_projet/api/mark_all_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// ============================================
// METTRE À JOUR LE COMPTEUR DE NOTIFICATIONS
// ============================================
function updateNotificationCount() {
    fetch('/gestion_projet/api/notifications_count.php')
        .then(response => response.json())
        .then(data => {
            const badges = document.querySelectorAll('.navbar-icon-badge, .nav-badge');
            badges.forEach(badge => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
        })
        .catch(error => console.error('Erreur:', error));
}

// ============================================
// CONFIRMATION DE SUPPRESSION
// ============================================
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(message, type = 'info') {
    // Créer le conteneur de toasts s'il n'existe pas
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        min-width: 300px;
        padding: 1rem 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        border-left: 4px solid;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Couleurs selon le type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    toast.style.borderLeftColor = colors[type] || colors.info;
    
    // Icônes selon le type
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    toast.innerHTML = `
        <i class="bi ${icons[type] || icons.info}" style="font-size: 1.5rem; color: ${colors[type] || colors.info};"></i>
        <span style="flex: 1; color: #0f172a; font-weight: 500;">${message}</span>
        <i class="bi bi-x" style="cursor: pointer; color: #94a3b8; font-size: 1.25rem;"></i>
    `;
    
    // Ajouter au conteneur
    toastContainer.appendChild(toast);
    
    // Fermer au clic sur X
    toast.querySelector('.bi-x').addEventListener('click', () => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    });
    
    // Auto-fermeture après 5 secondes
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Ajouter les animations CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// LOADING SPINNER
// ============================================
function showLoading() {
    let loader = document.querySelector('.page-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.className = 'page-loader';
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        loader.innerHTML = `
            <div style="
                width: 60px;
                height: 60px;
                border: 4px solid rgba(59, 130, 246, 0.2);
                border-top-color: #3b82f6;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            "></div>
        `;
        document.body.appendChild(loader);
    }
    loader.style.display = 'flex';
}

function hideLoading() {
    const loader = document.querySelector('.page-loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

// Animation de rotation
const spinStyle = document.createElement('style');
spinStyle.textContent = `
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(spinStyle);

// ============================================
// RECHERCHE EN TEMPS RÉEL
// ============================================
function initLiveSearch(inputSelector, tableSelector) {
    const searchInput = document.querySelector(inputSelector);
    const table = document.querySelector(tableSelector);
    
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
}

// ============================================
// CONFIRMATIONS GLOBALES DES ACTIONS SENSIBLES
// ============================================
function initActionConfirmations() {
    document.addEventListener('click', function(e) {
        const action = e.target.closest('a, button');
        if (!action || action.dataset.noConfirm === 'true') return;

        const href = action.getAttribute('href') || '';
        const title = (action.getAttribute('title') || '').toLowerCase();
        const text = (action.textContent || '').trim().toLowerCase();
        const isBootstrapModal = action.hasAttribute('data-bs-toggle');
        const isLogout = href.includes('logout.php');
        const isEditLink = action.tagName === 'A' && (title.includes('modifier') || text.includes('modifier') || action.classList.contains('btn-action-edit'));
        const isDangerLink = action.tagName === 'A' && !isBootstrapModal && (
            title.includes('supprimer') ||
            text.includes('supprimer') ||
            action.classList.contains('btn-danger-modern') ||
            action.classList.contains('btn-action-delete')
        );

        if (!isLogout && !isEditLink && !isDangerLink) return;

        let message = 'Confirmer cette action ?';
        if (isLogout) message = 'Voulez-vous vraiment vous deconnecter ?';
        if (isEditLink) message = 'Voulez-vous modifier cet element ?';
        if (isDangerLink) message = 'Voulez-vous vraiment supprimer cet element ?';

        if (!confirm(message)) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
}

// ============================================
// COPIER DANS LE PRESSE-PAPIERS
// ============================================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copié dans le presse-papiers', 'success');
    }).catch(err => {
        showToast('Erreur lors de la copie', 'error');
    });
}

// ============================================
// FORMATER LES DATES
// ============================================
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('fr-FR', options);
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('fr-FR', options);
}

// ============================================
// VALIDATION DE FORMULAIRE
// ============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// ============================================
// INITIALISATION AU CHARGEMENT
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les dropdowns de notifications
    initNotificationsDropdown();
    initActionConfirmations();
    
    // Mettre à jour le compteur de notifications toutes les 30 secondes
    updateNotificationCount();
    setInterval(updateNotificationCount, 30000);
    
    // Initialiser les tooltips Bootstrap si disponibles
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// ============================================
// EXPORTS GLOBAUX
// ============================================
window.GCManager = {
    showToast,
    showLoading,
    hideLoading,
    confirmDelete,
    copyToClipboard,
    formatDate,
    formatDateTime,
    validateForm,
    markNotificationAsRead,
    markAllNotificationsAsRead,
    updateNotificationCount,
    initLiveSearch
};
