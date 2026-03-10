/**
 * Sistema de Contador de Palavras
 * Conta palavras em tempo real para campos de texto
 */
class WordCounter {
    constructor(textarea, options = {}) {
        this.textarea = textarea;
        this.options = {
            minWords: options.minWords || 0,
            maxWords: options.maxWords || null,
            showProgress: options.showProgress || false,
            warningThreshold: options.warningThreshold || 0.8,
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.createCounter();
        this.bindEvents();
        this.update();
    }
    
    createCounter() {
        // Criar container do contador
        this.counterContainer = document.createElement('div');
        this.counterContainer.className = 'word-counter-container flex justify-between items-center text-sm mt-1';
        
        // Contador de palavras
        this.wordCount = document.createElement('span');
        this.wordCount.className = 'word-count text-gray-500';
        
        // Contador de caracteres
        this.charCount = document.createElement('span');
        this.charCount.className = 'char-count text-gray-400';
        
        // Barra de progresso (opcional)
        if (this.options.showProgress && this.options.maxWords) {
            this.progressBar = document.createElement('div');
            this.progressBar.className = 'progress-bar w-full bg-gray-200 rounded-full h-1 mt-1';
            this.progressFill = document.createElement('div');
            this.progressFill.className = 'progress-fill bg-blue-500 h-1 rounded-full transition-all duration-300';
            this.progressBar.appendChild(this.progressFill);
        }
        
        this.counterContainer.appendChild(this.wordCount);
        this.counterContainer.appendChild(this.charCount);
        
        // Inserir após o textarea
        this.textarea.parentNode.insertBefore(this.counterContainer, this.textarea.nextSibling);
        
        if (this.options.showProgress && this.options.maxWords) {
            this.counterContainer.appendChild(this.progressBar);
        }
    }
    
    bindEvents() {
        this.textarea.addEventListener('input', () => this.update());
        this.textarea.addEventListener('paste', () => {
            setTimeout(() => this.update(), 10);
        });
    }
    
    update() {
        const text = this.textarea.value;
        const words = this.countWords(text);
        const chars = text.length;
        
        // Atualizar contadores
        this.wordCount.textContent = `${words} palavra${words !== 1 ? 's' : ''}`;
        this.charCount.textContent = `${chars} caractere${chars !== 1 ? 's' : ''}`;
        
        // Atualizar barra de progresso
        if (this.options.showProgress && this.options.maxWords) {
            const progress = Math.min(words / this.options.maxWords, 1);
            this.progressFill.style.width = `${progress * 100}%`;
            
            // Mudar cor baseado no progresso
            if (progress >= this.options.warningThreshold) {
                this.progressFill.className = 'progress-fill bg-yellow-500 h-1 rounded-full transition-all duration-300';
            } else if (progress >= 1) {
                this.progressFill.className = 'progress-fill bg-red-500 h-1 rounded-full transition-all duration-300';
            } else {
                this.progressFill.className = 'progress-fill bg-blue-500 h-1 rounded-full transition-all duration-300';
            }
        }
        
        // Adicionar classes de validação
        this.updateValidationClasses(words);
    }
    
    countWords(text) {
        if (!text.trim()) return 0;
        return text.trim().split(/\s+/).filter(word => word.length > 0).length;
    }
    
    updateValidationClasses(words) {
        // Remover classes anteriores
        this.textarea.classList.remove('border-red-300', 'border-yellow-300', 'border-green-300');
        this.wordCount.classList.remove('text-red-500', 'text-yellow-500', 'text-green-500');
        
        if (words < this.options.minWords) {
            this.textarea.classList.add('border-red-300');
            this.wordCount.classList.add('text-red-500');
        } else if (this.options.maxWords && words > this.options.maxWords) {
            this.textarea.classList.add('border-red-300');
            this.wordCount.classList.add('text-red-500');
        } else if (this.options.maxWords && words > this.options.maxWords * this.options.warningThreshold) {
            this.textarea.classList.add('border-yellow-300');
            this.wordCount.classList.add('text-yellow-500');
        } else if (words >= this.options.minWords) {
            this.textarea.classList.add('border-green-300');
            this.wordCount.classList.add('text-green-500');
        }
    }
    
    getWordCount() {
        return this.countWords(this.textarea.value);
    }
    
    getCharCount() {
        return this.textarea.value.length;
    }
    
    destroy() {
        if (this.counterContainer && this.counterContainer.parentNode) {
            this.counterContainer.parentNode.removeChild(this.counterContainer);
        }
    }
}

// Auto-inicializar contadores para campos específicos
document.addEventListener('DOMContentLoaded', function() {
    // Contador para descrições de personagens
    const characterDescription = document.querySelector('textarea[name="description"]');
    if (characterDescription) {
        new WordCounter(characterDescription, {
            minWords: 10,
            maxWords: 2000,
            showProgress: true,
            warningThreshold: 0.9
        });
    }
    
    // Contador para descrições de cenas
    const sceneDescription = document.querySelector('textarea[name="description"]');
    if (sceneDescription && !characterDescription) {
        new WordCounter(sceneDescription, {
            minWords: 10,
            maxWords: 5000,
            showProgress: true,
            warningThreshold: 0.9
        });
    }
    
    // Contador para diálogos
    const dialogues = document.querySelectorAll('textarea[name*="dialogue"]');
    dialogues.forEach(dialogue => {
        new WordCounter(dialogue, {
            minWords: 0,
            maxWords: 2000,
            showProgress: true,
            warningThreshold: 0.8
        });
    });
    
    // Contador para notas
    const notes = document.querySelectorAll('textarea[name="notes"]');
    notes.forEach(note => {
        new WordCounter(note, {
            minWords: 0,
            maxWords: 2000,
            showProgress: false
        });
    });
});

// Exportar para uso global
window.WordCounter = WordCounter;
