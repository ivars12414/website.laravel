<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(
            response()->json([
                'error' => true,
                'class' => 'alert--red',
                'error_fields' => $this->mapErrorFields($errors),
                'empty_groups' => [
                    'default' => [
                        'msg' => $this->firstErrorMessage($errors),
                    ],
                ],
            ], 422)
        );
    }

    protected function mapErrorFields(array $errors): array
    {
        $out = [];
        foreach ($errors as $field => $messages) {
            $out[$field] = [
                'class' => 'error',
                'msg' => $messages[0] ?? '',
            ];
        }
        return $out;
    }

    protected function firstErrorMessage(array $errors): string
    {
        foreach ($errors as $messages) {
            if (!empty($messages[0])) {
                return $messages[0];
            }
        }
        return 'Validation error';
    }
}
