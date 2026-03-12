<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ExcelData;
use Illuminate\Support\Facades\DB;

class ExcelDataService
{
    /**
     * Processa e compara os dados do Excel com o banco de dados
     * para gerar a matriz de visualização.
     */
    public function getTimelineMatrix(ExcelData $excelData): array
    {
        // Define o número total de atos
        $totalActs = 30;

        // Carrega os dados do Excel
        $data = is_string($excelData->data) ? json_decode($excelData->data, true) : $excelData->data;

        // Carrega os personagens do banco de dados
        $characters = Character::where('project_id', $excelData->project_id)
            ->orderBy('name')
            ->get();

        // Carrega os diálogos do banco de dados com todos os campos necessários
        $characterScenes = DB::table('character_scene AS cs')
            ->select('cs.*', 's.title', 's.order', 'c.name as character_name')
            ->join('scenes AS s', 's.id', '=', 'cs.scene_id')
            ->join('characters AS c', 'c.id', '=', 'cs.character_id')
            ->where('s.project_id', $excelData->project_id)
            ->orderBy('s.order')
            ->get();

        // Matriz final para exibição
        $finalMatrix = [];

        // Primeiro, processa os dados do banco de dados
        foreach ($characters as $character) {
            $characterActs = array_fill(1, $totalActs, '');

            // Adiciona os diálogos do banco de dados
            foreach ($characterScenes as $cs) {
                if ($cs->character_name === $character->name) {
                    $actNumber = $cs->order;
                    if ($actNumber >= 1 && $actNumber <= $totalActs) {
                        $characterActs[$actNumber] = $cs->dialogue;
                    }
                }
            }

            $finalMatrix[$character->id] = [
                'name' => $character->name,
                'acts' => $characterActs,
            ];
        }

        // Depois, verifica se há personagens no Excel que não estão no banco
        if (is_array($data)) {
            foreach ($data as $row) {
                $characterName = trim((string) ($row[0] ?? ''));
                if (empty($characterName)) {
                    continue;
                }

                // Verifica se o personagem já está na matriz final
                $exists = false;
                foreach ($finalMatrix as $charData) {
                    if ($charData['name'] === $characterName) {
                        $exists = true;
                        break;
                    }
                }

                // Se não existe, adiciona com os dados do Excel
                if (! $exists) {
                    $acts = array_fill(1, $totalActs, '');
                    for ($i = 1; $i <= $totalActs; $i++) {
                        if (isset($row[$i])) {
                            $acts[$i] = trim((string) $row[$i]);
                        }
                    }

                    $finalMatrix['temp_'.md5($characterName)] = [
                        'name' => $characterName,
                        'acts' => $acts,
                    ];
                }
            }
        }

        // Ordena a matriz final por nome do personagem
        uasort($finalMatrix, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'matrix' => $finalMatrix,
            'totalActs' => $totalActs,
        ];
    }
}
