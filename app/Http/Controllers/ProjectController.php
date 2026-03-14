<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
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

    public function filesIndex(Request $request, Project $project)
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

        $editingDocument = null;
        $docToEdit = $request->query('doc');
        if (is_string($docToEdit) && $docToEdit !== '') {
            if (! $this->isSafeStoredName($docToEdit)) {
                abort(404);
            }

            $docPath = $basePath.'/'.$docToEdit;
            if (! $disk->exists($docPath)) {
                abort(404);
            }

            $editingDocument = [
                'stored_name' => $docToEdit,
                'original_name' => $this->originalNameFromStoredName($docToEdit),
                'content' => $disk->get($docPath),
            ];
        }

        return view('projects.files', [
            'project' => $project,
            'files' => $files,
            'editingDocument' => $editingDocument,
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

    public function documentsStore(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:200000'],
        ]);

        $basePath = $this->projectFilesBasePath($project->id);
        $disk = Storage::disk('local');

        $titleWithExtension = $validated['title'];
        if (! Str::endsWith(Str::lower($titleWithExtension), '.html')) {
            $titleWithExtension .= '.html';
        }

        $safeOriginalName = $this->sanitizeOriginalName($titleWithExtension);
        $storedName = now()->format('YmdHis').'_'.Str::random(10).'__'.$safeOriginalName;
        $path = $basePath.'/'.$storedName;

        $disk->put($path, $validated['content']);

        return redirect()
            ->route('projects.files.index', ['project' => $project->id])
            ->with('success', 'Documento salvo com sucesso!');
    }

    public function documentsUpdate(Request $request, Project $project, string $storedName)
    {
        $this->authorize('update', $project);

        if (! $this->isSafeStoredName($storedName)) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:200000'],
        ]);

        $basePath = $this->projectFilesBasePath($project->id);
        $disk = Storage::disk('local');

        $currentPath = $basePath.'/'.$storedName;
        if (! $disk->exists($currentPath)) {
            abort(404);
        }

        $parts = explode('__', $storedName, 2);
        $prefix = $parts[0] ?? now()->format('YmdHis').'_'.Str::random(10);

        $titleWithExtension = $validated['title'];
        if (! Str::endsWith(Str::lower($titleWithExtension), '.html')) {
            $titleWithExtension .= '.html';
        }

        $safeOriginalName = $this->sanitizeOriginalName($titleWithExtension);
        $nextStoredName = $prefix.'__'.$safeOriginalName;

        $nextPath = $basePath.'/'.$nextStoredName;
        if ($nextStoredName !== $storedName) {
            if ($disk->exists($nextPath)) {
                $nextStoredName = $prefix.'_'.Str::random(5).'__'.$safeOriginalName;
                $nextPath = $basePath.'/'.$nextStoredName;
            }

            $disk->move($currentPath, $nextPath);
        }

        $disk->put($nextPath, $validated['content']);

        return redirect()
            ->route('projects.files.index', ['project' => $project->id])
            ->with('success', 'Documento atualizado com sucesso!');
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function share(Project $project)
    {
        $this->authorize('update', $project);

        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $members = $project->members()
            ->orderBy('name')
            ->get();

        $pendingInvites = $project->invitations()
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->get();

        return view('projects.share', compact('project', 'members', 'pendingInvites'));
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

    public function invitesStore(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'role' => ['required', 'in:viewer,editor'],
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($existingUser->id === $project->user_id || $project->members()->whereKey($existingUser->id)->exists()) {
                return redirect()
                    ->route('projects.share', $project)
                    ->with('error', 'Este usuário já possui acesso a este roteiro.');
            }
        }

        $pending = ProjectInvitation::where('project_id', $project->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        $token = Str::random(64);

        $payload = [
            'invited_by_user_id' => Auth::id(),
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'role' => $validated['role'],
            'expires_at' => now()->addDays(7),
        ];

        if ($pending) {
            $pending->update($payload);
        } else {
            ProjectInvitation::create([
                'project_id' => $project->id,
                ...$payload,
            ]);
        }

        $inviteUrl = route('invites.accept', ['token' => $token]);

        if ($existingUser) {
            return redirect()
                ->route('projects.share', $project)
                ->with('success', 'Convite enviado. O usuário verá no painel para aceitar ou rejeitar.');
        }

        return redirect()
            ->route('projects.share', $project)
            ->with('success', 'Convite criado com sucesso!')
            ->with('invite_url', $inviteUrl);
    }

    public function invitesDestroy(Project $project, ProjectInvitation $invitation)
    {
        $this->authorize('update', $project);

        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        if ($invitation->project_id !== $project->id) {
            abort(404);
        }

        if ($invitation->accepted_at) {
            abort(404);
        }

        $invitation->delete();

        return redirect()
            ->route('projects.share', $project)
            ->with('success', 'Convite cancelado com sucesso!');
    }

    public function acceptInvite(string $token)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $invitation = ProjectInvitation::where('token_hash', hash('sha256', $token))->firstOrFail();

        if ($invitation->accepted_at) {
            abort(404);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(404);
        }

        if (mb_strtolower($user->email) !== mb_strtolower($invitation->email)) {
            abort(403);
        }

        $project = $invitation->project;
        if (! $project) {
            abort(404);
        }

        $project->members()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role],
        ]);

        $invitation->update([
            'accepted_at' => now(),
        ]);

        CacheService::clearProjectCache($project->id, $user->id);

        return redirect()->route('dashboard')
            ->with('success', 'Convite aceito com sucesso!');
    }

    public function acceptInviteById(ProjectInvitation $invitation)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($invitation->accepted_at) {
            abort(404);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(404);
        }

        if (mb_strtolower($user->email) !== mb_strtolower($invitation->email)) {
            abort(403);
        }

        $project = $invitation->project;
        if (! $project) {
            abort(404);
        }

        if ($project->members()->whereKey($user->id)->exists()) {
            $project->members()->updateExistingPivot($user->id, [
                'role' => $invitation->role,
            ]);
        } else {
            $project->members()->syncWithoutDetaching([
                $user->id => ['role' => $invitation->role],
            ]);
        }

        $invitation->update([
            'accepted_at' => now(),
        ]);

        CacheService::clearProjectCache($project->id, $user->id);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Convite aceito com sucesso!');
    }

    public function rejectInviteById(ProjectInvitation $invitation)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($invitation->accepted_at) {
            abort(404);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(404);
        }

        if (mb_strtolower($user->email) !== mb_strtolower($invitation->email)) {
            abort(403);
        }

        $projectId = $invitation->project_id;
        $invitation->delete();

        CacheService::clearProjectCache($projectId, $user->id);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Convite recusado com sucesso!');
    }

    public function membersUpdate(Request $request, Project $project, \App\Models\User $user)
    {
        $this->authorize('update', $project);

        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        if ($user->id === $project->user_id) {
            abort(400);
        }

        if (! $project->members()->whereKey($user->id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:viewer,editor'],
        ]);

        $project->members()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        CacheService::clearProjectCache($project->id, $user->id);

        return redirect()
            ->route('projects.share', $project)
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    public function membersDestroy(Project $project, \App\Models\User $user)
    {
        $this->authorize('update', $project);

        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        if ($user->id === $project->user_id) {
            abort(400);
        }

        $project->members()->detach($user->id);
        CacheService::clearProjectCache($project->id, $user->id);

        return redirect()
            ->route('projects.share', $project)
            ->with('success', 'Acesso removido com sucesso!');
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
