<?php

namespace App\Http\Requests;

class UpdateArtworkRequest extends StoreArtworkRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'remove_gallery_image_ids' => ['nullable', 'array'],
            'remove_gallery_image_ids.*' => ['integer', 'exists:artwork_images,id'],
            'primary_gallery_image_id' => ['nullable', 'integer', 'exists:artwork_images,id'],
        ];
    }
}
