<?php

namespace App\Imports;

use App\Models\Episode;
use App\Models\Character;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class StoryMatrixImport implements ToCollection
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
        // 1. Identify Header Row (Look for "Personagem" or columns that look like Acts/Episodes)
        $headerRowIndex = null;
        $episodeColumns = []; // Map column index => episode number

        foreach ($rows as $index => $row) {
            // Check if this row looks like a header
            // Criteria: Has "Personagem" OR has "Ato" or numeric headers
            $rowString = implode(' ', $row->filter()->toArray());
            
            if (stripos($rowString, 'Personagem') !== false || stripos($rowString, 'Ato') !== false || stripos($rowString, 'Episódio') !== false || stripos($rowString, '101') !== false) {
                $headerRowIndex = $index;
                
                // Map columns
                foreach ($row as $colIndex => $cell) {
                    $cell = (string)$cell;
                    // Detect Act/Episode number
                    if (preg_match('/(Ato|Episódio|Ep)\s*(\d+)/i', $cell, $matches)) {
                        $episodeColumns[$colIndex] = (int)$matches[2];
                    } elseif (is_numeric($cell) && $cell > 0 && $cell < 1000) {
                        // Assuming plain numbers like "101", "1", "2" are acts/episodes
                        $episodeColumns[$colIndex] = (int)$cell;
                    }
                }
                
                // If we found episode columns, break
                if (!empty($episodeColumns)) {
                    break;
                }
            }
        }

        if ($headerRowIndex === null || empty($episodeColumns)) {
             // Fallback: Assume Row 1 is header, Col A is Char, Col B=Ep 1, Col C=Ep 2...
             $headerRowIndex = 0;
             $colCount = count($rows[0] ?? []);
             for ($i = 1; $i < $colCount; $i++) {
                 $episodeColumns[$i] = $i;
             }
        }

        // 2. Process Data Rows
        DB::transaction(function () use ($rows, $headerRowIndex, $episodeColumns) {
            $project = Project::find($this->projectId);
            if (!$project) return;

            // Ensure we have episodes for these columns
            foreach ($episodeColumns as $epNum) {
                $episode = Episode::firstOrCreate(
                    ['project_id' => $this->projectId, 'episode_number' => $epNum],
                    [
                        'title' => "Episódio $epNum",
                        'description' => 'Importado do Excel',
                        'order' => $epNum,
                        'duration' => 60 // Default duration
                    ]
                );
            }

            // Process Characters
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $charName = trim((string)($row[0] ?? '')); // Assume Col 0 is Name

                if (empty($charName)) continue;

                // Find or Create Character
                $character = Character::firstOrCreate(
                    ['project_id' => $this->projectId, 'name' => $charName],
                    ['user_id' => $project->user_id]
                );

                // Update Episode Contents (Pivot Table)
                foreach ($episodeColumns as $colIndex => $epNum) {
                    $content = trim((string)($row[$colIndex] ?? ''));
                    if (!empty($content)) {
                        // Find the episode
                        $episode = Episode::where('project_id', $this->projectId)
                            ->where('episode_number', $epNum)
                            ->first();

                        if ($episode) {
                            // Sync or Attach character to episode with dialogue
                            // Use syncWithoutDetaching to avoid removing other characters
                            $episode->characters()->syncWithoutDetaching([
                                $character->id => ['dialogue' => $content]
                            ]);
                        }
                    }
                }
            }
        });
    }
}
