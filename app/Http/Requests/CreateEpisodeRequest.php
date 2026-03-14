<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateEpisodeRequest extends FormRequest
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
            'title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:5000',
            'duration' => 'nullable|integer|min:1|max:1440',
            'order' => 'required|integer|min:1|max:1000',
            'episode_number' => 'nullable|integer|min:1|max:1000',
            'characters' => 'nullable|array',
            'characters.*' => 'exists:characters,id',
            'dialogues' => 'array',
            'dialogues.*' => 'nullable|string|max:10000',
            'project_id' => 'required|exists:projects,id',
            'update_characters' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título do episódio é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 3 caracteres.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
            'duration.min' => 'A duração deve ser de pelo menos 1 minuto.',
            'duration.max' => 'A duração não pode ser maior que 24 horas.',
            'order.required' => 'A ordem do episódio é obrigatória.',
            'order.min' => 'A ordem deve ser pelo menos 1.',
            'order.max' => 'A ordem não pode ser maior que 1000.',
            'episode_number.min' => 'O número do episódio deve ser pelo menos 1.',
            'episode_number.max' => 'O número do episódio não pode ser maior que 1000.',
            'characters.*.exists' => 'Um dos personagens selecionados não existe.',
            'dialogues.*.max' => 'O texto do personagem não pode ter mais de 10000 caracteres.',
            'project_id.required' => 'O projeto é obrigatório.',
            'project_id.exists' => 'O projeto selecionado não existe.',
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
     * Sanitiza strings removendo tags HTML e espaços extras
     */
    private function sanitizeString(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        return trim(strip_tags($value));
    }

    /**
     * Sanitiza array de diálogos
     */
    private function sanitizeDialogues(array $dialogues): array
    {
        return array_map(function ($dialogue) {
            return $this->sanitizeString($dialogue);
        }, $dialogues);
    }
}
