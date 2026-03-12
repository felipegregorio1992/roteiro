<?php

namespace App\Imports;

use App\Models\Character;
use App\Models\Episode;
use App\Models\ExcelData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ScriptImport implements ToCollection
{
    protected $projectId;

    protected $fileName;

    public function __construct($projectId, $fileName)
    {
        $this->projectId = $projectId;
        $this->fileName = $fileName;
    }

    public function collection(Collection $rows)
    {
        // Remove linhas totalmente vazias
        $rows = $rows->filter(function ($row) {
            return $row->filter()->isNotEmpty();
        });

        if ($rows->isEmpty()) {
            throw new \Exception('O arquivo Excel está vazio ou não contém dados válidos.');
        }

        // Determina o número total de episódios baseado no número máximo de colunas
        $maxColumns = $rows->max(fn ($row) => count($row));
        $totalEpisodes = $maxColumns > 1 ? $maxColumns - 1 : 1;

        DB::transaction(function () use ($rows, $totalEpisodes) {
            $this->clearProjectData();

            // Cria os episódios
            $episodeIds = $this->createEpisodes($totalEpisodes);

            // Processa personagens e diálogos
            $this->processCharactersAndDialogues($rows, $episodeIds, $totalEpisodes);

            // Salva registro do Excel importado
            $this->saveExcelData($rows);
        });
    }

    protected function clearProjectData()
    {
        // Remove relações antigas
        DB::table('character_episode')
            ->join('episodes', 'episodes.id', '=', 'character_episode.episode_id')
            ->where('episodes.project_id', $this->projectId)
            ->delete();

        Episode::where('project_id', $this->projectId)->delete();
        Character::where('project_id', $this->projectId)->delete();
    }

    protected function createEpisodes($totalEpisodes)
    {
        $episodeIds = [];
        for ($i = 1; $i <= $totalEpisodes; $i++) {
            $episode = Episode::create([
                'title' => "Episódio {$i}",
                'project_id' => $this->projectId,
                'user_id' => Auth::id(),
                'description' => '',
                'duration' => 60,
                'order' => $i,
                'episode_number' => $i,
            ]);
            $episodeIds[$i] = $episode->id;
        }

        return $episodeIds;
    }

    protected function processCharactersAndDialogues(Collection $rows, array $episodeIds, int $totalEpisodes)
    {
        $dialoguesToInsert = [];

        foreach ($rows as $row) {
            $characterName = trim((string) ($row[0] ?? ''));

            if (empty($characterName)) {
                continue;
            }

            $character = Character::create([
                'name' => $characterName,
                'project_id' => $this->projectId,
                'user_id' => Auth::id(),
            ]);

            // Para cada coluna (episódio)
            for ($i = 1; $i <= $totalEpisodes; $i++) {
                $dialogue = trim((string) ($row[$i] ?? ''));

                if (! empty($dialogue) && isset($episodeIds[$i])) {
                    $dialoguesToInsert[] = [
                        'character_id' => $character->id,
                        'episode_id' => $episodeIds[$i],
                        'dialogue' => $dialogue,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($dialoguesToInsert)) {
            DB::table('character_episode')->insert($dialoguesToInsert);
        }
    }

    protected function saveExcelData(Collection $rows)
    {
        // Converte Collection para array para salvar no JSON
        ExcelData::create([
            'user_id' => Auth::id(),
            'project_id' => $this->projectId,
            'file_name' => $this->fileName,
            'headers' => json_encode([]),
            'data' => json_encode($rows->toArray()),
        ]);
    }
}
