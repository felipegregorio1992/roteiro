<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('episodes.create', ['project' => $project->id]) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Episódio
                </a>
                <a href="{{ route('excel.index', ['project' => $project->id]) }}"
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Importar Dados
                </a>
                <a href="{{ route('projects.files.index', ['project' => $project->id]) }}"
                   class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L5.757 10.757a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Arquivos
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($project->description)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-2">Descrição do Roteiro</h3>
                        <p class="text-gray-600">{{ $project->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Seção de Episódios -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Episódios do Roteiro</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Gerencie os episódios do seu roteiro.
                            </p>
                        </div>
                        <a href="{{ route('episodes.create', ['project' => $project->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Adicionar Novo Episódio
                        </a>
                    </div>

                    @if($episodes->isEmpty())
                        <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                            </svg>
                            <p class="mt-2 text-gray-500">Nenhum episódio encontrado.</p>
                            <p class="text-sm text-gray-400">Comece criando o primeiro episódio.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($episodes as $episode)
                                <a href="{{ route('episodes.show', ['episode' => $episode->id, 'project' => $project->id]) }}" 
                                   class="block p-4 border rounded-lg hover:shadow-md transition bg-gray-50 hover:bg-white group">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded text-xs font-bold uppercase">
                                            Episódio {{ $episode->episode_number ?? $episode->order }}
                                        </span>
                                        <span class="text-gray-400 group-hover:text-indigo-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-gray-900 mb-1 group-hover:text-indigo-600 transition">{{ $episode->title }}</h4>
                                    @if($episode->description)
                                        <p class="text-sm text-gray-500 line-clamp-2">{{ $episode->description }}</p>
                                    @endif
                                    <div class="mt-3 flex items-center text-xs text-gray-400 space-x-2">
                                        @if($episode->duration)
                                            <span>{{ $episode->duration }} min</span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>{{ $episode->characters->count() }} personagens</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout> 
