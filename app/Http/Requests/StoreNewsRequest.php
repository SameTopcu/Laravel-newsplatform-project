<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['required', 'string', 'max:2000'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'category' => ['required', 'string', 'max:100'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'thumbnail_caption' => ['nullable', 'string', 'max:255'],
            'source_url' => ['required', 'url', 'max:2048'],
            'published_at' => ['required', 'date'],
        ];
    }
}
