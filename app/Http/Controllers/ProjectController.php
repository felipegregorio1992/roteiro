<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\CacheService;
use App\Services\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                return 'Ato '.$scene->act;
            });

        $episodes = $project->episodes()
            ->with('characters')
            ->orderBy('order')
            ->get();

        return view('projects.show', compact('project', 'scenes', 'episodes'));
    }

    public function filesIndex(Project $project)
    {
        $this->authorize('view', $project);

        $basePath = $this->projectFilesBasePath($project->id);
        $disk = Storage::disk('local');

        $files = collect($disk->files($basePath))
            ->map(function (string $path) use ($disk, $basePath) {
                $storedName = Str::after($path, $basePath.'/');
                $originalName = $this->originalNameFromStoredName($storedName);

                return [
                    'stored_name' => $storedName,
                    'original_name' => $originalName,
                    'size' => $disk->size($path),
                    'last_modified' => $disk->lastModified($path),
                ];
            })
            ->sortByDesc('last_modified')
            ->values();

        return view('projects.files', [
            'project' => $project,
            'files' => $files,
        ]);
    }

    public function filesStore(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => [
                'file',
                'max:25600',
                'mimes:pdf,doc,docx,txt,rtf,xls,xlsx,csv,png,jpg,jpeg,webp,zip',
            ],
        ]);

        $basePath = $this->projectFilesBasePath($project->id);
        $disk = Storage::disk('local');

        $uploadedCount = 0;
        foreach ($validated['files'] as $file) {
            $originalName = $file->getClientOriginalName() ?: 'arquivo';
            $safeOriginalName = $this->sanitizeOriginalName($originalName);
            $storedName = now()->format('YmdHis').'_'.Str::random(10).'__'.$safeOriginalName;

            $disk->putFileAs($basePath, $file, $storedName);
            $uploadedCount++;
        }

        return redirect()
            ->route('projects.files.index', ['project' => $project->id])
            ->with('success', $uploadedCount === 1 ? 'Arquivo enviado com sucesso!' : "{$uploadedCount} arquivos enviados com sucesso!");
    }

    public function filesDownload(Project $project, string $storedName)
    {
        $this->authorize('view', $project);

        if (! $this->isSafeStoredName($storedName)) {
            abort(404);
        }

        $basePath = $this->projectFilesBasePath($project->id);
        $path = $basePath.'/'.$storedName;

        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->download($path, $this->originalNameFromStoredName($storedName));
    }

    public function filesDestroy(Project $project, string $storedName)
    {
        $this->authorize('update', $project);

        if (! $this->isSafeStoredName($storedName)) {
            abort(404);
        }

        $basePath = $this->projectFilesBasePath($project->id);
        $path = $basePath.'/'.$storedName;

        $disk = Storage::disk('local');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        return redirect()
            ->route('projects.files.index', ['project' => $project->id])
            ->with('success', 'Arquivo removido com sucesso!');
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

    private function projectFilesBasePath(int $projectId): string
    {
        return "project_uploads/{$projectId}";
    }

    private function originalNameFromStoredName(string $storedName): string
    {
        $parts = explode('__', $storedName, 2);
        if (count($parts) === 2 && trim($parts[1]) !== '') {
            return $parts[1];
        }

        return $storedName;
    }

    private function sanitizeOriginalName(string $originalName): string
    {
        $originalName = str_replace(["\0", '/', '\\'], '-', $originalName);
        $originalName = preg_replace('/\s+/', ' ', $originalName) ?? $originalName;
        $originalName = trim($originalName);

        if ($originalName === '') {
            return 'arquivo';
        }

        if (mb_strlen($originalName) > 180) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $base = pathinfo($originalName, PATHINFO_FILENAME);
            $base = mb_substr($base, 0, 160);

            return $extension ? ($base.'.'.$extension) : $base;
        }

        return $originalName;
    }

    private function isSafeStoredName(string $storedName): bool
    {
        if ($storedName !== basename($storedName)) {
            return false;
        }

        return ! str_contains($storedName, '..') && ! str_contains($storedName, '/')
            && ! str_contains($storedName, '\\') && $storedName !== '';
    }
}
