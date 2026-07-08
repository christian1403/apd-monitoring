<?php

namespace App\Http\Requests\Cameras;

use App\Models\Camera;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCameraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cameraId = $this->route('camera') instanceof Camera
            ? $this->route('camera')->id
            : $this->route('camera');

        return [
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:255', 'unique:cameras,ip_address,'.$cameraId],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
            'location_id' => ['required', 'exists:locations,id'],
            'rtsp_url' => ['nullable', 'string', 'max:255', 'url'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
