<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreApiDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'camera_id' => ['required', 'integer', 'exists:cameras,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.code' => ['required', 'string', 'exists:items,code'],
            'items.*.status' => ['required', 'string', 'in:detected,undetected'],
            'image' => ['required', 'file', 'image', 'max:10240'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
