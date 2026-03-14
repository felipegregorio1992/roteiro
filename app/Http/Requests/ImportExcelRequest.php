<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ImportExcelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $projectId = $this->input('project_id');
        $project = Project::find($projectId);

        return $project && Auth::user() && Auth::user()->can('update', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel',
                'max:10240', // 10MB
            ],
            'project_id' => 'required|exists:projects,id',
            'import_type' => 'required|in:script,story_matrix',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Por favor, selecione um arquivo Excel.',
            'file.mimes' => 'O arquivo deve ser do tipo Excel (xlsx, xls).',
            'file.max' => 'O arquivo não pode ser maior que 10MB.',
            'project_id.required' => 'O projeto é obrigatório.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'import_type.required' => 'Selecione o tipo de importação.',
            'import_type.in' => 'Tipo de importação inválido.',
        ];
    }
}
