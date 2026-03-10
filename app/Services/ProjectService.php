<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    /**
     * Create a new project.
     *
     * @param array $data
     * @return Project
     */
    public function createProject(array $data): Project
    {
        $project = Auth::user()->projects()->create($data);

        CacheService::clearUserCache(Auth::id());

        return $project;
    }

    /**
     * Update an existing project.
     *
     * @param Project $project
     * @param array $data
     * @return bool
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
     *
     * @param Project $project
     * @return bool|null
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
