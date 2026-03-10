/**
 * Sistema de Auto-save
 * Salva automaticamente formulários em intervalos regulares
 */
class AutoSave {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            interval: options.interval || 30000, // 30 segundos
            endpoint: options.endpoint || form.action,
            method: options.method || form.method || 'POST',
            showIndicator: options.showIndicator !== false,
            enableDraft: options.enableDraft !== false,
            ...options
        };
        
        this.lastSaved = null;
        this.isDirty = false;
        this.saveTimeout = null;
        this.isSaving = false;
        
        this.init();
    }
    
    init() {
        this.createIndicator();
        this.bindEvents();
        this.loadDraft();
        
        // Iniciar auto-save
        this.startAutoSave();
    }
    
    createIndicator() {
        if (!this.options.showIndicator) return;
        
        // Criar indicador de status
        this.indicator = document.createElement('div');
        this.indicator.className = 'auto-save-indicator fixed top-4 right-4 bg-white shadow-lg rounded-lg px-4 py-2 text-sm z-50 hidden';
        this.indicator.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="status-icon w-3 h-3 rounded-full bg-gray-400"></div>
                <span class="status-text">Não salvo</span>
            </div>
        `;
        
        document.body.appendChild(this.indicator);
        
        this.statusIcon = this.indicator.querySelector('.status-icon');
        this.statusText = this.indicator.querySelector('.status-text');
    }
    
    bindEvents() {
        // Detectar mudanças no formulário
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => this.markDirty());
            input.addEventListener('change', () => this.markDirty());
        });
        
        // Salvar antes de sair da página
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty && !this.isSaving) {
                this.saveDraft();
                const message = 'Você tem alterações não salvas. Deseja realmente sair?';
                e.returnValue = message;
                return message;
            }
        });
        
        // Salvar no submit do formulário
        this.form.addEventListener('submit', (e) => {
            this.clearDraft();
        });
    }
    
    markDirty() {
        this.isDirty = true;
        this.updateIndicator('dirty', 'Alterações não salvas');
        this.saveDraft();
    }
    
    startAutoSave() {
        this.saveTimeout = setInterval(() => {
            if (this.isDirty && !this.isSaving) {
                this.save();
            }
        }, this.options.interval);
    }
    
    async save() {
        if (this.isSaving) return;
        
        this.isSaving = true;
        this.updateIndicator('saving', 'Salvando...');
        
        try {
            const formData = new FormData(this.form);
            formData.append('_auto_save', '1');
            
            const response = await fetch(this.options.endpoint, {
                method: this.options.method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                this.lastSaved = new Date();
                this.isDirty = false;
                this.updateIndicator('saved', `Salvo às ${this.lastSaved.toLocaleTimeString()}`);
                this.clearDraft();
                
                // Esconder indicador após 3 segundos
                setTimeout(() => {
                    if (!this.isDirty) {
                        this.hideIndicator();
                    }
                }, 3000);
            } else {
                throw new Error('Erro ao salvar');
            }
        } catch (error) {
            console.error('Erro no auto-save:', error);
            this.updateIndicator('error', 'Erro ao salvar');
        } finally {
            this.isSaving = false;
        }
    }
    
    updateIndicator(status, text) {
        if (!this.indicator) return;
        
        this.indicator.classList.remove('hidden');
        
        // Atualizar ícone
        this.statusIcon.className = 'status-icon w-3 h-3 rounded-full';
        switch (status) {
            case 'saved':
                this.statusIcon.classList.add('bg-green-500');
                break;
            case 'saving':
                this.statusIcon.classList.add('bg-blue-500', 'animate-pulse');
                break;
            case 'dirty':
                this.statusIcon.classList.add('bg-yellow-500');
                break;
            case 'error':
                this.statusIcon.classList.add('bg-red-500');
                break;
            default:
                this.statusIcon.classList.add('bg-gray-400');
        }
        
        this.statusText.textContent = text;
    }
    
    hideIndicator() {
        if (this.indicator) {
            this.indicator.classList.add('hidden');
        }
    }
    
    saveDraft() {
        if (!this.options.enableDraft) return;
        
        const formData = new FormData(this.form);
        const draftData = {};
        
        for (let [key, value] of formData.entries()) {
            if (key !== '_token' && key !== '_method') {
                draftData[key] = value;
            }
        }
        
        const draftKey = `draft_${this.form.action}_${window.location.pathname}`;
        localStorage.setItem(draftKey, JSON.stringify({
            data: draftData,
            timestamp: new Date().toISOString()
        }));
    }
    
    loadDraft() {
        if (!this.options.enableDraft) return;
        
        const draftKey = `draft_${this.form.action}_${window.location.pathname}`;
        const draft = localStorage.getItem(draftKey);
        
        if (draft) {
            try {
                const draftData = JSON.parse(draft);
                const age = Date.now() - new Date(draftData.timestamp).getTime();
                
                // Só carregar se o draft não for muito antigo (24 horas)
                if (age < 24 * 60 * 60 * 1000) {
                    this.loadFormData(draftData.data);
                    this.updateIndicator('draft', 'Rascunho carregado');
                } else {
                    localStorage.removeItem(draftKey);
                }
            } catch (error) {
                console.error('Erro ao carregar rascunho:', error);
            }
        }
    }
    
    loadFormData(data) {
        Object.entries(data).forEach(([key, value]) => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = value === 'on' || value === '1';
                } else {
                    input.value = value;
                }
            }
        });
    }
    
    clearDraft() {
        if (!this.options.enableDraft) return;
        
        const draftKey = `draft_${this.form.action}_${window.location.pathname}`;
        localStorage.removeItem(draftKey);
    }
    
    destroy() {
        if (this.saveTimeout) {
            clearInterval(this.saveTimeout);
        }
        
        if (this.indicator && this.indicator.parentNode) {
            this.indicator.parentNode.removeChild(this.indicator);
        }
    }
}

// Auto-inicializar para formulários específicos
document.addEventListener('DOMContentLoaded', function() {
    // Formulários de criação/edição de personagens
    const characterForms = document.querySelectorAll('form[action*="characters"]');
    characterForms.forEach(form => {
        if (form.querySelector('textarea[name="description"]')) {
            new AutoSave(form, {
                interval: 30000,
                enableDraft: true
            });
        }
    });
    
    // Formulários de criação/edição de cenas
    const sceneForms = document.querySelectorAll('form[action*="scenes"]');
    sceneForms.forEach(form => {
        if (form.querySelector('textarea[name="description"]')) {
            new AutoSave(form, {
                interval: 45000,
                enableDraft: true
            });
        }
    });
    
    // Formulários de projetos
    const projectForms = document.querySelectorAll('form[action*="projects"]');
    projectForms.forEach(form => {
        new AutoSave(form, {
            interval: 60000,
            enableDraft: true
        });
    });
});

// Exportar para uso global
window.AutoSave = AutoSave;
