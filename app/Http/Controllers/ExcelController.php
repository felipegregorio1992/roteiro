<?php

namespace App\Http\Controllers;

use App\Imports\ScriptImport;
use App\Imports\StoryMatrixImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ExcelData;
use App\Http\Requests\ImportExcelRequest;
use App\Services\ExcelDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExcelController extends Controller
{
    public function __construct(
        protected ExcelDataService $excelDataService
    ) {}

    public function index()
    {
        $excelData = ExcelData::where('user_id', Auth::id())->get();
        return view('excel.index', compact('excelData'));
    }

    public function import(ImportExcelRequest $request)
    {
        $validated = $request->validated();
        $file = $request->file('file');

        try {
            if ($file && $file->isValid()) {
                $importType = $validated['import_type'] ?? 'script';
                
                if ($importType === 'story_matrix') {
                    Excel::import(new StoryMatrixImport(
                        $validated['project_id'],
                        $file->getClientOriginalName()
                    ), $file);
                } else {
                    Excel::import(new ScriptImport(
                        $validated['project_id'],
                        $file->getClientOriginalName()
                    ), $file);
                }

                return redirect()->route('excel.index')
                    ->with('success', 'Arquivo importado com sucesso!');
            }

            return redirect()->back()
                ->with('error', 'O arquivo enviado é inválido.');

        } catch (\Exception $e) {
            Log::error('Erro na importação:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao importar arquivo: ' . $e->getMessage());
        }
    }

    public function show(ExcelData $excelData)
    {
        // Autorização (assumindo que o usuário só pode ver seus próprios dados ou do projeto que tem acesso)
        if ($excelData->user_id !== Auth::id()) {
             // Poderia verificar também o projeto via Policy, mas por enquanto:
             abort(403);
        }

        $result = $this->excelDataService->getTimelineMatrix($excelData);

        return view('excel.show', [
            'excelData' => $excelData,
            'timelineMatrix' => $result['matrix'],
            'maxActs' => $result['totalActs']
        ]);
    }

    public function destroy(ExcelData $excelData)
    {
        if ($excelData->user_id !== Auth::id()) {
            abort(403);
        }

        $excelData->delete();
        return redirect()->route('excel.index')
            ->with('success', 'Arquivo excluído com sucesso!');
    }
}
