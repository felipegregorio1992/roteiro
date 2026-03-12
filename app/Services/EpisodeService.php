<?php

namespace App\Services;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EpisodeService
{
    /**
     * Get episodes for a specific project.
     */
    public function getProjectEpisodes(int $projectId): Collection
    {
        return Episode::where('project_id', $projectId)
            ->with(['characters'])
            ->orderBy('order')
            ->orderBy('episode_number')
            ->get();
    }

    /**
     * Create a new episode.
     */
    public function createEpisode(array $data): Episode
    {
        return DB::transaction(function () use ($data) {
            $episode = new Episode([
                'project_id' => $data['project_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'duration' => $data['duration'] ?? null,
                'order' => $data['order'],
                'episode_number' => $data['episode_number'] ?? null,
            ]);

            // Set user_id if authenticated, otherwise expect it in data or handle appropriately
            $episode->user_id = auth()->id() ?? $data['user_id'] ?? null;
            $episode->save();

            if (isset($data['characters']) && is_array($data['characters'])) {
                $syncData = [];
                $dialogues = $data['dialogues'] ?? [];
                foreach ($data['characters'] as $characterId) {
                    $dialogue = $dialogues[$characterId] ?? null;
                    $syncData[$characterId] = ['dialogue' => $dialogue];
                }
                $episode->characters()->sync($syncData);
            }

            return $episode;
        });
    }

    /**
     * Update an existing episode.
     */
    public function updateEpisode(Episode $episode, array $data): Episode
    {
        return DB::transaction(function () use ($episode, $data) {
            $episode->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'duration' => $data['duration'] ?? null,
                'order' => $data['order'],
                'episode_number' => $data['episode_number'] ?? null,
            ]);

            // Handle characters sync if provided or if we need to clear them (empty array)
            // We check if 'characters' key exists in data (even if empty/null) OR if explicit empty array was passed
            // However, HTML forms don't send empty arrays for unchecked checkboxes.
            // We assume if 'dialogues' is present, then characters section was likely present.
            // Or better: we rely on the controller to ensure 'characters' is present as empty array if needed.
            // But to be safe and allow "uncheck all":

            // If the key 'characters' exists (even if null), we sync.
            // If it doesn't exist, we might be doing a partial update (e.g. only title), so we do NOT sync.
            // To support unchecking all, the controller should ensure 'characters' is passed as [] if it was missing from request but intended.
            if (array_key_exists('characters', $data)) {
                $syncData = [];
                if (is_array($data['characters']) && ! empty($data['characters'])) {
                    $dialogues = $data['dialogues'] ?? [];
                    foreach ($data['characters'] as $characterId) {
                        $dialogue = $dialogues[$characterId] ?? null;
                        $syncData[$characterId] = ['dialogue' => $dialogue];
                    }
                }
                $episode->characters()->sync($syncData);
            }

            return $episode;
        });
    }

    /**
     * Delete an episode.
     */
    public function deleteEpisode(Episode $episode): void
    {
        $episode->delete();
    }

    /**
     * Get scenes for a specific episode grouped by act.
     */
    public function getEpisodeScenesGroupedByAct(Episode $episode): array
    {
        $scenes = $episode->scenes;

        $acts = [];

        foreach ($scenes as $scene) {
            $actNumber = $scene->act ?? 1;

            // Fallback for legacy data or if not set
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

        ksort($acts);

        return $acts;
    }
}
