<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEarningRequest extends FormRequest
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
            'label' => 'required|string|min:2|max:25',
            // 'description' => 'nullable|string|max:1000',
            'type' => 'required|in:fixed,percentage',
            //'amount' => 'required|numeric|min:0',
            'is_taxable' => 'boolean',
             'frequency'=> 'enum:monthly,weekly',
            'is_recurring' => 'boolean',
            //'active' => 'boolean',
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
