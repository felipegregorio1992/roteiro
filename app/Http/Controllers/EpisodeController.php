<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEpisodeRequest;
use App\Http\Requests\UpdateEpisodeRequest;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Project;
use App\Services\EpisodeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EpisodeController extends Controller
{
    use AuthorizesRequests;

    protected $episodeService;

    public function __construct(EpisodeService $episodeService)
    {
        $this->episodeService = $episodeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project = $this->getProjectOrAbort($request);
        $this->authorize('view', $project);

        $episodes = $this->episodeService->getProjectEpisodes($project->id);

        return view('episodes.index', compact('project', 'episodes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $project = $this->getProjectOrAbort($request);
        $this->authorize('view', $project);
        $this->authorize('create', Episode::class);

        // Calculate next episode number
        $lastEpisode = Episode::where('project_id', $project->id)
            ->orderBy('episode_number', 'desc')
            ->first();

        $nextEpisodeNumber = $lastEpisode ? ($lastEpisode->episode_number + 1) : 1;

        $characters = Character::where('project_id', $project->id)
            ->where('user_id', Auth::id())
            ->orderBy('name', 'asc')
            ->get();

        return view('episodes.create', compact('characters', 'project', 'nextEpisodeNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEpisodeRequest $request)
    {
        $this->authorize('create', Episode::class);

        $validated = $request->validated();

        // If update_characters flag is present but characters array is missing (e.g. all unchecked),
        // ensure we pass an empty array to the service.
        if ($request->has('update_characters') && ! array_key_exists('characters', $validated)) {
            $validated['characters'] = [];
        }

        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('view', $project);

        $episode = $this->episodeService->createEpisode($validated);

        return redirect()->route('episodes.show', ['episode' => $episode, 'project' => $validated['project_id']])
            ->with('success', 'Episódio criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Episode $episode, Request $request)
    {
        $this->authorize('view', $episode);

        $project = $this->getProjectOrAbort($request);

        // Verifica se o episódio pertence ao projeto
        if ($episode->project_id != $project->id) {
            abort(404, 'Episódio não encontrado neste projeto.');
        }

        // Verifica se o usuário tem acesso ao projeto
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para acessar este projeto.');
        }

        $episode->load(['characters' => function ($query) {
            $query->orderBy('name', 'asc');
        }]);

        $acts = $this->episodeService->getEpisodeScenesGroupedByAct($episode);

        return view('episodes.show', compact('episode', 'project', 'acts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Episode $episode, Request $request)
    {
        $this->authorize('update', $episode);

        $project = $this->getProjectOrAbort($request);

        // Verifica se o episódio pertence ao projeto
        if ($episode->project_id != $project->id) {
            abort(404, 'Episódio não encontrado neste projeto.');
        }

        // Verifica se o usuário tem acesso ao projeto
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para acessar este projeto.');
        }

        $characters = Character::where('project_id', $project->id)
            ->where('user_id', Auth::id())
            ->orderBy('name', 'asc')
            ->get();

        return view('episodes.edit', compact('episode', 'characters', 'project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEpisodeRequest $request, Episode $episode)
    {
        $this->authorize('update', $episode);

        $validated = $request->validated();

        // If update_characters flag is present but characters array is missing (e.g. all unchecked),
        // ensure we pass an empty array to the service.
        if ($request->has('update_characters') && ! array_key_exists('characters', $validated)) {
            $validated['characters'] = [];
        }

        // Project authorization is already handled in FormRequest

        $this->episodeService->updateEpisode($episode, $validated);

        return redirect()->route('episodes.show', ['episode' => $episode, 'project' => $validated['project_id']])
            ->with('success', 'Episódio atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Episode $episode, Request $request)
    {
        $this->authorize('delete', $episode);

        $project = $this->getProjectOrAbort($request);

        // Verifica se o episódio pertence ao projeto
        if ($episode->project_id != $project->id) {
            abort(404, 'Episódio não encontrado neste projeto.');
        }

        $this->episodeService->deleteEpisode($episode);

        return redirect()->route('episodes.index', ['project' => $project->id])
            ->with('success', 'Episódio excluído com sucesso!');
    }

    /**
     * Helper to get project from request or abort
     */
    private function getProjectOrAbort(Request $request): Project
    {
        $projectId = $request->query('project') ?? $request->input('project_id');

        if (! $projectId) {
            abort(400, 'Por favor, selecione um projeto.');
        }

        return Project::findOrFail($projectId);
    }
}
