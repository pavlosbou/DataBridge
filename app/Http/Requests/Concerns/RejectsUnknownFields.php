<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;


trait RejectsUnknownFields{

    abstract public function rules(): array;
    protected function rejectUnknownFields(Validator $validator, array $input): void
    {
        $allowedFields = array_keys($this->rules());

        $unknownFields = array_diff(
            array_keys($input),
            $allowedFields
        );

        foreach ($unknownFields as $field) {
            $validator->errors()->add(
                $field,
                "The {$field} field is not allowed.");
        }
    }
}
