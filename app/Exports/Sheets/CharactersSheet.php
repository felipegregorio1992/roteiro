<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CharactersSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function collection()
    {
        return $this->project->characters()
            ->orderBy('name')
            ->get()
            ->map(function ($character) {
                return [
                    'name' => $character->name,
                    'role' => $character->role,
                    'description' => $character->description,
                    'goals' => $character->goals,
                    'fears' => $character->fears,
                    'history' => $character->history,
                    'personality' => $character->personality,
                    'notes' => $character->notes,
                ];
            });
    }

    public function headings(): array
    {
        return ['Nome', 'Papel', 'Descrição', 'Objetivos', 'Medos', 'História', 'Personalidade', 'Notas'];
    }

    public function title(): string
    {
        return 'Personagens';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
