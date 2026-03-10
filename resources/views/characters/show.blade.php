<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $character->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Projeto: {{ $project->name }}</p>
            </div>
            <a href="{{ route('characters.index', ['project' => $project->id]) }}" class="text-gray-600 hover:text-gray-900">
                Voltar para Lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Informações Básicas do Personagem -->
                <div class="mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <h3 class="text-2xl font-bold">{{ $character->name }}</h3>
                        <span class="px-3 py-1 text-sm rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            {{ $character->role }}
                        </span>
                    </div>
                    @if($character->description)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Descrição</h4>
                            <p class="text-gray-700 whitespace-pre-line">{{ $character->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Características do Personagem -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    @if($character->goals)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Objetivos</h4>
                            <p class="text-gray-700 whitespace-pre-line">{{ $character->goals }}</p>
                        </div>
                    @endif

                    @if($character->fears)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Medos</h4>
                            <p class="text-gray-700 whitespace-pre-line">{{ $character->fears }}</p>
                        </div>
                    @endif

                    @if($character->personality)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Personalidade</h4>
                            <p class="text-gray-700 whitespace-pre-line">{{ $character->personality }}</p>
                        </div>
                    @endif

                    @if($character->history)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">História</h4>
                            <p class="text-gray-700 whitespace-pre-line">{{ $character->history }}</p>
                        </div>
                    @endif
                </div>

                @if($character->notes)
                    <div class="mb-8">
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Notas Adicionais</h4>
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-line">{{ $character->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Timeline dos Atos -->
                <div class="mb-8" x-data="{ showHiddenScenes: false }">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Linha do Tempo</h4>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="showHiddenScenes" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Mostrar ocultos</span>
                        </label>
                    </div>
                    <div class="space-y-4">
                        @forelse($character->scenes->sortBy('order') as $scene)
                            <div x-data="{ 
                                    open: true, 
                                    isHidden: {{ $scene->pivot->is_hidden ? 'true' : 'false' }},
                                    async toggleVisibility() {
                                        // A URL depende do ESTADO ATUAL (isHidden)
                                        // Se isHidden é true, significa que o usuário acabou de marcar o checkbox -> Queremos Ocultar (DELETE)
                                        // Se isHidden é false, significa que o usuário desmarcou -> Queremos Restaurar (POST)
                                        
                                        const url = this.isHidden 
                                            ? '{{ route('characters.remove-dialogue', ['character' => $character->id, 'scene' => $scene->id, 'project' => $project->id]) }}'
                                            : '{{ route('characters.restore-dialogue', ['character' => $character->id, 'scene' => $scene->id, 'project' => $project->id]) }}';
                                        
                                        const method = this.isHidden ? 'DELETE' : 'POST';
                                        
                                        try {
                                            const response = await fetch(url, {
                                                method: method,
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json',
                                                    'Content-Type': 'application/json'
                                                }
                                            });
                                            
                                            if (!response.ok) {
                                                throw new Error('Erro na requisição');
                                            }
                                        } catch (error) {
                                            console.error('Erro:', error);
                                            this.isHidden = !this.isHidden; // Reverte o estado visual em caso de erro
                                            alert('Ocorreu um erro ao atualizar. Tente novamente.');
                                        }
                                    }
                                 }" 
                                 x-show="!isHidden || showHiddenScenes"
                                 class="border rounded-lg hover:shadow-md transition-shadow bg-white dark:bg-gray-700 overflow-hidden"
                                 :class="{ 'opacity-60 border-dashed border-gray-400': isHidden }"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95">
                                
                                <div class="p-4 cursor-pointer flex justify-between items-center bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-600" @click="open = !open">
                                    <div class="flex items-center gap-2">
                                        <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors focus:outline-none">
                                            <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                            <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <h5 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ $scene->title }}
                                            <span x-show="isHidden" class="ml-2 text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">Oculto</span>
                                        </h5>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 px-2 py-1 rounded border border-gray-200 dark:border-gray-600">
                                        {{ $scene->duration }} min
                                    </span>
                                </div>
                                
                                <div x-show="open" x-collapse class="p-4 border-t border-gray-100 dark:border-gray-600">
                                    @if($scene->description)
                                        <p class="text-gray-700 dark:text-gray-300 mb-3 whitespace-pre-line">{{ $scene->description }}</p>
                                    @endif
                                    
                                    <div class="flex justify-end items-center gap-3 mb-2">
                                        <form action="{{ route('scenes.remove-character', ['scene' => $scene->id, 'character' => $character->id, 'project' => $project->id]) }}" 
                                              method="POST" 
                                              class="inline-flex"
                                              onsubmit="return confirm('Tem certeza que deseja remover este personagem desta cena? Esta ação não pode ser desfeita.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 focus:outline-none" title="Remover da cena">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>

                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" x-model="isHidden" @change="toggleVisibility()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ocultar na timeline</span>
                                        </label>
                                    </div>

                                    @if($scene->pivot->dialogue)
                                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3 relative group">
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Diálogo:</p>
                                                <p class="text-gray-600 italic mt-1 whitespace-pre-line">"{{ $scene->pivot->dialogue }}"</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic">Este personagem ainda não participa de nenhuma cena.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-4">
                    <a href="{{ route('characters.edit', ['character' => $character, 'project' => $project->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Editar Personagem
                    </a>
                    <form action="{{ route('characters.destroy', ['character' => $character, 'project' => $project->id]) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Tem certeza que deseja excluir este personagem?')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Excluir Personagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 