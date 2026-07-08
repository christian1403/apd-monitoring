<?php

namespace App\Http\Requests\Cameras;

use Illuminate\Foundation\Http\FormRequest;

class StoreCameraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:255', 'unique:cameras,ip_address'],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
            'location_id' => ['required', 'exists:locations,id'],
            'rtsp_url' => ['nullable', 'string', 'max:255', 'url'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
