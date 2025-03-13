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

            @if(empty($acts))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma cena encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova cena para seu roteiro.</p>
                            <div class="mt-6">
                                <a href="{{ route('scenes.create', ['project' => $project->id]) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Criar Cena
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    <!-- Botão para criar novo ato -->
                    <div class="flex justify-end mb-4 space-x-4">
                        <a href="{{ route('scenes.export', ['project' => $project->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar Excel
                        </a>
                        <button onclick="openNewActModal()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Novo Ato
                        </button>
                    </div>

                    @foreach($acts as $actNumber => $act)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900">Ato {{ $actNumber }}</h3>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <button onclick="openQuickSceneModal({{ $actNumber }})" 
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Nova Cena
                                        </button>
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                            {{ count($act['scenes']) }} cena(s)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="divide-y divide-gray-200">
                                @foreach($act['scenes'] as $index => $scene)
                                    <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-start justify-between">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 text-sm font-medium">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    <h4 class="text-lg font-medium text-indigo-600">
                                                        <a href="{{ route('scenes.show', ['scene' => $scene['id'], 'project' => $project->id]) }}" 
                                                           class="hover:text-indigo-800 hover:underline">
                                                            {{ $scene['title'] }}
                                                        </a>
                                                    </h4>
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                        {{ $scene['duration'] }} min
                                                    </span>
                                                </div>

                                                @if($scene['description'])
                                                    <p class="mt-2 text-sm text-gray-600 prose-content line-clamp-2">{{ $scene['description'] }}</p>
                                                @endif

                                                @if(!empty($scene['characters']))
                                                    <div class="mt-3">
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @foreach($scene['characters'] as $character)
                                                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                                                    {{ $character['name'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                        @if(collect($scene['characters'])->some(fn($char) => !empty($char['dialogue'])))
                                                            <div class="mt-3 pl-4 border-l-2 border-gray-200 space-y-3">
                                                                @foreach($scene['characters'] as $character)
                                                                    @if(!empty($character['dialogue']))
                                                                        <div class="relative">
                                                                            <div class="text-sm prose-content">
                                                                                <span class="font-medium text-gray-900">{{ $character['name'] }}</span>
                                                                                <p class="mt-0.5 text-gray-600 italic">
                                                                                    "{{ $character['dialogue'] }}"
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="ml-4 flex flex-shrink-0 gap-2">
                                                <a href="{{ route('scenes.edit', ['scene' => $scene['id'], 'project' => $project->id]) }}" 
                                                   class="rounded-md bg-white p-1.5 text-gray-400 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('scenes.destroy', ['scene' => $scene['id'], 'project' => $project->id]) }}" 
                                                      method="POST" 
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            onclick="return confirm('Tem certeza que deseja excluir esta cena?')"
                                                            class="rounded-md bg-white p-1.5 text-gray-400 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Criação Rápida de Cena -->
    <div id="quickSceneModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity hidden" style="z-index: 100;">
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button type="button" onclick="closeQuickSceneModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Fechar</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Criar Nova Cena</h3>
                            <div class="mt-4">
                                <form id="quickSceneForm" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                                    
                                    <div>
                                        <label for="quick_title" class="block text-sm font-medium text-gray-700">Título</label>
                                        <input type="text" name="title" id="quick_title" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="quick_description" class="block text-sm font-medium text-gray-700">Descrição</label>
                                        <textarea name="description" id="quick_description" rows="3" required
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="quick_duration" class="block text-sm font-medium text-gray-700">Duração (min)</label>
                                            <input type="number" name="duration" id="quick_duration" required min="1"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="quick_order" class="block text-sm font-medium text-gray-700">Ordem</label>
                                            <input type="number" name="order" id="quick_order" required min="1"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Personagens</label>
                                        <div class="space-y-3 max-h-60 overflow-y-auto border rounded-md p-3">
                                            @foreach($characters as $character)
                                                <div class="border rounded-lg p-3 bg-gray-50">
                                                    <div class="flex items-start gap-3">
                                                        <div class="flex items-center h-5 pt-1">
                                                            <input type="checkbox" 
                                                                   name="characters[]" 
                                                                   value="{{ $character->id }}"
                                                                   id="quick_character_{{ $character->id }}"
                                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        </div>
                                                        <div class="flex-grow">
                                                            <label for="quick_character_{{ $character->id }}" class="font-medium text-gray-700 block mb-1">
                                                                {{ $character->name }}
                                                                <span class="text-sm text-gray-500">({{ $character->role }})</span>
                                                            </label>
                                                            <div class="mt-1">
                                                                <textarea name="dialogues[{{ $character->id }}]" 
                                                                          rows="2"
                                                                          placeholder="Diálogo do personagem nesta cena..."
                                                                          class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">Selecione os personagens que participam desta cena e adicione seus diálogos.</p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" onclick="saveAndCreateAnother()"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:w-auto">
                            Salvar e Criar Outra
                        </button>
                        <button type="button" onclick="closeQuickSceneModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Fechar
                        </button>
                    </div>
                </div>
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
        display: none;
    }

    .fab-button:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>

<!-- Botão flutuante para criar nova cena -->
<a href="javascript:void(0)" onclick="openQuickSceneModal()"
   class="fab-button inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-full font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Nova Cena
</a>

<script>
    function openQuickSceneModal(actNumber) {
        document.getElementById('quickSceneModal').classList.remove('hidden');
        document.getElementById('quick_title').value = `Ato ${actNumber} - `;
        document.getElementById('quick_title').focus();
        
        // Define um valor padrão para a ordem baseado no número de cenas no ato
        const scenesInAct = {{ isset($act) ? count($act['scenes']) : 0 }};
        document.getElementById('quick_order').value = scenesInAct + 1;
    }

    function closeQuickSceneModal() {
        document.getElementById('quickSceneModal').classList.add('hidden');
        document.getElementById('quickSceneForm').reset();
        
        // Limpa os campos de diálogo
        document.querySelectorAll('textarea[name^="dialogues["]').forEach(textarea => {
            textarea.value = '';
        });
        
        // Desmarca todos os checkboxes de personagens
        document.querySelectorAll('input[name="characters[]"]').forEach(checkbox => {
            checkbox.checked = false;
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
                const errorMessage = data.message || Object.values(data.errors || {}).flat().join('\n');
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
                container.insertBefore(errorDiv, container.firstChild);
                
                // Remove a mensagem após 5 segundos
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
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
                const errorMessage = data.message || Object.values(data.errors || {}).flat().join('\n');
                alert('Erro ao criar ato: ' + errorMessage);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao criar ato: ' + error.message);
        }
    }
</script> 