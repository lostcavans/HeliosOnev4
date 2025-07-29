// Funciones globales para todo el sistema

document.addEventListener('DOMContentLoaded', function() {
    // Controlar sidebar colapsable
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            // Guardar preferencia en localStorage
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }
    
    // Verificar preferencia de sidebar al cargar
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
    
    // Manejar notificaciones
    const notificationBell = document.querySelector('.notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationPanel = document.querySelector('.notification-panel');
            if (notificationPanel) {
                notificationPanel.classList.toggle('show');
            }
        });
    }
    
    // Cerrar notificaciones al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-container')) {
            const notificationPanel = document.querySelector('.notification-panel');
            if (notificationPanel && notificationPanel.classList.contains('show')) {
                notificationPanel.classList.remove('show');
            }
        }
    });
    
    // Confirmación antes de acciones importantes
    const confirmActions = document.querySelectorAll('[data-confirm]');
    confirmActions.forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Estás seguro de realizar esta acción?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// Función para mostrar mensajes toast
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast show ${type}`;
    toast.innerHTML = message;
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// Función para cargar contenido dinámico
function loadContent(url, containerId, callback) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '<div class="loading">Cargando...</div>';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            if (callback && typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => {
            container.innerHTML = `<div class="error">Error al cargar el contenido: ${error.message}</div>`;
        });
}