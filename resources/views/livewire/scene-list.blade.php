<div class="space-y-6">
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
        <!-- Flash Message -->
        @if (session()->has('success'))
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" wire:key="act-{{ $actNumber }}">
                <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Ato {{ $actNumber }}</h3>
                            <button onclick="openEditActModal({{ $actNumber }})" 
                                    class="inline-flex items-center px-2 py-1 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    title="Editar nome do ato">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            
                            <!-- Botões de Mover Ato -->
                            <div class="flex items-center space-x-1 ml-2">
                                @if(!$loop->first)
                                    <button wire:click="moveActUp({{ $actNumber }})" 
                                            class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                                            title="Mover Ato para Cima">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                @endif
                                
                                @if(!$loop->last)
                                    <button wire:click="moveActDown({{ $actNumber }})" 
                                            class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                                            title="Mover Ato para Baixo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
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
                
                <div class="divide-y divide-gray-200 scenes-list min-h-[50px]">
                     
                    @if(count($act['scenes']) > 0)
                        @foreach($act['scenes'] as $index => $scene)
                        <div class="scene-item p-6 hover:bg-gray-50 transition-colors duration-200 border border-transparent hover:border-indigo-100 rounded-lg">
                             
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="order-display inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 text-sm font-medium">
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
                                        <p class="mt-2 text-sm text-gray-600 prose-content line-clamp-2 ml-10">{{ $scene['description'] }}</p>
                                    @endif

                                    @if(!empty($scene['characters']))
                                        <div class="mt-3 ml-10">
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($scene['characters'] as $character)
                                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                                        {{ $character['name'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-4 flex flex-shrink-0 gap-2 items-center">
                                    <!-- Botões de ação -->
                                    <button wire:click="toggleExpand({{ $scene['id'] }})" 
                                            class="rounded-md bg-white p-1.5 text-gray-400 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            title="Editar Diálogos">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </button>

                                    <a href="{{ route('scenes.edit', ['scene' => $scene['id'], 'project' => $project->id]) }}" 
                                       class="rounded-md bg-white p-1.5 text-gray-400 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    
                                    <button wire:click="deleteScene({{ $scene['id'] }})" 
                                            wire:confirm="Tem certeza que deseja excluir esta cena?"
                                            class="rounded-md bg-white p-1.5 text-gray-400 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            @if($expandedSceneId === $scene['id'])
                                <div class="mt-4 border-t border-gray-100 pt-4 animate-fade-in-down">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Editar Diálogos</h4>
                                    <div class="space-y-4">
                                        @foreach($scene['characters'] as $character)
                                            <div class="flex gap-4">
                                                <div class="w-32 flex-shrink-0 pt-2">
                                                    <span class="text-sm font-medium text-gray-700">{{ $character['name'] }}</span>
                                                </div>
                                                <div class="flex-grow">
                                                    <textarea 
                                                        wire:model.live.debounce.500ms="editingDialogues.{{ $character['id'] }}"
                                                        wire:blur="saveDialogue({{ $scene['id'] }}, {{ $character['id'] }})"
                                                        rows="2"
                                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                        placeholder="Digite o diálogo..."
                                                    ></textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if(empty($scene['characters']))
                                            <p class="text-sm text-gray-500 italic">Nenhum personagem nesta cena.</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="p-4 text-center text-gray-500 italic">
                            Nenhuma cena neste ato.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>