<?php

namespace App\Exports;

use App\Models\Scene;
use App\Models\Character;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ScenesExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function array(): array
    {
        // Buscar personagens
        $characters = Character::where('project_id', $this->projectId)
            ->orderBy('name')
            ->get();

        // Buscar cenas
        $scenes = Scene::where('project_id', $this->projectId)
            ->orderBy('order')
            ->get();

        // Preparar dados
        $data = [];
        
        // Preparar cabeçalho
        $header = ['Personagem'];
        foreach ($scenes as $scene) {
            $header[] = $scene->title;
        }
        $data[] = $header;
        
        // Dados
        foreach ($characters as $character) {
            $row = [$character->name];
            foreach ($scenes as $scene) {
                $characterInScene = $scene->characters
                    ->where('id', $character->id)
                    ->first();
                
                $row[] = $characterInScene ? ($characterInScene->pivot->dialogue ?? '') : '';
            }
            $data[] = $row;
        }

        return $data;
    }

    public function columnWidths(): array
    {
        $scenes = Scene::where('project_id', $this->projectId)->count();
        $widths = ['A' => 30]; // Personagem
        
        // Adiciona larguras para cada coluna de ato
        $columns = range('B', chr(65 + min($scenes, 25))); // Limita a 26 colunas (A-Z)
        foreach ($columns as $column) {
            $widths[$column] = 40;
        }
        
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        return [
            // Estilo do cabeçalho
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM],
                ]
            ],
            // Estilo para todas as células
            'A1:'.$lastColumn.$lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ]
            ],
            // Estilo para linhas alternadas
            'A2:'.$lastColumn.$lastRow => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9FAFB']
                ]
            ],
            // Estilo para a coluna de personagens
            'A2:A'.$lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6']
                ]
            ]
        ];
    }
} 