<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Scene;
use App\Models\Project;
use App\Services\CacheService;
use App\Services\CharacterService;
use App\Http\Requests\CreateCharacterRequest;
use App\Http\Requests\UpdateCharacterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CharacterController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected CharacterService $characterService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }

        // Autorização via Policy
        $this->authorize('view', $project);

        // Busca os personagens usando cache otimizado
        $characters = CacheService::getProjectCharacters($project->id, $user->id);

        // Processar conteúdo dos atos para cada personagem
        foreach ($characters as $character) {
            $character->act_contents = $this->characterService->getCharacterActContents($character, $project->id);
        }

        return view('characters.index', compact('characters', 'project'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }
        
        // Autorização via Policy
        $this->authorize('view', $project);

        return view('characters.create', compact('project'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCharacterRequest $request)
    {
        $validated = $request->validated();

        $character = $this->characterService->createCharacter($validated);

        $this->logActivity('Character created', ['character_id' => $character->id]);

        return redirect()->route('characters.show', ['character' => $character, 'project' => $validated['project_id']])
            ->with('success', 'Personagem criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Character $character, Request $request)
    {
        $this->authorize('view', $character);
        
        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }

        // Verifica se o personagem pertence ao projeto
        if ($character->project_id != $project->id) {
            abort(404, 'Personagem não encontrado neste projeto.');
        }
        
        // Carrega as cenas ordenadas
        $character->load(['scenes' => function($query) {
            $query->orderBy('order', 'asc');
        }]);
        
        return view('characters.show', compact('character', 'project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Character $character, Request $request)
    {
        $this->authorize('update', $character);

        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }

        // Verifica se o personagem pertence ao projeto
        if ($character->project_id != $project->id) {
            abort(404, 'Personagem não encontrado neste projeto.');
        }

        return view('characters.edit', compact('character', 'project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCharacterRequest $request, Character $character)
    {
        $this->authorize('update', $character);

        $validated = $request->validated();

        $this->characterService->updateCharacter($character, $validated);

        $this->logActivity('Character updated', ['character_id' => $character->id]);

        return redirect()->route('characters.show', ['character' => $character, 'project' => $validated['project_id']])
            ->with('success', 'Personagem atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Character $character, Request $request)
    {
        $this->authorize('delete', $character);

        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }

        // Verifica se o personagem pertence ao projeto
        if ($character->project_id != $project->id) {
            abort(404, 'Personagem não encontrado neste projeto.');
        }

        $this->characterService->deleteCharacter($character);
        
        $this->logActivity('Character deleted', ['character_id' => $character->id]);

        return redirect()->route('characters.index', ['project' => $project->id])
            ->with('success', 'Personagem excluído com sucesso!');
    }

    /**
     * Remove dialogue from a scene for this character (soft delete/hide)
     */
    public function removeDialogue(Character $character, Scene $scene, Request $request)
    {
        $this->authorize('update', $character);

        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }

        // Verifica se o personagem e a cena pertencem ao projeto
        if ($character->project_id != $project->id || $scene->project_id != $project->id) {
            abort(404, 'Personagem ou cena não encontrada neste projeto.');
        }

        // Atualiza o pivot ocultando o diálogo em vez de apagar
        $character->scenes()->updateExistingPivot($scene->id, ['is_hidden' => true]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Diálogo ocultado com sucesso!']);
        }

        return back()->with('success', 'Diálogo ocultado com sucesso!');
    }

    /**
     * Restore hidden dialogue
     */
    public function restoreDialogue(Character $character, Scene $scene, Request $request)
    {
        $this->authorize('update', $character);
        
        $project = $this->getProjectOrRedirect($request);
        if ($project instanceof \Illuminate\Http\RedirectResponse) {
            return $project;
        }

        $character->scenes()->updateExistingPivot($scene->id, ['is_hidden' => false]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Diálogo restaurado com sucesso!']);
        }

        return back()->with('success', 'Diálogo restaurado com sucesso!');
    }

    /**
     * Helper to get project from request or return redirect
     */
    private function getProjectOrRedirect(Request $request)
    {
        $projectId = $request->query('project');
        if (!$projectId) {
            return redirect()->route('projects.index')
                ->with('error', 'Por favor, selecione um projeto.');
        }

        // Use validateProjectAccess from BaseController which checks ownership
        // Note: validateProjectAccess returns Project model or aborts
        try {
            return $this->validateProjectAccess($projectId, Auth::id());
        } catch (\Exception $e) {
            // If it's a 403 abort, we might want to handle it or let it bubble
            throw $e;
        }
    }
}
