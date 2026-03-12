<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderScenesRequest extends FormRequest
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
            'act_number' => 'required|integer|min:1',
            'scenes' => 'required|array|min:1',
            'scenes.*.id' => 'required|integer|exists:scenes,id',
            'scenes.*.order' => 'required|integer|min:1',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
