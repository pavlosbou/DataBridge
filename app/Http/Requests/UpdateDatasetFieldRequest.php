<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\RequireUpdateFields;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use \App\Http\Requests\Concerns\RejectsUnknownFields;

class UpdateDatasetFieldRequest extends FormRequest
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
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('dataset_fields', 'name')
                    ->where('dataset_id', $this->route('datasetId'))
                    ->ignore($this->route('datasetFieldId')),
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['string', 'integer', 'boolean','decimal','date']),
            ],
            'rules' => [
                'sometimes',
                'nullable',
                'array',
            ]
        ];
    }

    use RejectsUnknownFields;
    use RequireUpdateFields;
    public function after(): array
    {
        return [
          function (Validator $validator) {
            $this->rejectUnknownFields($validator, $this->all());
            $this->requireUpdateFields($validator, $this->all());
          }
        ];
    }
}
