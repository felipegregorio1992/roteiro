<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use App\Models\Character;
use App\Models\Scene;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DialogueMatrixSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function array(): array
    {
        $characters = $this->project->characters()->orderBy('name')->get();
        $scenes = $this->project->scenes()->with('characters')->orderBy('order')->get();

        $header = ['Personagem'];
        foreach ($scenes as $scene) {
            $header[] = "Ato {$scene->act} - {$scene->title}";
        }

        $data = [$header];

        foreach ($characters as $character) {
            $row = [$character->name];
            foreach ($scenes as $scene) {
                // Find character in the eager loaded relation
                $charInScene = $scene->characters->firstWhere('id', $character->id);
                $row[] = $charInScene ? ($charInScene->pivot->dialogue ?? '') : '';
            }
            $data[] = $row;
        }

        return $data;
    }

    public function title(): string
    {
        return 'Matriz de Diálogos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
