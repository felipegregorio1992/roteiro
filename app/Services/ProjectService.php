<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    /**
     * Create a new project.
     */
    public function createProject(array $data): Project
    {
        $project = Auth::user()->projects()->create($data);

        CacheService::clearUserCache(Auth::id());

        return $project;
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Project $project, array $data): bool
    {
        $updated = $project->update($data);

        if ($updated) {
            // Clear user cache as name/description changed
            CacheService::clearUserCache(Auth::id());
        }

        return $updated;
    }

    /**
     * Delete a project.
     */
    public function deleteProject(Project $project): ?bool
    {
        $deleted = $project->delete();

        if ($deleted) {
            CacheService::clearUserCache(Auth::id());
        }

        return $deleted;
    }
}
