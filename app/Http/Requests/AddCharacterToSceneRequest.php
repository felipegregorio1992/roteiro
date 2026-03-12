<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCharacterToSceneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller via Policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'character_id' => 'required|exists:characters,id',
            'dialogue' => 'nullable|string',
        ];
    }
}
