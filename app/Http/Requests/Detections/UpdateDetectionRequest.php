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
            'item_id'     => ['required', 'exists:items,id'],
            'camera_id'   => ['required', 'exists:cameras,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'status'      => ['required', 'string', 'in:safe,warning,unsafe'],
            'detected_at' => ['nullable', 'date'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
