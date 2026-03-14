<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCharacterToSceneRequest;
use App\Http\Requests\CreateActRequest;
use App\Http\Requests\CreateSceneRequest;
use App\Http\Requests\ReorderScenesRequest;
use App\Http\Requests\UpdateActTitleRequest;
use App\Http\Requests\UpdateSceneRequest;
use App\Models\Character;
use App\Models\Project;
use App\Models\Scene;
use App\Services\SceneService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SceneController extends Controller
{
    use AuthorizesRequests;

    protected $sceneService;

    public function __construct(SceneService $sceneService)
    {
        $this->sceneService = $sceneService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Redireciona para episódios como solicitado pelo usuário que deseja substituir o sistema de cenas
        $project = $this->getProjectOrAbort($request);

        return redirect()->route('episodes.index', ['project' => $project->id]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $project = $this->getProjectOrAbort($request);
        $episodeId = $request->input('episode_id');

        $this->authorize('update', $project);
        $this->authorize('create', Scene::class);

        $characters = Character::where('project_id', $project->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('scenes.create', compact('characters', 'project', 'episodeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSceneRequest $request)
    {
        $this->authorize('create', Scene::class);

        $validated = $request->validated();

        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('view', $project);

        $scene = $this->sceneService->createScene($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cena criada com sucesso!',
                'scene' => $scene,
            ]);
        }

        if (isset($validated['episode_id'])) {
            return redirect()->route('episodes.show', ['episode' => $validated['episode_id'], 'project' => $validated['project_id']])
                ->with('success', 'Cena criada e adicionada ao episódio com sucesso!');
        }

        return redirect()->route('scenes.show', ['scene' => $scene, 'project' => $validated['project_id']])
            ->with('success', 'Cena criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Scene $scene, Request $request)
    {
        $this->authorize('view', $scene);

        $project = $this->getProjectOrAbort($request);

        // Verifica se a cena pertence ao projeto
        if ($scene->project_id != $project->id) {
            abort(404, 'Cena não encontrada neste projeto.');
        }

        $scene->load(['characters' => function ($query) {
            $query->orderBy('name', 'asc');
        }]);

        return view('scenes.show', compact('scene', 'project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scene $scene, Request $request)
    {
        $this->authorize('update', $scene);

        $project = $this->getProjectOrAbort($request);

        // Verifica se a cena pertence ao projeto
        if ($scene->project_id != $project->id) {
            abort(404, 'Cena não encontrada neste projeto.');
        }

        $characters = Character::where('project_id', $project->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('scenes.edit', compact('scene', 'characters', 'project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSceneRequest $request, Scene $scene)
    {
        $this->authorize('update', $scene);

        $validated = $request->validated();

        // Project authorization is already handled in FormRequest

        $this->sceneService->updateScene($scene, $validated);

        return redirect()->route('scenes.show', ['scene' => $scene, 'project' => $validated['project_id']])
            ->with('success', 'Cena atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scene $scene, Request $request)
    {
        $this->authorize('delete', $scene);

        $project = $this->getProjectOrAbort($request);

        // Verifica se a cena pertence ao projeto
        if ($scene->project_id != $project->id) {
            abort(404, 'Cena não encontrada neste projeto.');
        }

        $this->sceneService->deleteScene($scene);

        return redirect()->route('scenes.index', ['project' => $project->id])
            ->with('success', 'Cena excluída com sucesso!');
    }

    public function addCharacter(AddCharacterToSceneRequest $request, Scene $scene)
    {
        $this->authorize('update', $scene);

        $validated = $request->validated();

        $this->sceneService->addCharacterToScene($scene, $validated['character_id'], $validated['dialogue'] ?? null);

        return back()->with('success', 'Personagem adicionado à cena!');
    }

    public function removeCharacter(Scene $scene, Character $character)
    {
        $this->authorize('update', $scene);

        $this->sceneService->removeCharacterFromScene($scene, $character->id);

        return back()->with('success', 'Personagem removido da cena!');
    }

    /**
     * Create a new act
     */
    public function createAct(CreateActRequest $request)
    {
        $this->authorize('create', Scene::class);

        $validated = $request->validated();

        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('update', $project);

        $scene = $this->sceneService->createAct(
            $validated['project_id'],
            $validated['act_number'],
            $validated['act_title'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Ato {$validated['act_number']} criado com sucesso!",
                'scene' => $scene,
            ]);
        }

        return redirect()->route('scenes.index', ['project' => $validated['project_id']])
            ->with('success', "Ato {$validated['act_number']} criado com sucesso!");
    }

    /**
     * Update act title
     */
    public function updateActTitle(UpdateActTitleRequest $request)
    {
        $this->authorize('create', Scene::class);

        $validated = $request->validated();

        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('view', $project);

        $this->sceneService->updateActTitle($validated['project_id'], $validated['act_number'], $validated['act_title']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Nome do ato {$validated['act_number']} atualizado com sucesso!",
            ]);
        }

        return redirect()->route('scenes.index', ['project' => $validated['project_id']])
            ->with('success', "Nome do ato {$validated['act_number']} atualizado com sucesso!");
    }

    /**
     * Reorder scenes within an act
     */
    public function reorder(ReorderScenesRequest $request)
    {
        $validated = $request->validated();

        $actNumber = $validated['act_number'];
        $scenes = $validated['scenes'];

        // Use project_id from request if available, otherwise fallback to user's current project (legacy support)
        $projectId = $validated['project_id'] ?? Auth::user()->current_project_id;

        if (! $projectId) {
            return response()->json(['message' => 'Projeto não identificado.'], 400);
        }

        try {
            $this->sceneService->reorderScenes($projectId, $actNumber, $scenes);

            Log::info('Ordem das cenas atualizada', [
                'act_number' => $actNumber,
                'project_id' => $projectId,
                'scenes_count' => count($scenes),
            ]);

            return response()->json(['message' => 'Ordem atualizada com sucesso!']);
        } catch (\Exception $e) {
            Log::error('Erro ao reordenar cenas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro ao salvar a ordem das cenas.'], 500);
        }
    }

    /**
     * Export scenes to Excel
     */
    public function export(Project $project)
    {
        $this->authorize('view', $project);

        return Excel::download(new \App\Exports\ProjectExport($project), 'roteiro_completo.xlsx');
    }

    /**
     * Helper to get project from request or abort
     */
    private function getProjectOrAbort(Request $request): Project
    {
        // Tenta obter o ID do projeto de várias fontes: input (form/query), rota ou query string explícita
        $projectId = $request->input('project_id')
            ?? $request->input('project')
            ?? $request->route('project');

        if (! $projectId) {
            abort(400, 'Por favor, selecione um projeto.');
        }

        // Se o parâmetro da rota já for o objeto Project (binding explícito)
        if ($projectId instanceof Project) {
            return $projectId;
        }

        return Project::findOrFail($projectId);
    }
}
