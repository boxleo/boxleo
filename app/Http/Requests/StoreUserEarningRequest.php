<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserEarningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     */
  public function rules(): array
{
    return [
        'user_id' => 'required|exists:users,id',
        'earnings' => 'required|array|min:1',
        'earnings.*.earning_id' => 'required|exists:earnings,id',
        'earnings.*.amount' => 'required|numeric|min:0'
    ];
}



}
