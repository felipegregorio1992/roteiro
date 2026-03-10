<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OverviewSheet implements FromArray, WithTitle, WithStyles
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function array(): array
    {
        return [
            ['Projeto', $this->project->name],
            ['Descrição', $this->project->description],
            ['Autor', $this->project->user->name],
            ['Data de Criação', $this->project->created_at->format('d/m/Y')],
            [],
            ['Total de Cenas', $this->project->scenes()->count()],
            ['Total de Personagens', $this->project->characters()->count()],
        ];
    }

    public function title(): string
    {
        return 'Visão Geral';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            'A1:A7' => ['font' => ['bold' => true]],
        ];
    }
}
