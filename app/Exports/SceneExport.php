<?php

namespace App\Exports;

use App\Models\Character;
use App\Models\Scene;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SceneExport implements FromCollection, WithHeadings, WithStyles
{
    protected $projectId;

    protected $acts;

    protected $characters;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->acts = Scene::where('project_id', $projectId)
            ->orderBy('order')
            ->get();
        $this->characters = Character::where('project_id', $projectId)
            ->orderBy('name')
            ->get();
    }

    public function collection()
    {
        $data = new Collection;

        foreach ($this->characters as $character) {
            $row = [$character->name];

            foreach ($this->acts as $act) {
                $dialogue = $character->scenes()
                    ->where('scenes.id', $act->id)
                    ->first()?->pivot?->dialogue ?? '';

                $row[] = $dialogue;
            }

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $headers = ['Personagem'];

        foreach ($this->acts as $act) {
            $headers[] = 'Ato '.$act->order;
        }

        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para o cabeçalho
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Cor indigo-600
            ],
        ]);

        // Ajusta a largura das colunas
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Adiciona bordas em todas as células
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().$sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Estilo zebrado para as linhas
        for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A'.$row.':'.$sheet->getHighestColumn().$row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'], // Cor gray-100
                    ],
                ]);
            }
        }
    }
}
