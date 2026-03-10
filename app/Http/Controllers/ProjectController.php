<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Services\CacheService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends BaseController
{
    use AuthorizesRequests;

    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        $projects = CacheService::getUserProjects(Auth::id());
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(CreateProjectRequest $request)
    {
        $validated = $request->validated();

        $project = $this->projectService->createProject($validated);

        $this->logActivity('Project created', ['project_id' => $project->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Roteiro criado com sucesso!');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        
        $scenes = $project->scenes()
            ->with('characters')
            ->orderBy('order')
            ->get()
            ->groupBy(function ($scene) {
                return 'Ato ' . $scene->act;
            });

        $episodes = $project->episodes()
            ->with('characters')
            ->orderBy('order')
            ->get();

        return view('projects.show', compact('project', 'scenes', 'episodes'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validated();

        $this->projectService->updateProject($project, $validated);

        $this->logActivity('Project updated', ['project_id' => $project->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Roteiro atualizado com sucesso!');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        $this->logActivity('Project deleted', ['project_id' => $project->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Roteiro excluído com sucesso!');
    }
} 