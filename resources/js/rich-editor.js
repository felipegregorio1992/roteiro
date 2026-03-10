/**
 * Editor de Texto Rico Simples
 * WYSIWYG básico para campos de texto
 */
class RichEditor {
    constructor(textarea, options = {}) {
        this.textarea = textarea;
        this.options = {
            toolbar: options.toolbar || ['bold', 'italic', 'underline', 'link', 'list'],
            placeholder: options.placeholder || 'Digite aqui...',
            maxLength: options.maxLength || null,
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.createEditor();
        this.bindEvents();
        this.loadContent();
    }
    
    createEditor() {
        // Criar container do editor
        this.editorContainer = document.createElement('div');
        this.editorContainer.className = 'rich-editor border border-gray-300 rounded-lg overflow-hidden';
        
        // Criar toolbar
        this.createToolbar();
        
        // Criar área de edição
        this.editor = document.createElement('div');
        this.editor.className = 'editor-content p-3 min-h-32 focus:outline-none';
        this.editor.contentEditable = true;
        this.editor.setAttribute('data-placeholder', this.options.placeholder);
        
        // Adicionar estilos de placeholder
        this.addPlaceholderStyles();
        
        // Montar editor
        this.editorContainer.appendChild(this.toolbar);
        this.editorContainer.appendChild(this.editor);
        
        // Substituir textarea
        this.textarea.parentNode.insertBefore(this.editorContainer, this.textarea);
        this.textarea.style.display = 'none';
    }
    
    createToolbar() {
        this.toolbar = document.createElement('div');
        this.toolbar.className = 'editor-toolbar bg-gray-50 border-b border-gray-300 p-2 flex flex-wrap gap-2';
        
        const buttons = {
            bold: { icon: 'B', title: 'Negrito' },
            italic: { icon: 'I', title: 'Itálico' },
            underline: { icon: 'U', title: 'Sublinhado' },
            link: { icon: '🔗', title: 'Link' },
            list: { icon: '•', title: 'Lista' },
            quote: { icon: '"', title: 'Citação' },
            clear: { icon: '🗑️', title: 'Limpar formatação' }
        };
        
        this.options.toolbar.forEach(tool => {
            if (buttons[tool]) {
                const button = this.createToolbarButton(tool, buttons[tool]);
                this.toolbar.appendChild(button);
            }
        });
        
        // Botão de toggle (editor/textarea)
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'toolbar-button px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded border';
        toggleButton.innerHTML = '📝';
        toggleButton.title = 'Alternar editor';
        toggleButton.addEventListener('click', () => this.toggleMode());
        this.toolbar.appendChild(toggleButton);
    }
    
    createToolbarButton(tool, config) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'toolbar-button px-3 py-1 text-xs bg-white hover:bg-gray-100 rounded border';
        button.innerHTML = config.icon;
        button.title = config.title;
        
        button.addEventListener('click', (e) => {
            e.preventDefault();
            this.executeCommand(tool);
            this.updateToolbarState();
        });
        
        return button;
    }
    
    executeCommand(command) {
        this.editor.focus();
        
        switch (command) {
            case 'bold':
                document.execCommand('bold');
                break;
            case 'italic':
                document.execCommand('italic');
                break;
            case 'underline':
                document.execCommand('underline');
                break;
            case 'link':
                this.insertLink();
                break;
            case 'list':
                document.execCommand('insertUnorderedList');
                break;
            case 'quote':
                this.insertQuote();
                break;
            case 'clear':
                document.execCommand('removeFormat');
                break;
        }
        
        this.syncToTextarea();
    }
    
    insertLink() {
        const url = prompt('Digite a URL:');
        if (url) {
            document.execCommand('createLink', false, url);
        }
    }
    
