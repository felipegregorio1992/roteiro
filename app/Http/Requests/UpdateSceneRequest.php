<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Scene;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSceneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $scene = $this->route('scene');
        $projectId = $this->input('project_id');

        // If scene is provided in route, check if it belongs to the project
        if ($scene && $scene->project_id != $projectId) {
            return false;
        }

        $project = Project::find($projectId);

        return $project && $project->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'scene_type' => 'nullable|string|max:20',
            'description' => 'required|string|max:5000',
            'duration' => 'required|integer|min:1|max:1440', // Max 24 hours
            'order' => 'required|integer|min:1|max:1000',
            'characters' => 'nullable|array',
            'characters.*' => 'exists:characters,id',
            'dialogues' => 'array',
            'dialogues.*' => 'nullable|string|max:2000',
            'project_id' => 'required|exists:projects,id',
            'act_number' => 'sometimes|integer|min:1|max:30',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título da cena é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 3 caracteres.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.required' => 'A descrição da cena é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
            'duration.required' => 'A duração da cena é obrigatória.',
            'duration.min' => 'A duração deve ser de pelo menos 1 minuto.',
            'duration.max' => 'A duração não pode ser maior que 24 horas.',
            'order.required' => 'A ordem da cena é obrigatória.',
            'order.min' => 'A ordem deve ser pelo menos 1.',
            'order.max' => 'A ordem não pode ser maior que 1000.',
            'characters.*.exists' => 'Um dos personagens selecionados não existe.',
            'dialogues.*.max' => 'O diálogo não pode ter mais de 2000 caracteres.',
            'project_id.required' => 'O projeto é obrigatório.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'act_number.min' => 'O número do ato deve ser pelo menos 1.',
            'act_number.max' => 'O número do ato não pode ser maior que 30.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->sanitizeString($this->input('title')),
            'description' => $this->sanitizeString($this->input('description')),
            'dialogues' => $this->sanitizeDialogues($this->input('dialogues', [])),
        ]);
    }

    /**
     * Sanitize string removing HTML tags and extra spaces
     */
    private function sanitizeString(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        return trim(strip_tags($value));
    }

    /**
     * Sanitize dialogues array
     */
    private function sanitizeDialogues(array $dialogues): array
    {
        return array_map(function ($dialogue) {
            return $this->sanitizeString($dialogue);
        }, $dialogues);
    }
}
