<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'artwork_id' => ['required', 'exists:artworks,id'],
            'from_location' => ['required', 'string', 'max:255'],
            'to_location' => ['required', 'string', 'max:255'],
            'date_out' => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date'],
            'responsible_handler' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'condition_report' => ['nullable', 'string'],
        ];
    }
}
