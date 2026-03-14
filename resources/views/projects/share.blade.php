<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Compartilhar Acesso') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Projeto: {{ $project->name }}</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('projects.edit', $project) }}" class="text-gray-600 hover:text-gray-900">
                    Editar
                </a>
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-8">
                    @if (session('invite_url'))
                        <div class="rounded-md bg-green-50 p-4">
                            <div class="text-sm text-green-800 font-medium">Link de convite gerado</div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="text" value="{{ session('invite_url') }}" readonly
                                       class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                                <a href="{{ session('invite_url') }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Abrir
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Acesso ao roteiro</h3>
                            <p class="mt-1 text-sm text-gray-600">Convide outros usuários para acessar este roteiro.</p>

                            <form method="POST" action="{{ route('projects.invites.store', $project) }}" class="mt-6 space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="invite_email" :value="__('Email do usuário')" />
                                    <x-text-input id="invite_email" name="email" type="email"
                                                 class="mt-1 block w-full"
                                                 value="{{ old('email') }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                </div>

                                <div>
                                    <x-input-label for="invite_role" :value="__('Permissão')" />
                                    <select id="invite_role" name="role"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="viewer">Leitor</option>
                                        <option value="editor">Editor</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                                </div>

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Gerar convite
                                </button>
                            </form>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Membros</h3>

                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between rounded-md bg-white p-3 border border-gray-200">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $project->user->name ?? 'Dono' }}</div>
                                        <div class="text-xs text-gray-600">{{ $project->user->email ?? '' }}</div>
                                    </div>
                                    <div class="text-xs font-semibold text-gray-700">Dono</div>
                                </div>

                                @forelse($members as $member)
                                    <div class="flex items-center justify-between rounded-md bg-white p-3 border border-gray-200">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                            <div class="text-xs text-gray-600">{{ $member->email }}</div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <form method="POST" action="{{ route('projects.members.update', ['project' => $project->id, 'user' => $member->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role"
                                                        class="text-xs font-semibold text-gray-700 border-gray-300 rounded-md py-1 pl-2 pr-6 focus:border-indigo-500 focus:ring-indigo-500"
                                                        onchange="this.form.submit()">
                                                    <option value="viewer" @selected(($member->pivot->role ?? 'viewer') === 'viewer')>Leitor</option>
                                                    <option value="editor" @selected(($member->pivot->role ?? 'viewer') === 'editor')>Editor</option>
                                                </select>
                                            </form>
                                            <form method="POST" action="{{ route('projects.members.destroy', ['project' => $project->id, 'user' => $member->id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs text-red-600 hover:text-red-700"
                                                        >
                                                    Remover
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-600">Nenhum membro convidado ainda.</div>
                                @endforelse
                            </div>

                            <h4 class="mt-8 text-sm font-semibold text-gray-900">Convites pendentes</h4>
                            <div class="mt-3 space-y-2">
                                @forelse($pendingInvites as $invite)
                                    <div class="flex items-center justify-between rounded-md bg-white p-3 border border-gray-200">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $invite->email }}</div>
                                            <div class="text-xs text-gray-600">
                                                Expira em {{ $invite->expires_at?->format('d/m/Y H:i') ?? 'sem expiração' }}
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-xs font-semibold text-gray-700">
                                                {{ $invite->role === 'editor' ? 'Editor' : 'Leitor' }}
                                            </div>
                                            <form method="POST" action="{{ route('projects.invites.destroy', ['project' => $project->id, 'invitation' => $invite->id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs text-red-600 hover:text-red-700"
                                                        >
                                                    Cancelar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-600">Nenhum convite pendente.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
