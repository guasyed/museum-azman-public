<?php

namespace App\Http\Requests;

use App\Models\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArtworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'artist_name' => ['required', 'string', 'max:255'],
            'artist_country' => ['nullable', 'string', 'max:255'],
            'artist_birth_year' => ['nullable', 'integer', 'between:1000,'.date('Y')],
            'location_name' => ['required', 'string', 'max:255'],
            'location_type' => ['nullable', 'string', 'max:255'],
            'location_address' => ['nullable', 'string', 'max:500'],

            'title' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1000,'.date('Y')],
            'description' => ['nullable', 'string'],
            'medium' => ['nullable', 'string', 'max:255'],
            'size_from_cm' => ['nullable', 'numeric', 'min:0'],
            'size_to_cm' => ['nullable', 'numeric', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_price' => ['nullable', 'numeric', 'min:0'],
            'current_valuation' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50', Rule::in(Status::allowedNames())],
            'provenance' => ['nullable', 'string'],

            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:10240'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:10240'],
        ];
    }
}
