<?php

namespace App\Http\Requests\Detections;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.status' => ['required', 'string', 'in:detected,undetected'],
            'camera_id' => ['required', 'exists:cameras,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'status' => ['sometimes', 'string', 'in:safe,unsafe'],
            'detected_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
