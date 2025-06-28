<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeductionRequest extends FormRequest
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
            'label' => 'required|string|min:2|max:255|unique:deductions,label',
            'deduction_type' => 'nullable|string|max:1000',
             'mandatory' => 'sometimes|boolean',
         
        ];
    }

    public function validated($key = null, $default = null): array
    {
        return array_merge(parent::validated($key, $default), [
            'is_recurring ' => $this->input('mandatory', false),
            'deduction_type' => $this->input('deduction_type', false),
            'lable' => $this->input('lable', true),
        ]);
    }

}
