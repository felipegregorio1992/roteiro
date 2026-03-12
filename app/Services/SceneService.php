<?php

namespace App\Services;

use App\Models\Scene;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SceneService
{
    /**
     * Get scenes for a specific project.
     */
    public function getProjectScenes(int $projectId, int $userId): Collection
    {
        return CacheService::getProjectScenes($projectId, $userId);
    }

    /**
     * Get scenes grouped by act.
     */
    public function getScenesGroupedByAct(int $projectId, int $userId): array
    {
        $scenes = $this->getProjectScenes($projectId, $userId);

        $acts = [];

        foreach ($scenes as $scene) {
            // Use the act column if available, otherwise fallback to parsing title or default to 1
            $actNumber = $scene->act ?? 1;

            // Fallback for legacy data without act column populated (if migration just ran but data wasn't updated)
            if ($actNumber === 1 && preg_match('/Ato (\d+)/', $scene->title, $matches)) {
                $actNumber = (int) $matches[1];
            }

            if (! isset($acts[$actNumber])) {
                $acts[$actNumber] = [
                    'title' => "Ato {$actNumber}",
                    'scenes' => [],
                ];
            }

            $acts[$actNumber]['scenes'][] = $scene;
        }

        // Sort acts by number
        ksort($acts);

        return $acts;
    }

    /**
     * Create a new act (represented by an initial scene).
     */
    public function createAct(int $projectId, int $actNumber, ?string $actTitle = null, ?int $episodeId = null): Scene
    {
        $title = ! empty($actTitle) ?
            "Ato {$actNumber} - {$actTitle}" :
            "Ato {$actNumber}";

        $sceneData = [
            'project_id' => $projectId,
            'episode_id' => $episodeId,
            'title' => $title,
            'description' => "Início do Ato {$actNumber}",
            'duration' => 0, // Duração zero para indicar separador
            'order' => ($actNumber * 1000), // Usa múltiplos de 1000 para ordenação base
            'act' => $actNumber,
        ];

        return $this->createScene($sceneData);
    }

    /**
     * Create a new scene.
     */
    public function createScene(array $data): Scene
    {
        // Calculate act if not provided
        $act = $data['act'] ?? 1;

        // Remove auxiliary fields not in fillable
        if (isset($data['act_number'])) {
            unset($data['act_number']);
        }

        $data['act'] = $act;

        // Ensure user_id is set
        if (! isset($data['user_id'])) {
            $data['user_id'] = Auth::id();
        }

        $scene = Scene::create($data);

        // Attach characters if provided
        if (! empty($data['characters'])) {
            $dialogues = $data['dialogues'] ?? [];
            $characters = collect($data['characters'])->mapWithKeys(function ($characterId) use ($dialogues) {
                // Check if dialogue is provided for this character
                $dialogue = $dialogues[$characterId] ?? null;

                return [$characterId => ['dialogue' => $dialogue]];
            })->all();

            $scene->characters()->attach($characters);
        }

        // Invalidate cache
        $this->invalidateProjectCache($data['project_id']);

        return $scene;
    }

    /**
     * Update an existing scene.
     */
    public function updateScene(Scene $scene, array $data): bool
    {
        // Handle act update if provided via act_number
        if (isset($data['act_number'])) {
            $data['act'] = $data['act_number'];
            unset($data['act_number']);
        }

        $updated = $scene->update($data);

        // Sync characters if provided
        if (isset($data['characters'])) {
            $dialogues = $data['dialogues'] ?? [];
            $characters = collect($data['characters'])->mapWithKeys(function ($characterId) use ($dialogues) {
                // Check if dialogue is provided for this character
                $dialogue = $dialogues[$characterId] ?? null;

                return [$characterId => ['dialogue' => $dialogue]];
            })->all();

            $scene->characters()->sync($characters);
            $updated = true; // Mark as updated if characters changed
        }

        if ($updated) {
            $this->invalidateProjectCache($scene->project_id);
        }

        return $updated;
    }

    /**
     * Add character to scene.
     */
    public function addCharacterToScene(Scene $scene, int $characterId, ?string $dialogue = null): void
    {
        $scene->characters()->attach($characterId, [
            'dialogue' => $dialogue,
        ]);
        $this->invalidateProjectCache($scene->project_id);
    }

    /**
     * Remove character from scene.
     */
    public function removeCharacterFromScene(Scene $scene, int $characterId): void
    {
        $scene->characters()->detach($characterId);
        $this->invalidateProjectCache($scene->project_id);
    }

    /**
     * Delete a scene.
     */
    public function deleteScene(Scene $scene): ?bool
    {
        $projectId = $scene->project_id;
        $deleted = $scene->delete();

        if ($deleted) {
            $this->invalidateProjectCache($projectId);
        }

        return $deleted;
    }

    /**
     * Update act titles.
     * Updates the title of the act header scene (duration = 0).
     *
     * @return int Number of updated scenes
     */
    public function updateActTitle(int $projectId, int $actNumber, string $newTitle): int
    {
        $formattedTitle = "Ato {$actNumber} - {$newTitle}";

        // Update only the "header" scene (duration = 0) for this act
        $updated = Scene::where('project_id', $projectId)
            ->where('user_id', Auth::id())
            ->where('act', $actNumber)
            ->where('duration', 0) // Identify act header
            ->update(['title' => $formattedTitle]);

        if ($updated) {
            $this->invalidateProjectCache($projectId);
        }

        return $updated;
    }

    /**
     * Reorder scenes within an act.
     *
     * @param  array  $scenesData  Array of ['id' => int, 'order' => int]
     */
    public function reorderScenes(int $projectId, int $actNumber, array $scenesData): void
    {
        if (empty($scenesData)) {
            return;
        }

        DB::beginTransaction();
        try {
            $cases = [];
            $ids = [];
            $params = [];

            foreach ($scenesData as $sceneData) {
                $id = (int) $sceneData['id'];
                $order = (int) $sceneData['order'];

                $cases[] = 'WHEN id = ? THEN ?';
                $params[] = $id;
                $params[] = $order;
                $ids[] = $id;
            }

            if (! empty($ids)) {
                $idsStr = implode(',', $ids);
                // Also update the act column to ensure scenes are in the correct act
                $query = 'UPDATE scenes SET `act` = ?, `order` = CASE '.implode(' ', $cases)." END WHERE id IN ($idsStr) AND project_id = ?";

                // Prepend actNumber to params
                array_unshift($params, $actNumber);
                $params[] = $projectId;

                DB::update($query, $params);
            }

            $this->invalidateProjectCache($projectId);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Swap the position of two acts.
     */
    public function swapActs(int $projectId, int $actA, int $actB): bool
    {
        DB::beginTransaction();
        try {
            // Use a temporary act number to avoid constraint violations or mixing
            // Find a safe temporary act number (e.g., negative or very large)
            $tempAct = -999;

            // Move Act A scenes to Temp
            DB::table('scenes')
                ->where('project_id', $projectId)
                ->where('act', $actA)
                ->update(['act' => $tempAct]);

            // Move Act B scenes to Act A
            DB::table('scenes')
                ->where('project_id', $projectId)
                ->where('act', $actB)
                ->update(['act' => $actA]);

            // Move Temp scenes (originally Act A) to Act B
            DB::table('scenes')
                ->where('project_id', $projectId)
                ->where('act', $tempAct)
                ->update(['act' => $actB]);

            // Also update titles if they contain "Ato X"
            // This is a bit tricky because titles might be custom.
            // Ideally, we should regenerate the "Ato X" prefix, but let's stick to just swapping the 'act' column for now.
            // However, usually the first scene of an act is a header like "Ato 1".
            // If we swap acts, we should probably update those headers if they follow the pattern.

            // Let's rely on the View to display "Ato X" correctly based on the key,
            // but the Scene titles in the DB might still say "Ato 1 - Intro".
            // Let's attempt to fix the prefixes for the header scenes (duration = 0)

            $this->updateActHeaderTitle($projectId, $actA);
            $this->updateActHeaderTitle($projectId, $actB);

            $this->invalidateProjectCache($projectId);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    private function updateActHeaderTitle(int $projectId, int $actNumber)
    {
        // Find the header scene for this act
        $headerScene = Scene::where('project_id', $projectId)
            ->where('act', $actNumber)
            ->where('duration', 0)
            ->first();

        if ($headerScene) {
            // Check if title starts with "Ato X"
            if (preg_match('/^Ato \d+(.*)/', $headerScene->title, $matches)) {
                $suffix = $matches[1]; // " - Intro" or similar
                $newTitle = "Ato {$actNumber}{$suffix}";
                $headerScene->update(['title' => $newTitle]);
            }
        }
    }

    /**
     * Invalidate cache for a project.
     */
    public function invalidateProjectCache(int $projectId): void
    {
        CacheService::clearProjectCache($projectId, Auth::id());
        CacheService::clearProjectScenesCache($projectId, Auth::id());
    }
}
