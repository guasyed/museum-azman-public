<?php

namespace App\Http\Requests;

use App\Models\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => $this->input('external_reason') ?: $this->input('movement_type'),
        ]);
    }

    public function rules(): array
    {
        return [
            'artwork_id' => ['required', 'exists:artworks,id'],
            'from_location' => ['required', 'string', 'max:255'],
            'from_location_code' => ['nullable', 'string', 'max:255'],
            'to_location' => ['required', 'string', 'max:255'],
            'to_location_code' => ['nullable', 'string', 'max:255'],
            'date_out' => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date'],
            'completed_date' => ['nullable', 'date'],
            'movement_type' => ['required', 'string', 'max:100'],
            'external_reason' => ['nullable', 'string', 'max:255'],
            'external_party' => ['nullable', 'string', 'max:255'],
            'responsible_handler' => ['required', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50', Rule::in(Status::allowedNames())],
            'notes' => ['nullable', 'string'],
            'condition_report' => ['nullable', 'string'],
        ];
    }

}
