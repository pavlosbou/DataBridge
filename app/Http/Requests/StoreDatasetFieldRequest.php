<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDatasetFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dataset_fields', 'name')
                ->where('dataset_id', $this->route('id')),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['string', 'integer', 'boolean','decimal','date']),
            ],
            'rules' => 'nullable|array',
        ];
    }
}
