<?php

namespace App\Http\Requests\Cameras;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCameraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cameraId = $this->route('camera') instanceof \App\Models\Camera
            ? $this->route('camera')->id
            : $this->route('camera');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'ip_address'  => ['required', 'string', 'max:255', 'unique:cameras,ip_address,' . $cameraId],
            'status'      => ['required', 'string', 'in:active,inactive,maintenance'],
            'location_id' => ['required', 'exists:locations,id'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
