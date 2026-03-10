<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Episódio {{ $episode->episode_number ?? $episode->order }}: {{ $episode->title }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Projeto: {{ $project->name }}</p>
            </div>
            <a href="{{ route('episodes.index', ['project' => $project->id]) }}" 
               class="text-gray-600 hover:text-gray-900">
                Voltar para Lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Informações do Episódio -->
                    <div class="mb-8 border-b border-gray-200 pb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold uppercase tracking-wide">
                                        Episódio {{ $episode->episode_number ?? $episode->order }}
                                    </span>
                                    @if($episode->duration)
                                        <span class="text-gray-500 text-sm flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $episode->duration }} min
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900">{{ $episode->title }}</h3>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('episodes.edit', ['episode' => $episode->id, 'project' => $project->id]) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </a>
                                <form action="{{ route('episodes.destroy', ['episode' => $episode->id, 'project' => $project->id]) }}" 
                                      method="POST" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Tem certeza que deseja excluir este episódio?')"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($episode->description)
                            <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Sinopse</h4>
                                <p class="text-gray-800 whitespace-pre-line leading-relaxed">{{ $episode->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Cenas e Atos -->
                    <div class="mt-8 mb-12">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                Roteiro / Cenas
                            </h3>
                            <a href="{{ route('scenes.create', ['project_id' => $project->id, 'episode_id' => $episode->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar Cena
                            </a>
                        </div>

                        <div class="space-y-8">
                            @forelse($acts as $actNumber => $actData)
                                <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                        <h4 class="font-bold text-lg text-gray-700">Ordem das Cenas Episódio {{ $episode->episode_number }}</h4>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($actData['scenes'] as $scene)
                                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition duration-200 flex flex-col h-full">
                                                    <div class="p-4 flex-grow">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <div class="flex gap-2 items-center">
                                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-mono rounded font-bold">
                                                                    #{{ $scene->order }}
                                                                </span>
                                                                @if($scene->scene_type)
                                                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded border border-indigo-100 uppercase">
                                                                        {{ $scene->scene_type }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <span class="text-xs text-gray-500 font-mono flex items-center bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                {{ $scene->duration }} min
                                                            </span>
                                                        </div>
                                                        <h5 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 leading-tight">
                                                            {{ $scene->title }}
                                                        </h5>
                                                        <p class="text-sm text-gray-500 line-clamp-3 mb-4">
                                                            {{ $scene->description }}
                                                        </p>
                                                    </div>
                                                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-100 flex justify-end rounded-b-lg">
                                                        <a href="{{ route('scenes.show', ['scene' => $scene->id, 'project' => $project->id]) }}" 
                                                           class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center group">
                                                            Ver Detalhes
                                                            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma cena neste episódio</h3>
                                    <p class="mt-1 text-sm text-gray-500">Comece adicionando cenas para estruturar seu episódio.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('scenes.create', ['project_id' => $project->id, 'episode_id' => $episode->id]) }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Adicionar Primeira Cena
                                        </a>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Personagens e Diálogos -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Roteiro / Personagens
                        </h3>
                        
                        <div class="space-y-8">
                            @if ($episode->characters->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach ($episode->characters as $character)
                                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition duration-200 overflow-hidden flex flex-col h-full">
                                            <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center text-indigo-700 font-bold text-lg mr-3 shadow-inner">
                                                    {{ substr($character->name, 0, 1) }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="text-base font-bold text-gray-900 truncate" title="{{ $character->name }}">
                                                        <a href="{{ route('characters.show', ['character' => $character, 'project' => $project->id]) }}" 
                                                           class="hover:text-indigo-600 transition">
                                                            {{ $character->name }}
                                                        </a>
                                                    </h4>
                                                    <span class="text-xs text-gray-500 uppercase tracking-wide block truncate">{{ $character->role }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="p-5 flex-grow flex flex-col">
                                                @if($character->pivot->dialogue)
                                                    <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line leading-relaxed font-serif italic flex-grow relative pl-4 border-l-2 border-indigo-200">
                                                        "{{ Str::limit($character->pivot->dialogue, 150) }}"
                                                    </div>
                                                    @if(strlen($character->pivot->dialogue) > 150)
                                                        <div class="mt-3 text-right">
                                                            <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium" 
                                                                    onclick="alert('{{ js_escape($character->pivot->dialogue) }}')">
                                                                Ler tudo
                                                            </button>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="flex-grow flex items-center justify-center">
                                                        <p class="text-gray-400 text-sm italic text-center">Nenhum diálogo registrado.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum personagem adicionado</h3>
                                    <p class="mt-1 text-sm text-gray-500">Adicione personagens e seus diálogos editando este episódio.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('episodes.edit', ['episode' => $episode->id, 'project' => $project->id, 'mode' => 'characters']) }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Adicionar Personagens
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
