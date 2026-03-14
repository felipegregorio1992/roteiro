<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Arquivos') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                Voltar para Meus Roteiros
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
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

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Ocorreram os seguintes erros:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @can('update', $project)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Enviar arquivos</h3>
                            <p class="text-sm text-gray-500">Você pode enviar até 10 arquivos por vez (máx. 25MB cada).</p>
                        </div>

                        <form action="{{ route('projects.files.store', ['project' => $project->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <label for="files" class="block text-sm font-medium text-gray-700">Selecione os arquivos</label>
                                <input type="file" name="files[]" id="files" required multiple
                                       class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Tipos aceitos: PDF, Word, TXT, Excel, CSV, imagens e ZIP.</p>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Enviar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @can('update', $project)
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Documentos</h3>
                            <p class="text-sm text-gray-500">Escreva como no Word e salve dentro do projeto.</p>
                        </div>
                        @if(!empty($editingDocument))
                            <a href="{{ route('projects.files.index', ['project' => $project->id]) }}"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                Cancelar edição
                            </a>
                        @endif
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50">
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">
                                    {{ !empty($editingDocument) ? 'Editando: '.($editingDocument['original_name'] ?? '') : 'Novo documento' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Salva como arquivo HTML na área de Arquivos do roteiro
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(!empty($editingDocument))
                                    <a href="{{ route('projects.files.download', ['project' => $project->id, 'storedName' => $editingDocument['stored_name']]) }}"
                                       class="inline-flex items-center px-3 py-2 rounded-lg bg-white text-gray-700 hover:bg-gray-100 text-sm font-medium border border-gray-200">
                                        Baixar
                                    </a>
                                @endif
                            </div>
                        </div>

                        <form method="POST"
                              action="{{ !empty($editingDocument) ? route('projects.documents.update', ['project' => $project->id, 'storedName' => $editingDocument['stored_name']]) : route('projects.documents.store', ['project' => $project->id]) }}"
                              class="p-4 space-y-4">
                            @csrf
                            @if(!empty($editingDocument))
                                @method('PUT')
                            @endif

                            <div>
                                <label for="doc_title" class="block text-sm font-medium text-gray-700">Nome do documento</label>
                                <input id="doc_title"
                                       name="title"
                                       type="text"
                                       required
                                       value="{{ old('title', !empty($editingDocument) ? pathinfo((string) ($editingDocument['original_name'] ?? ''), PATHINFO_FILENAME) : '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>

                            <div class="rounded-xl bg-gray-100 p-6">
                                <div class="mx-auto w-full max-w-4xl">
                                    <div class="rounded-xl bg-white shadow-sm border border-gray-200">
                                        <div class="p-6">
                                            <textarea id="doc_content"
                                                      name="content"
                                                      class="w-full min-h-[420px] rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                      data-word-editor="1"
                                                      required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-medium">
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>

                    @endcan

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Arquivos do roteiro</h3>

                    @if($files->isEmpty())
                        <div class="text-center py-12 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                            <div class="mx-auto w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-4">
                                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <p class="text-gray-700 font-medium">Nenhum arquivo enviado ainda</p>
                            <p class="text-gray-500 text-sm mt-1">Envie arquivos acima para anexar ao seu roteiro.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($files as $file)
                                @php
                                    $size = (int) $file['size'];
                                    $humanSize = $size >= 1048576
                                        ? number_format($size / 1048576, 2, ',', '.') . ' MB'
                                        : number_format($size / 1024, 0, ',', '.') . ' KB';

                                    $original = (string) ($file['original_name'] ?? '');
                                    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

                                    $typeLabel = 'Arquivo';
                                    $iconBg = 'bg-gray-100';
                                    $iconText = 'text-gray-700';

                                    if (in_array($ext, ['pdf'], true)) {
                                        $typeLabel = 'PDF';
                                        $iconBg = 'bg-red-100';
                                        $iconText = 'text-red-700';
                                    } elseif (in_array($ext, ['doc', 'docx', 'rtf', 'txt'], true)) {
                                        $typeLabel = 'Documento';
                                        $iconBg = 'bg-blue-100';
                                        $iconText = 'text-blue-700';
                                    } elseif (in_array($ext, ['html'], true)) {
                                        $typeLabel = 'Documento';
                                        $iconBg = 'bg-blue-100';
                                        $iconText = 'text-blue-700';
                                    } elseif (in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
                                        $typeLabel = 'Planilha';
                                        $iconBg = 'bg-green-100';
                                        $iconText = 'text-green-700';
                                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                                        $typeLabel = 'Imagem';
                                        $iconBg = 'bg-purple-100';
                                        $iconText = 'text-purple-700';
                                    } elseif (in_array($ext, ['zip'], true)) {
                                        $typeLabel = 'ZIP';
                                        $iconBg = 'bg-amber-100';
                                        $iconText = 'text-amber-800';
                                    }
                                @endphp

                                <div class="border border-gray-100 rounded-xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                                    <div class="p-4 flex gap-4">
                                        <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 {{ $iconText }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <a class="text-indigo-600 hover:text-indigo-800 font-medium block truncate"
                                                       title="{{ $original }}"
                                                       href="{{ route('projects.files.download', ['project' => $project->id, 'storedName' => $file['stored_name']]) }}">
                                                        {{ $original }}
                                                    </a>
                                                    <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                            {{ $typeLabel }}
                                                        </span>
                                                        <span>{{ $humanSize }}</span>
                                                        <span>&bull;</span>
                                                        <span>{{ \Carbon\Carbon::createFromTimestamp($file['last_modified'])->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 flex items-center justify-end gap-2">
                                                @can('update', $project)
                                                    @if($ext === 'html')
                                                        <a href="{{ route('projects.files.index', ['project' => $project->id, 'doc' => $file['stored_name']]) }}"
                                                           class="inline-flex items-center px-3 py-2 rounded-lg bg-white text-gray-700 hover:bg-gray-100 text-sm font-medium border border-gray-200">
                                                            Editar
                                                        </a>
                                                    @endif
                                                @endcan

                                                <a href="{{ route('projects.files.download', ['project' => $project->id, 'storedName' => $file['stored_name']]) }}"
                                                   class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">
                                                    Baixar
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('projects.files.destroy', ['project' => $project->id, 'storedName' => $file['stored_name']]) }}"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 text-sm font-medium"
                                                            onclick="return confirm('Remover este arquivo?')">
                                                        Remover
                                                    </button>
                                                </form>
                                            </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('doc_content');
            if (!textarea) return;
            textarea.value = @json(old('content', !empty($editingDocument) ? (string) ($editingDocument['content'] ?? '') : ''));
            if (!window.RichEditor) return;
            new window.RichEditor(textarea, {
                toolbar: ['bold', 'italic', 'underline', 'link', 'list', 'quote', 'clear'],
                placeholder: 'Comece a escrever...',
                containerClass: 'word-editor',
            });
        });
    </script>
</x-app-layout>
