<?php

namespace App\Http\Requests\Concerns;

use \Illuminate\Validation\Validator;

trait RequireUpdateFields{

    abstract  public function rules(): array;

    protected function requireUpdateFields(Validator $validator, array $input): void
    {
        $allowedFields = array_keys($this->rules());

        $providedFields = array_intersect($allowedFields, array_keys($input));

        if(empty($providedFields)){
            $validator->errors()->add('field', 'At least one field must be provided.');
        }
    }
}
