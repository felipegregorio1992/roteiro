<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Criar Episódio') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Projeto: {{ $project->name }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('episodes.index', ['project' => $project->id]) }}" 
                   class="text-gray-600 hover:text-gray-900">
                    Voltar para Lista
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Novo Episódio</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Adicione um novo episódio ao seu roteiro.
                            Cada episódio pode ter seus próprios personagens e diálogos.
                        </p>
                    </div>
                    
                    <form method="POST" action="{{ route('episodes.store') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <input type="hidden" name="update_characters" value="1">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="title" value="Título" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" 
                                             :value="old('title')" required autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="episode_number" value="Número do Episódio" />
                                <x-text-input id="episode_number" name="episode_number" type="number" class="mt-1 block w-full" 
                                             :value="old('episode_number', $nextEpisodeNumber)" min="1" />
                                <x-input-error :messages="$errors->get('episode_number')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" value="Sinopse / Descrição" />
                            <textarea id="description" name="description" 
                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      rows="3">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="duration" value="Duração Estimada (minutos)" />
                                <x-text-input id="duration" name="duration" type="number" class="mt-1 block w-full" 
                                             :value="old('duration')" min="1" />
                                <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="order" value="Ordem de Exibição" />
                                <x-text-input id="order" name="order" type="number" class="mt-1 block w-full" 
                                             :value="old('order', $nextEpisodeNumber)" required min="1" />
                                <x-input-error :messages="$errors->get('order')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="characters" value="Personagens e Diálogos" />
                            <p class="text-sm text-gray-500 mb-4">Selecione os personagens que participam deste episódio e insira seus textos/diálogos.</p>
                            
                            <div class="space-y-4">
                                @foreach($characters as $character)
                                    <div x-data="{ open: false }" class="border rounded-lg p-4 bg-gray-50">
                                        <div class="flex items-start gap-4">
                                            <div class="flex items-center h-5 pt-1">
                                                <input type="checkbox" 
                                                       name="characters[]" 
                                                       value="{{ $character->id }}"
                                                       id="character_{{ $character->id }}"
                                                       @click="open = $el.checked"
                                                       class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                            </div>
                                            
                                            <div class="flex-grow">
                                                <label for="character_{{ $character->id }}" class="flex items-center cursor-pointer">
                                                    <span class="font-bold text-gray-800 text-lg">{{ $character->name }}</span>
                                                    <span class="ml-2 text-sm text-gray-500">({{ $character->role }})</span>
                                                </label>
                                                
                                                <div x-show="open" 
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                                     class="mt-3" 
                                                     style="display: none;">
                                                    <label for="dialogue_{{ $character->id }}" class="block text-sm font-medium text-gray-700 mb-1">Texto / Diálogo / Anotações</label>
                                                    <textarea name="dialogues[{{ $character->id }}]" 
                                                              id="dialogue_{{ $character->id }}"
                                                              rows="6"
                                                              placeholder="Escreva o texto do personagem para este episódio..."
                                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old("dialogues.$character->id") }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('characters')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Criar Episódio') }}</x-primary-button>
                            <a href="{{ route('episodes.index', ['project' => $project->id]) }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