    insertQuote() {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const blockquote = document.createElement('blockquote');
            blockquote.className = 'border-l-4 border-gray-300 pl-4 italic text-gray-600';
            blockquote.textContent = range.toString() || 'Citação';
            range.deleteContents();
            range.insertNode(blockquote);
        }
    }
    
    updateToolbarState() {
        const buttons = this.toolbar.querySelectorAll('.toolbar-button');
        buttons.forEach(button => {
            button.classList.remove('active');
            
            const command = this.getButtonCommand(button);
            if (command && document.queryCommandState(command)) {
                button.classList.add('active', 'bg-blue-100', 'border-blue-300');
            }
        });
    }
    
    getButtonCommand(button) {
        const icon = button.innerHTML.trim();
        const commands = {
            'B': 'bold',
            'I': 'italic',
            'U': 'underline',
            '•': 'insertUnorderedList'
        };
        return commands[icon];
    }
    
    bindEvents() {
        // Sincronizar com textarea
        this.editor.addEventListener('input', () => this.syncToTextarea());
        this.editor.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });
        
        // Atualizar toolbar
        this.editor.addEventListener('keyup', () => this.updateToolbarState());
        this.editor.addEventListener('mouseup', () => this.updateToolbarState());
        
        // Placeholder
        this.editor.addEventListener('focus', () => this.hidePlaceholder());
        this.editor.addEventListener('blur', () => this.showPlaceholder());
        this.editor.addEventListener('input', () => this.showPlaceholder());
        
        // Contador de caracteres
        if (this.options.maxLength) {
            this.editor.addEventListener('input', () => this.updateCharCount());
        }
    }
    
    syncToTextarea() {
        this.textarea.value = this.editor.innerHTML;
        
        // Disparar evento de input no textarea para compatibilidade
        const event = new Event('input', { bubbles: true });
        this.textarea.dispatchEvent(event);
    }
    
    loadContent() {
        if (this.textarea.value) {
            this.editor.innerHTML = this.textarea.value;
        }
        this.showPlaceholder();
    }
    
    showPlaceholder() {
        if (!this.editor.textContent.trim()) {
            this.editor.classList.add('placeholder-shown');
        } else {
            this.editor.classList.remove('placeholder-shown');
        }
    }
    
    hidePlaceholder() {
        this.editor.classList.remove('placeholder-shown');
    }
    
    updateCharCount() {
        const length = this.editor.textContent.length;
        if (length > this.options.maxLength * 0.9) {
            this.editor.style.borderColor = '#ef4444';
        } else {
            this.editor.style.borderColor = '#d1d5db';
        }
    }
    
    toggleMode() {
        if (this.editorContainer.style.display === 'none') {
            // Mostrar editor rico
            this.editorContainer.style.display = 'block';
            this.textarea.style.display = 'none';
        } else {
            // Mostrar textarea
            this.editorContainer.style.display = 'none';
            this.textarea.style.display = 'block';
        }
    }
    
    addPlaceholderStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .editor-content[data-placeholder]:empty::before {
                content: attr(data-placeholder);
                color: #9ca3af;
                pointer-events: none;
                position: absolute;
                top: 1rem;
                left: 1rem;
                right: 1rem;
                white-space: nowrap;
                overflow: hidden;
            }
            
            .editor-content.placeholder-shown::before {
                content: attr(data-placeholder);
                color: #9ca3af;
                pointer-events: none;
                position: absolute;
                top: 1rem;
                left: 1rem;
                right: 1rem;
                white-space: nowrap;
                overflow: hidden;
            }
            
            .toolbar-button.active {
                background-color: #dbeafe;
                border-color: #3b82f6;
            }
            
            .rich-editor blockquote {
                border-left: 4px solid #d1d5db;
                padding-left: 1rem;
                margin: 0.5rem 0;
                font-style: italic;
                color: #6b7280;
            }
            
            .rich-editor ul, .rich-editor ol {
                margin: 0.5rem 0;
                padding-left: 1.5rem;
            }
            
            .rich-editor a {
                color: #3b82f6;
                text-decoration: underline;
            }
        `;
        
        if (!document.getElementById('rich-editor-styles')) {
            style.id = 'rich-editor-styles';
            document.head.appendChild(style);
        }
    }
    
    getContent() {
        return this.editor.innerHTML;
    }
    
    setContent(content) {
        this.editor.innerHTML = content;
        this.syncToTextarea();
    }
    
    destroy() {
        if (this.editorContainer && this.editorContainer.parentNode) {
            this.textarea.style.display = 'block';
            this.editorContainer.parentNode.removeChild(this.editorContainer);
        }
    }
}

// Auto-inicializar para campos específicos
document.addEventListener('DOMContentLoaded', function() {
    // Editor para descrições de personagens
    const characterDescription = document.querySelector('textarea[name="description"]');
    if (characterDescription && characterDescription.closest('form[action*="characters"]')) {
        new RichEditor(characterDescription, {
            toolbar: ['bold', 'italic', 'link', 'list', 'quote'],
            placeholder: 'Descreva o personagem...'
        });
    }
    
    // Editor para descrições de cenas
    const sceneDescription = document.querySelector('textarea[name="description"]');
    if (sceneDescription && sceneDescription.closest('form[action*="scenes"]')) {
        new RichEditor(sceneDescription, {
            toolbar: ['bold', 'italic', 'link', 'list'],
            placeholder: 'Descreva a cena...'
        });
    }
    
    // Editor para diálogos
    const dialogues = document.querySelectorAll('textarea[name*="dialogue"]');
    dialogues.forEach(dialogue => {
        new RichEditor(dialogue, {
            toolbar: ['bold', 'italic'],
            placeholder: 'Digite o diálogo...'
        });
    });
});

// Exportar para uso global
window.RichEditor = RichEditor;
