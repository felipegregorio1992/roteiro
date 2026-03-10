<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Episódios') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
            </div>
            <a href="{{ route('episodes.create', ['project' => $project->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Novo Episódio
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
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

            <div class="bg-transparent overflow-hidden sm:rounded-lg">
                <div class="p-0 text-gray-900">
                    @if($episodes->isEmpty())
                        <div class="bg-white text-center py-10 rounded-lg shadow-sm">
                            <p class="text-gray-500 text-lg">Nenhum episódio encontrado.</p>
                            <p class="text-gray-400 text-sm mt-2">Comece criando o primeiro episódio do seu roteiro.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach($episodes as $episode)
                                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden flex flex-col h-full border border-gray-100">
                                    <div class="p-6 flex-1 flex flex-col">
                                        <div class="flex justify-between items-start mb-4">
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 font-bold text-lg flex-shrink-0">
                                                {{ $episode->episode_number ?? '-' }}
                                            </span>
                                            
                                            <div class="flex items-center space-x-1">
                                                <a href="{{ route('episodes.edit', ['episode' => $episode->id, 'project' => $project->id]) }}" 
                                                   class="p-2 text-gray-400 hover:text-indigo-600 transition rounded-full hover:bg-indigo-50"
                                                   title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('episodes.destroy', ['episode' => $episode->id, 'project' => $project->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este episódio?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition rounded-full hover:bg-red-50" title="Excluir">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <a href="{{ route('episodes.show', ['episode' => $episode->id, 'project' => $project->id]) }}" class="block group flex-1">
                                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition mb-2 line-clamp-2">
                                                {{ $episode->title }}
                                            </h3>
                                            @if($episode->description)
                                                <p class="text-sm text-gray-500 line-clamp-3 mb-4">
                                                    {{ $episode->description }}
                                                </p>
                                            @else
                                                <p class="text-sm text-gray-400 italic mb-4">Sem descrição</p>
                                            @endif
                                        </a>
                                    </div>
                                    
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $episode->duration ? $episode->duration . ' min' : '-' }}
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            {{ $episode->characters->count() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
