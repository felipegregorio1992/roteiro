<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Character;
use App\Models\Scene;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Cache TTL em segundos
     */
    private const CACHE_TTL = 3600; // 1 hora
    
    /**
     * Busca estatísticas do projeto com cache
     */
    public static function getProjectStats(int $projectId): array
    {
        return Cache::remember("project_stats_{$projectId}", self::CACHE_TTL, function () use ($projectId) {
            return [
                'characters_count' => Character::where('project_id', $projectId)->count(),
                'scenes_count' => Scene::where('project_id', $projectId)->count(),
                'total_duration' => Scene::where('project_id', $projectId)->sum('duration'),
                'avg_scene_duration' => Scene::where('project_id', $projectId)->avg('duration'),
                'last_updated' => now()
            ];
        });
    }
    
    /**
     * Busca personagens do projeto com cache
     */
    public static function getProjectCharacters(int $projectId, int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "project_characters_{$projectId}_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId, $userId) {
            return Character::where('project_id', $projectId)
                ->where('user_id', $userId)
                ->with(['scenes' => function($query) {
                    $query->orderBy('order', 'asc');
                }])
                ->orderBy('name')
                ->get();
        });
    }
    
    /**
     * Busca cenas do projeto com cache
     */
    public static function getProjectScenes(int $projectId, int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "project_scenes_{$projectId}_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId, $userId) {
            return Scene::where('project_id', $projectId)
                ->where('user_id', $userId)
                ->with(['characters' => function($query) {
                    $query->select('characters.*', 'character_scene.dialogue')
                          ->orderBy('name', 'asc');
                }])
                ->orderBy('order')
                ->get();
        });
    }
    
    /**
     * Busca projetos do usuário com cache
     */
    public static function getUserProjects(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("user_projects_{$userId}", self::CACHE_TTL, function () use ($userId) {
            return Project::where('user_id', $userId)
                ->withCount(['characters', 'scenes'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Busca matriz de personagens por ato com cache
     */
    public static function getCharacterActMatrix(int $projectId, int $userId): array
    {
        $cacheKey = "character_act_matrix_{$projectId}_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId, $userId) {
            $characters = Character::where('project_id', $projectId)
                ->where('user_id', $userId)
                ->orderBy('name')
                ->get();
                
            $matrix = [];
            $maxActs = 30;
            
            foreach ($characters as $character) {
                $characterActs = array_fill(1, $maxActs, '');
                
                // Buscar diálogos do personagem por ato
                $dialogues = DB::table('character_scene as cs')
                    ->join('scenes as s', 's.id', '=', 'cs.scene_id')
                    ->where('cs.character_id', $character->id)
                    ->where('s.project_id', $projectId)
                    ->select('s.act as act_number', 'cs.dialogue')
                    ->get();
                
                foreach ($dialogues as $dialogue) {
                    if ($dialogue->act_number >= 1 && $dialogue->act_number <= $maxActs) {
                        $characterActs[$dialogue->act_number] = $dialogue->dialogue;
                    }
                }
                
                $matrix[$character->id] = [
                    'name' => $character->name,
                    'acts' => $characterActs
                ];
            }
            
            return $matrix;
        });
    }
    
    /**
     * Limpa cache relacionado a um projeto
     */
    public static function clearProjectCache(int $projectId, int $userId): void
    {
        $keys = [
            "project_stats_{$projectId}",
            "project_characters_{$projectId}_{$userId}",
            "project_scenes_{$projectId}_{$userId}",
            "character_act_matrix_{$projectId}_{$userId}",
            "user_projects_{$userId}"
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Limpa cache específico de cenas de um projeto
     */
    public static function clearProjectScenesCache(int $projectId, int $userId): void
    {
        $key = "project_scenes_{$projectId}_{$userId}";
        Cache::forget($key);
    }
    
    /**
     * Limpa todo o cache do usuário
     */
    public static function clearUserCache(int $userId): void
    {
        $pattern = "*_{$userId}";
        Cache::flush(); // Em produção, usar implementação mais específica
    }
    
    /**
     * Warm up cache para um projeto
     */
    public static function warmUpProjectCache(int $projectId, int $userId): void
    {
        self::getProjectStats($projectId);
        self::getProjectCharacters($projectId, $userId);
        self::getProjectScenes($projectId, $userId);
        self::getCharacterActMatrix($projectId, $userId);
    }
}
