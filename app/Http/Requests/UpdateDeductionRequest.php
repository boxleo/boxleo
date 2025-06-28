<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
    {
        return [
            'deduction_type' => 'required',
            'is_recurring'   => 'boolean',
            'label'          => 'required|string|min:2|max:255',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        return array_merge(parent::validated($key, $default), [
            'is_recurring' => $this->input('is_recurring', false),
        ]);
    }
}
