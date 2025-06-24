<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEarningRequest extends FormRequest
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
            'label' => 'sometimes|string|min:2|max:25',
            // 'description' => 'nullable|string|max:1000',
            'type' => 'sometimes|in:fixed,percentage',
            // 'amount' => 'sometimes|numeric|min:0',
            'is_taxable' => 'sometimes|boolean',
            'frequency' => 'sometimes|in:monthly,weekly',
            'is_recurring' => 'sometimes|boolean',
            // 'active' => 'sometimes|boolean',
        ];
    }

    public function validated($key = null, $default = null)
    {
        return array_merge(parent::validated($key, $default), [
            'taxable' => $this->input('taxable', true),
            'pensionable' => $this->input('pensionable', true),
            'active' => $this->input('active', true),
        ]);
    }

   
}
