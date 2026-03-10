<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CharacterService
{
    /**
     * Busca conteúdo dos atos para um personagem específico
     */
    public function getCharacterActContents(Character $character, int $projectId): array
    {
        $actContents = [];
        $maxActs = 30;

        // Buscar diálogos do personagem por ato usando query otimizada
        $dialogues = DB::table('character_scene as cs')
            ->join('scenes as s', 's.id', '=', 'cs.scene_id')
            ->where('cs.character_id', $character->id)
            ->where('s.project_id', $projectId)
            ->select('s.order as act_number', 'cs.dialogue', 's.description')
            ->orderBy('s.order')
            ->get()
            ->groupBy('act_number');

        // Organizar conteúdo por ato
        for ($act = 1; $act <= $maxActs; $act++) {
            if (isset($dialogues[$act])) {
                $actContent = '';
                foreach ($dialogues[$act] as $dialogue) {
                    if (!empty($dialogue->dialogue)) {
                        if (!empty($actContent)) {
                            $actContent .= "\n\n";
                        }
                        $actContent .= $dialogue->dialogue;
                    } elseif (!empty($dialogue->description)) {
                        if (!empty($actContent)) {
                            $actContent .= "\n\n";
                        }
                        $actContent .= $dialogue->description;
                    }
                }
                $actContents[$act] = $actContent;
            }
        }

        return $actContents;
    }

    /**
     * Create a new character.
     *
     * @param array $data
     * @return Character
     */
    public function createCharacter(array $data): Character
    {
        $character = Character::create([
            'user_id' => Auth::id(),
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'role' => $data['role'] ?? null,
            'goals' => $data['goals'] ?? null,
            'fears' => $data['fears'] ?? null,
            'history' => $data['history'] ?? null,
            'personality' => $data['personality'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);

        CacheService::clearProjectCache($data['project_id'], Auth::id());
        
        return $character;
    }

    /**
     * Update an existing character.
     *
     * @param Character $character
     * @param array $data
     * @return bool
     */
    public function updateCharacter(Character $character, array $data): bool
    {
        $updated = $character->update($data);

        if ($updated) {
             CacheService::clearProjectCache($character->project_id, Auth::id());
        }

        return $updated;
    }

    /**
     * Delete a character.
     *
     * @param Character $character
     * @return bool|null
     */
    public function deleteCharacter(Character $character): ?bool
    {
        $projectId = $character->project_id;
        $character->scenes()->detach();
        $deleted = $character->delete();

        if ($deleted) {
             CacheService::clearProjectCache($projectId, Auth::id());
        }

        return $deleted;
    }
}
