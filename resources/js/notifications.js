/**
 * Sistema de Notificações Modernas
 * Notificações elegantes e responsivas
 */
class NotificationSystem {
    constructor() {
        this.container = null;
        this.init();
    }
    
    init() {
        this.createContainer();
        this.bindEvents();
    }
    
    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = 'fixed top-4 right-4 z-50 space-y-3';
        document.body.appendChild(this.container);
    }
    
    bindEvents() {
        // Fechar notificações ao clicar fora
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#notification-container')) {
                // Não fechar automaticamente
            }
        });
    }
    
    show(message, type = 'info', options = {}) {
        const notification = this.createNotification(message, type, options);
        this.container.appendChild(notification);
        
        // Animação de entrada
        setTimeout(() => {
            notification.classList.add('animate-fade-in');
        }, 10);
        
        // Auto-remove após delay
        const delay = options.duration || this.getDefaultDuration(type);
        if (delay > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, delay);
        }
        
        return notification;
    }
    
    createNotification(message, type, options) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} max-w-sm w-full bg-white shadow-xl rounded-xl border border-gray-200 overflow-hidden`;
        
        const icon = this.getIcon(type);
        const colors = this.getColors(type);
        
        notification.innerHTML = `
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 ${colors.bg} rounded-full flex items-center justify-center">
                            ${icon}
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium ${colors.text}">${message}</p>
                        ${options.description ? `<p class="mt-1 text-sm text-gray-500">${options.description}</p>` : ''}
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button class="notification-close bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Adicionar barra de progresso se especificado
        if (options.showProgress !== false) {
            const progressBar = document.createElement('div');
            progressBar.className = `h-1 ${colors.progress} transition-all duration-300`;
            progressBar.style.width = '100%';
            notification.appendChild(progressBar);
            
            // Animar barra de progresso
            const delay = options.duration || this.getDefaultDuration(type);
            if (delay > 0) {
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 100);
            }
        }
        
        // Event listeners
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.remove(notification));
        
        return notification;
    }
    
    getIcon(type) {
        const icons = {
            success: '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
            error: '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
            warning: '<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z" /></svg>',
            info: '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        };
        return icons[type] || icons.info;
    }
    
    getColors(type) {
        const colors = {
            success: {
                bg: 'bg-green-100',
                text: 'text-green-800',
                progress: 'bg-green-500'
            },
            error: {
                bg: 'bg-red-100',
                text: 'text-red-800',
                progress: 'bg-red-500'
            },
            warning: {
                bg: 'bg-yellow-100',
                text: 'text-yellow-800',
                progress: 'bg-yellow-500'
            },
            info: {
                bg: 'bg-blue-100',
                text: 'text-blue-800',
                progress: 'bg-blue-500'
            }
        };
        return colors[type] || colors.info;
    }
    
    getDefaultDuration(type) {
        const durations = {
            success: 4000,
            error: 6000,
            warning: 5000,
            info: 4000
        };
        return durations[type] || 4000;
    }
    
    remove(notification) {
        notification.classList.add('animate-fade-out');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    clear() {
        const notifications = this.container.querySelectorAll('.notification');
        notifications.forEach(notification => this.remove(notification));
    }
    
    // Métodos de conveniência
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }
    
    error(message, options = {}) {
        return this.show(message, 'error', options);
    }
    
    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }
    
    info(message, options = {}) {
        return this.show(message, 'info', options);
    }
}

// Auto-inicializar
document.addEventListener('DOMContentLoaded', function() {
    window.notifications = new NotificationSystem();
    
    // Interceptar mensagens do Laravel
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const message = alert.textContent.trim();
        const type = alert.classList.contains('alert-success') ? 'success' :
                    alert.classList.contains('alert-danger') ? 'error' :
                    alert.classList.contains('alert-warning') ? 'warning' : 'info';
        
        if (message) {
            window.notifications.show(message, type);
            alert.style.display = 'none';
        }
    });
    
    // Interceptar mensagens de sucesso do session
    if (window.Laravel && window.Laravel.flash) {
        Object.entries(window.Laravel.flash).forEach(([type, message]) => {
            window.notifications.show(message, type);
        });
    }
});

// Adicionar estilos CSS
const style = document.createElement('style');
style.textContent = `
    .notification {
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease-in-out;
    }
    
    .notification.animate-fade-in {
        transform: translateX(0);
        opacity: 1;
    }
    
    .notification.animate-fade-out {
        transform: translateX(100%);
        opacity: 0;
    }
    
    .notification:hover {
        transform: translateX(0) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    @media (max-width: 640px) {
        #notification-container {
            top: 1rem;
            right: 1rem;
            left: 1rem;
        }
        
        .notification {
            max-width: none;
        }
    }
`;
document.head.appendChild(style);

// Exportar para uso global
window.NotificationSystem = NotificationSystem;
