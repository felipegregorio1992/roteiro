<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScenesSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function collection()
    {
        return $this->project->scenes()
            ->with('characters')
            ->orderBy('order')
            ->get()
            ->map(function ($scene) {
                return [
                    $scene->act,
                    $scene->order,
                    $scene->title,
                    $scene->description,
                    $scene->duration . ' min',
                    $scene->characters->pluck('name')->implode(', ')
                ];
            });
    }

    public function headings(): array
    {
        return ['Ato', 'Ordem', 'Título', 'Descrição', 'Duração', 'Personagens na Cena'];
    }

    public function title(): string
    {
        return 'Lista de Cenas';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
