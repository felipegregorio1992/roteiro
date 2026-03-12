<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCharacterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $projectId = $this->input('project_id');
        $project = Project::find($projectId);

        // Also check if the character belongs to the project
        $character = $this->route('character');

        return $project && $project->user_id === Auth::id() && $character->project_id == $projectId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->input('project_id');
        $characterId = $this->route('character')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('characters')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                })->ignore($characterId),
            ],
            'description' => 'required|string|max:2000|min:10',
            'role' => 'required|string|max:255|in:Protagonista,Antagonista,Mentor,Aliado,Personagem',
            'goals' => 'nullable|string|max:1000',
            'fears' => 'nullable|string|max:1000',
            'history' => 'nullable|string|max:2000',
            'personality' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'project_id' => 'required|exists:projects,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do personagem é obrigatório.',
            'name.unique' => 'Já existe um personagem com este nome neste projeto.',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'description.required' => 'A descrição do personagem é obrigatória.',
            'description.min' => 'A descrição deve ter pelo menos 10 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'role.required' => 'O papel do personagem é obrigatório.',
            'role.in' => 'O papel deve ser: Protagonista, Antagonista, Mentor, Aliado ou Personagem.',
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
            'name' => $this->sanitizeString($this->input('name')),
            'description' => $this->sanitizeString($this->input('description')),
            'role' => $this->sanitizeString($this->input('role')),
            'goals' => $this->sanitizeString($this->input('goals')),
            'fears' => $this->sanitizeString($this->input('fears')),
            'history' => $this->sanitizeString($this->input('history')),
            'personality' => $this->sanitizeString($this->input('personality')),
            'notes' => $this->sanitizeString($this->input('notes')),
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
}
