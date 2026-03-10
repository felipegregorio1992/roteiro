<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StoryMatrixSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function array(): array
    {
        $characters = $this->project->characters()->orderBy('name')->get();
        // Get all unique acts from scenes
        $acts = $this->project->scenes()->distinct()->orderBy('act')->pluck('act')->toArray();
        
        // If no acts, default to at least Act 1
        if (empty($acts)) {
            $acts = [1];
        }

        // Header
        $header = ['Personagem', 'Papel', 'Conflito / Objetivo'];
        foreach ($acts as $act) {
            $header[] = "Ato $act";
        }

        $data = [$header];

        foreach ($characters as $character) {
            $conflict = $this->formatConflict($character);
            $row = [
                $character->name,
                $character->role,
                $conflict
            ];

            // Load act contents (manual summaries)
            // Ensure act_contents is an array
            $manualSummaries = is_array($character->act_contents) ? $character->act_contents : [];

            foreach ($acts as $act) {
                // Priority: Manual Summary > Aggregated Scene Descriptions
                if (isset($manualSummaries[$act]) && !empty($manualSummaries[$act])) {
                    $row[] = $manualSummaries[$act];
                } else {
                    // Find scenes for this character in this act
                    // We need to use the relationship to filter by act
                    // But character->scenes() returns BelongsToMany, we need to filter on pivot? 
                    // No, scene table has 'act' column.
                    $scenes = $character->scenes()
                        ->where('act', $act)
                        ->orderBy('order')
                        ->get();
                    
                    if ($scenes->isEmpty()) {
                        $row[] = '';
                    } else {
                        // Aggregate scene info
                        $summary = $scenes->map(function($scene) {
                            $desc = $scene->description ? " - " . $scene->description : "";
                            return "• " . $scene->title . $desc;
                        })->implode("\n");
                        $row[] = $summary;
                    }
                }
            }
            $data[] = $row;
        }

        return $data;
    }

    protected function formatConflict($character)
    {
        $parts = [];
        if ($character->goals) $parts[] = "Objetivo: " . $character->goals;
        if ($character->fears) $parts[] = "Medo: " . $character->fears;
        if ($character->notes) $parts[] = "Notas: " . $character->notes;
        
        return implode("\n", $parts);
    }

    public function title(): string
    {
        return 'Resumo por Ato';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'] // Blue-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ],
            'A' => ['font' => ['bold' => true]], // Character names
            'C' => ['alignment' => ['wrapText' => true]], // Conflict column
        ];
    }
}
