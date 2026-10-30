<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDatasetSchemaRequest extends FormRequest
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
            'fields' => [
                'required',
                'array',
                'list',
                'min:1',
            ],
            'fields.*' =>[
                'required',
                'array:name,type,rules',
            ],
            'fields.*.name' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],
            'fields.*.type' => [
                'required',
                'string',
                Rule::in(['string', 'integer', 'boolean','decimal','date']),
            ],
            'fields.*.rules' => [
                'nullable',
                'array',
            ]
        ];
    }
}
