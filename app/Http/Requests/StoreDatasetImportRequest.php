<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use \App\Http\Requests\Concerns\RejectsUnknownFields;
use Illuminate\Validation\Validator;

class StoreDatasetImportRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'extensions:csv',
                'max:10240',
            ],
        ];
    }

    use RejectsUnknownFields;

    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->rejectUnknownFields($validator, $this->all()),
        ];
    }
}
