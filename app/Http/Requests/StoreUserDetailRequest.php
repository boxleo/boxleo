<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authorization for now. You can customize if needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'kin' => 'nullable|string|max:255',
            'kin_contact' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'payment_mode' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'mpesa_no' => 'nullable|string|max:20',
            'nhif_no' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'nssf_no' => 'nullable|string|max:50',
            'kra_pin' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'spouse' => 'nullable|string|max:255',
            'spouse_no' => 'nullable|string|max:20',
            'staffID' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'residential_address' => 'nullable|string|max:255',
            'postal_address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'country_of_origin' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_email' => 'nullable|email|max:255',
        ];
    }
}
