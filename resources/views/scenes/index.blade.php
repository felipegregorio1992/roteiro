<x-app-layout>
    @isset($header)
        <x-slot name="header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ __('Cenas') }}
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
                        <p class="text-sm text-gray-500 mt-2">Você pode criar quantas cenas desejar para seu roteiro. Organize-as por atos ou na ordem que preferir.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('scenes.create', ['project' => $project->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nova Cena
                        </a>
                        <span class="text-sm text-gray-500">Clique para adicionar uma nova cena ao seu roteiro</span>
                    </div>
                </div>
            </div>
        </x-slot>
    @endisset

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 animate-fade-in-down">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <livewire:scene-list :project="$project" />
        </div>
    </div>

    <!-- Modal de Criação Rápida de Cena -->
    <div id="quickSceneModal" class="modal-overlay hidden" onclick="handleModalOverlayClick(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 class="modal-title">Criar Nova Cena</h3>
                <button type="button" onclick="closeQuickSceneModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="quickSceneForm" class="space-y-6">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    
                    <div class="form-group">
                        <label for="quick_title" class="form-label">Título</label>
                        <input type="text" name="title" id="quick_title" required placeholder="Digite o título da cena..."
                               class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="quick_description" class="form-label">Descrição</label>
                        <textarea name="description" id="quick_description" rows="4" required placeholder="Descreva a cena..."
                                  class="form-textarea"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="quick_duration" class="form-label">Duração (min)</label>
                            <input type="number" name="duration" id="quick_duration" required min="1" placeholder="0"
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="quick_order" class="form-label">Ordem</label>
                            <input type="number" name="order" id="quick_order" required min="1" placeholder="1"
                                   class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Personagens</label>
                        <div class="space-y-4 max-h-80 overflow-y-auto border border-gray-200 rounded-xl p-4 bg-gray-50">
                            @foreach($characters as $character)
                                <div class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-start gap-4">
                                        <div class="flex items-center h-6 pt-1">
                                            <input type="checkbox" 
                                                   name="characters[]" 
                                                   value="{{ $character->id }}"
                                                   id="quick_character_{{ $character->id }}"
                                                   class="form-checkbox">
                                        </div>
                                        <div class="flex-grow">
                                            <label for="quick_character_{{ $character->id }}" class="font-semibold text-gray-900 block mb-2 cursor-pointer">
                                                {{ $character->name }}
                                                <span class="badge badge-secondary ml-2">{{ $character->role }}</span>
                                            </label>
                                            <div class="mt-2">
                                                <textarea name="dialogues[{{ $character->id }}]" 
                                                          rows="3"
                                                          placeholder="Digite o diálogo do personagem nesta cena..."
                                                          class="form-textarea"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-sm text-gray-600">Selecione os personagens que participam desta cena e adicione seus diálogos.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeQuickSceneModal()"
                        class="btn btn-secondary">
                    Fechar
                </button>
                <button type="button" onclick="saveAndCreateAnother()"
                        class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Salvar e Criar Outra
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Criação de Ato -->
    <div id="newActModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity hidden" style="z-index: 100;">
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button type="button" onclick="closeNewActModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Fechar</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Criar Novo Ato</h3>
                            <div class="mt-4">
                                <form id="newActForm" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                                    
                                    <div>
                                        <label for="act_number" class="block text-sm font-medium text-gray-700">Número do Ato</label>
                                        <input type="number" name="act_number" id="act_number" required min="1"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="act_title" class="block text-sm font-medium text-gray-700">Nome do Ato (opcional)</label>
                                        <input type="text" name="act_title" id="act_title" placeholder="Ex: Abertura, Desenvolvimento, Conclusão..."
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" onclick="createNewAct()"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:w-auto">
                            Criar Ato
                        </button>
                        <button type="button" onclick="closeNewActModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Ato -->
    <div id="editActModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity hidden" style="z-index: 100;">
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button type="button" onclick="closeEditActModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Fechar</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Editar Nome do Ato</h3>
                            <div class="mt-4">
                                <form id="editActForm" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                                    <input type="hidden" name="act_number" id="edit_act_number">
                                    
                                    <div>
                                        <label for="edit_act_title" class="block text-sm font-medium text-gray-700">Nome do Ato</label>
                                        <input type="text" name="act_title" id="edit_act_title" placeholder="Ex: Abertura, Desenvolvimento, Conclusão..."
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" onclick="saveActTitle()"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:w-auto">
                            Salvar
                        </button>
                        <button type="button" onclick="closeEditActModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<style>
    @keyframes fade-in-down {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.5s ease-out;
    }

    .fab-button {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 50;
        transition: all 0.2s ease-in-out;
        display: none !important;
    }

    .fab-button:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>


<script>
    function openQuickSceneModal(actNumber = 1) {
        try {
            console.log('openQuickSceneModal chamada com actNumber:', actNumber);
            const modal = document.getElementById('quickSceneModal');
            const titleInput = document.getElementById('quick_title');
            const orderInput = document.getElementById('quick_order');
            
            if (!modal || !titleInput || !orderInput) {
                console.error('Elementos do modal não encontrados');
                return;
            }
            
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            titleInput.value = `Ato ${actNumber} - `;
            titleInput.focus();
            
            // Define um valor padrão para a ordem baseado no número de cenas no ato
            let scenesInAct = 0;
            const actContainer = document.querySelector(`.scenes-list[data-act-number="${actNumber}"]`);
            if (actContainer) {
                scenesInAct = actContainer.querySelectorAll('.scene-item').length;
            }
            orderInput.value = scenesInAct + 1;
        } catch (error) {
            console.error('Erro ao abrir modal:', error);
        }
    }

    function closeQuickSceneModal() {
        try {
            console.log('closeQuickSceneModal chamada');
            const modal = document.getElementById('quickSceneModal');
            const form = document.getElementById('quickSceneForm');
            
            if (modal) {
                console.log('Modal encontrado, fechando...');
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
            
            if (form) {
                form.reset();
            }
            
            // Limpa os campos de diálogo
            document.querySelectorAll('textarea[name^="dialogues["]').forEach(textarea => {
                textarea.value = '';
            });
            
            // Desmarca todos os checkboxes de personagens
            document.querySelectorAll('input[name="characters[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Força o modal a ficar oculto
            setTimeout(() => {
                if (modal && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                    modal.style.visibility = 'hidden';
                }
            }, 100);
        } catch (error) {
            console.error('Erro ao fechar modal:', error);
        }
    }

    function handleModalOverlayClick(event) {
        // Fecha o modal apenas se o clique foi no overlay, não no conteúdo do modal
        if (event.target === event.currentTarget) {
            closeQuickSceneModal();
        }
    }

    // Garantir que todos os modais estejam fechados quando a página carrega
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded - verificando modais');
        const quickSceneModal = document.getElementById('quickSceneModal');
        const newActModal = document.getElementById('newActModal');
        const editActModal = document.getElementById('editActModal');
        
        if (quickSceneModal) {
            console.log('quickSceneModal encontrado, adicionando classe hidden');
            quickSceneModal.classList.add('hidden');
            quickSceneModal.style.display = 'none';
        }
        
        if (newActModal) {
            newActModal.classList.add('hidden');
        }
        
        if (editActModal) {
            editActModal.classList.add('hidden');
        }
        
        // Debug: verificar se todas as cenas estão carregadas
        const sceneItems = document.querySelectorAll('.scene-item');
        console.log('Cenas encontradas:', sceneItems.length);
    });

    // Função de teste simples
    function testMove() {
        console.log('Função de teste chamada!');
        alert('Função de teste funcionando!');
    }

    // Funções para mover cenas
    async function moveSceneUp(sceneId, actNumber) {
        alert(`moveSceneUp chamada! Scene ID: ${sceneId}, Act: ${actNumber}`);
        
        const sceneElement = document.querySelector(`[data-scene-id="${sceneId}"]`);
        
        if (!sceneElement) {
            alert('Scene element não encontrado!');
            return;
        }
        
        const previousScene = sceneElement.previousElementSibling;
        
        if (!previousScene || !previousScene.classList.contains('scene-item')) {
            alert('Já é a primeira cena!');
            return;
        }
        
        // Trocar posições no DOM
        sceneElement.parentNode.insertBefore(sceneElement, previousScene);
        alert('Posições trocadas!');
        
        // Salvar nova ordem no backend
        await saveSceneOrder(actNumber);
    }

    async function moveSceneDown(sceneId, actNumber) {
        alert(`moveSceneDown chamada! Scene ID: ${sceneId}, Act: ${actNumber}`);
        
        const sceneElement = document.querySelector(`[data-scene-id="${sceneId}"]`);
        
        if (!sceneElement) {
            alert('Scene element não encontrado!');
            return;
        }
        
        const nextScene = sceneElement.nextElementSibling;
        
        if (!nextScene || !nextScene.classList.contains('scene-item')) {
            alert('Já é a última cena!');
            return;
        }
        
        // Trocar posições no DOM
        sceneElement.parentNode.insertBefore(nextScene, sceneElement);
        alert('Posições trocadas!');
        
        // Salvar nova ordem no backend
        await saveSceneOrder(actNumber);
    }

    async function saveSceneOrder(actNumber) {
        console.log('saveSceneOrder chamada para ato:', actNumber);
        const actContainer = document.querySelector(`[data-act-number="${actNumber}"]`);
        console.log('actContainer encontrado:', actContainer);
        
        if (!actContainer) {
            console.log('Act container não encontrado');
            return;
        }
        
        const scenes = actContainer.querySelectorAll('.scene-item');
        console.log('Scenes encontradas:', scenes.length);
        
        const sceneOrder = Array.from(scenes).map(scene => ({
            id: scene.dataset.sceneId,
            order: Array.from(scenes).indexOf(scene) + 1
        }));
        
        console.log('Scene order:', sceneOrder);
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            console.log('CSRF token:', csrfToken ? csrfToken.getAttribute('content') : 'não encontrado');
            
            const response = await fetch('{{ route("scenes.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                },
                body: JSON.stringify({
                    act_number: actNumber,
                    scenes: sceneOrder
                })
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Erro response:', errorText);
                throw new Error('Erro ao salvar ordem das cenas');
            }
            
            // Atualizar números das cenas
            updateSceneNumbers(actNumber);
            
            // Mostrar notificação de sucesso
            if (window.notifications) {
                window.notifications.show('Ordem das cenas atualizada com sucesso!', 'success');
            } else {
                alert('Ordem das cenas atualizada com sucesso!');
            }
            
        } catch (error) {
            console.error('Erro ao salvar ordem das cenas:', error);
            if (window.notifications) {
                window.notifications.show('Erro ao atualizar ordem das cenas', 'error');
            } else {
                alert('Erro ao atualizar ordem das cenas');
            }
        }
    }

    function updateSceneNumbers(actNumber) {
        const actContainer = document.querySelector(`[data-act-number="${actNumber}"]`);
        if (!actContainer) return;
        
        const scenes = actContainer.querySelectorAll('.scene-item');
        scenes.forEach((scene, index) => {
            const orderDisplay = scene.querySelector('.order-display');
            if (orderDisplay) {
                orderDisplay.textContent = `${index + 1}`;
            }
        });
    }

    async function saveAndCreateAnother() {
        const form = document.getElementById('quickSceneForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route('scenes.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Mostra mensagem de sucesso
                const successDiv = document.createElement('div');
                successDiv.className = 'mb-4 rounded-md bg-green-50 p-4 animate-fade-in-down';
                successDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">${data.message}</p>
                        </div>
                    </div>
                `;
                
                // Adiciona a mensagem no topo da página
                const container = document.querySelector('.max-w-7xl');
                container.insertBefore(successDiv, container.firstChild);
                
                // Remove a mensagem após 3 segundos
                setTimeout(() => {
                    successDiv.remove();
                }, 3000);

                // Limpa o formulário
                form.reset();
                
                // Limpa os campos de diálogo
                document.querySelectorAll('textarea[name^="dialogues["]').forEach(textarea => {
                    textarea.value = '';
                });
                
                // Desmarca todos os checkboxes de personagens
                document.querySelectorAll('input[name="characters[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Atualiza o valor da ordem para a próxima cena
                const nextOrder = document.getElementById('quick_order').value;
                document.getElementById('quick_order').value = parseInt(nextOrder) + 1;
                
                // Foca no título para a próxima cena
                document.getElementById('quick_title').focus();
                
                // Recarrega a página em segundo plano
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                // Mostra mensagem de erro
                let errorMessage = 'Erro ao criar cena';
                
                if (data.errors) {
                    const errorMessages = [];
                    Object.keys(data.errors).forEach(key => {
                        errorMessages.push(`${key}: ${data.errors[key].join(', ')}`);
                    });
                    errorMessage = errorMessages.join('\n');
                } else if (data.message) {
                    errorMessage = data.message;
                }
                
                console.error('Erro ao criar cena:', data);
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'mb-4 rounded-md bg-red-50 p-4 animate-fade-in-down';
                errorDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">${errorMessage}</p>
                        </div>
                    </div>
                `;
                
                // Adiciona a mensagem no topo da página
                const container = document.querySelector('.max-w-7xl');
                if (container) {
                    container.insertBefore(errorDiv, container.firstChild);
                }
                
                // Remove a mensagem após 10 segundos
                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                }, 10000);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao criar cena: ' + error.message);
        }
    }

    function openNewActModal() {
        document.getElementById('newActModal').classList.remove('hidden');
        document.getElementById('act_number').value = {{ count($acts) + 1 }};
        document.getElementById('act_number').focus();
    }

    function closeNewActModal() {
        document.getElementById('newActModal').classList.add('hidden');
        document.getElementById('newActForm').reset();
    }

    async function createNewAct() {
        const actNumber = document.getElementById('act_number').value;
        if (!actNumber || actNumber < 1) {
            alert('Por favor, insira um número válido para o ato.');
            return;
        }

        const form = document.getElementById('newActForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route('scenes.create-act') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Mostra mensagem de sucesso
                const successDiv = document.createElement('div');
                successDiv.className = 'mb-4 rounded-md bg-green-50 p-4 animate-fade-in-down';
                successDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">${data.message}</p>
                        </div>
                    </div>
                `;
                
                // Adiciona a mensagem no topo da página
                const container = document.querySelector('.max-w-7xl');
                container.insertBefore(successDiv, container.firstChild);
                
                // Remove a mensagem após 3 segundos
                setTimeout(() => {
                    successDiv.remove();
                }, 3000);

                // Fecha o modal
                closeNewActModal();
                
                // Recarrega a página para mostrar o novo ato
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                // Mostra mensagem de erro
                let errorMessage = 'Erro ao criar ato';
                
                if (data.errors) {
                    const errorMessages = [];
                    Object.keys(data.errors).forEach(key => {
                        errorMessages.push(`${key}: ${data.errors[key].join(', ')}`);
                    });
                    errorMessage = errorMessages.join('\n');
                } else if (data.message) {
                    errorMessage = data.message;
                }
                
                console.error('Erro ao criar ato:', data);
                alert('Erro ao criar ato: ' + errorMessage);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao criar ato: ' + error.message);
        }
    }

    function openEditActModal(actNumber) {
        document.getElementById('editActModal').classList.remove('hidden');
        document.getElementById('edit_act_number').value = actNumber;
        document.getElementById('edit_act_title').value = '';
        document.getElementById('edit_act_title').focus();
    }

    function closeEditActModal() {
        document.getElementById('editActModal').classList.add('hidden');
        document.getElementById('editActForm').reset();
    }

    async function saveActTitle() {
        const actNumber = document.getElementById('edit_act_number').value;
        const actTitle = document.getElementById('edit_act_title').value.trim();

        if (!actTitle) {
            alert('Por favor, insira um nome para o ato.');
            return;
        }

        const form = document.getElementById('editActForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route('scenes.update-act-title') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                closeEditActModal();
                window.location.reload();
            } else {
                const errorMessage = data.message || 'Erro ao atualizar nome do ato.';
                alert(errorMessage);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao atualizar nome do ato: ' + error.message);
        }
    }

</script> 