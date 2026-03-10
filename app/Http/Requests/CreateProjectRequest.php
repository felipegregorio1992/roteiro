<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
                Rule::unique('projects')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'description' => 'nullable|string|max:2000|min:10'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do roteiro é obrigatório.',
            'name.unique' => 'Já existe um roteiro com este nome.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'description.min' => 'A descrição deve ter pelo menos 10 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 2000 caracteres.',
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
        ]);
    }

    /**
     * Sanitiza strings removendo tags HTML e espaços extras
     */
    private function sanitizeString(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim(strip_tags($value));
    }
}
