<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Falas de {{ $character->name }}
                                </h3>
                                
                                <!-- Form -->
                                <div class="mt-4 mb-4 bg-gray-50 p-3 rounded-md">
                                    <div class="grid grid-cols-1 gap-3">
                                        <textarea wire:model="content" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Digite a fala..."></textarea>
                                        @error('content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        
                                        <select wire:model="target_character_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Interagindo com (opcional)</option>
                                            @foreach($project_characters as $char)
                                                <option value="{{ $char->id }}">{{ $char->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 text-right">
                                        <button wire:click="save" type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Adicionar
                                        </button>
                                    </div>
                                </div>

                                <!-- List -->
                                <div class="mt-4 border-t border-gray-200 pt-4 max-h-60 overflow-y-auto">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Falas Registradas</h4>
                                    @if($character->dialogues->isEmpty())
                                        <p class="text-sm text-gray-500 text-center py-4">Nenhuma fala cadastrada.</p>
                                    @else
                                        <ul class="divide-y divide-gray-200">
                                            @foreach($character->dialogues as $dialogue)
                                                <li class="py-2 flex justify-between group items-start">
                                                    <div class="pr-2">
                                                        <p class="text-sm text-gray-900">{{ $dialogue->content }}</p>
                                                        @if($dialogue->targetCharacter)
                                                            <p class="text-xs text-indigo-600 mt-1">Interação com: {{ $dialogue->targetCharacter->name }}</p>
                                                        @endif
                                                    </div>
                                                    <button wire:click="delete({{ $dialogue->id }})" class="text-gray-400 hover:text-red-600 p-1" title="Excluir">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
