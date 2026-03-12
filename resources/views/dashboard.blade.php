<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl gradient-text leading-tight">
                    {{ __('Meus Roteiros') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gerencie seus projetos de roteiro</p>
            </div>
            <a href="{{ route('projects.create') }}" 
               class="btn btn-primary btn-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Novo Roteiro
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($projects->isEmpty())
                <div class="text-center py-16 card animate-fade-in">
                    <div class="mx-auto w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhum roteiro encontrado</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">Comece criando um novo roteiro para sua história. Organize suas ideias, personagens e cenas de forma profissional.</p>
                    <a href="{{ route('projects.create') }}" 
                       class="btn btn-primary btn-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Criar Novo Roteiro
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($projects as $project)
                        <div class="card hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 animate-fade-in">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl p-4 shadow-lg">
                                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $project->name }}</h3>
                                            <p class="text-sm text-gray-500">Criado em {{ $project->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('projects.edit', $project) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200" 
                                                    onclick="return confirm('Tem certeza que deseja excluir este roteiro?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                @if($project->description)
                                    <p class="text-gray-600 mb-4">{{ $project->description }}</p>
                                @endif

                                <div class="space-y-3">
                                    <a href="{{ route('characters.index', ['project' => $project->id]) }}" 
                                       class="block p-4 bg-gradient-to-r from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl transition-all duration-200 hover:shadow-md group">
                                        <div class="flex items-center text-purple-700 group-hover:text-purple-800">
                                            <div class="bg-purple-200 rounded-lg p-2 mr-3 group-hover:bg-purple-300 transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <span class="font-medium">Personagens</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('scenes.index', ['project' => $project->id]) }}" 
                                       class="block p-4 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-200 hover:shadow-md group">
                                        <div class="flex items-center text-blue-700 group-hover:text-blue-800">
                                            <div class="bg-blue-200 rounded-lg p-2 mr-3 group-hover:bg-blue-300 transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                                                </svg>
                                            </div>
                                            <span class="font-medium">Cenas</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('excel.index', ['project' => $project->id]) }}" 
                                       class="block p-4 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl transition-all duration-200 hover:shadow-md group">
                                        <div class="flex items-center text-green-700 group-hover:text-green-800">
                                            <div class="bg-green-200 rounded-lg p-2 mr-3 group-hover:bg-green-300 transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                            </div>
                                            <span class="font-medium">Importar Dados</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('projects.files.index', ['project' => $project->id]) }}" 
                                       class="block p-4 bg-gradient-to-r from-amber-50 to-amber-100 hover:from-amber-100 hover:to-amber-200 rounded-xl transition-all duration-200 hover:shadow-md group">
                                        <div class="flex items-center text-amber-700 group-hover:text-amber-800">
                                            <div class="bg-amber-200 rounded-lg p-2 mr-3 group-hover:bg-amber-300 transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L5.757 10.757a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                            </div>
                                            <span class="font-medium">Arquivos</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
