<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do roteiro é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
        ];
    }
}
