<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class StoreImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // ajuste según lógica de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxSizeMb = env('IMAGE_MAX_UPLOAD_MB', 100); // 100 MB por defecto
        $maxSizeKB = $maxSizeMb * 1024;
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:' . implode(',', Config::get('image.allowed_mime')),
                'max:' . $maxSizeKB,
            ],
        ];
    }
}
