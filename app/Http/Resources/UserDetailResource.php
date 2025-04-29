<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'kin' => $this->kin,
            'kin_contact' => $this->kin_contact,
            'gender' => $this->gender,
            'payment_mode' => $this->payment_mode,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'bank_account' => $this->bank_account,
            'mpesa_no' => $this->mpesa_no,
            'nhif_no' => $this->nhif_no,
            'national_id' => $this->national_id,
            'nssf_no' => $this->nssf_no,
            'kra_pin' => $this->kra_pin,
            'marital_status' => $this->marital_status,
            'spouse' => $this->spouse,
            'spouse_no' => $this->spouse_no,
            'staffID' => $this->staffID,
            'nationality' => $this->nationality,
            'country' => $this->country,
            'region' => $this->region,
            'date_of_birth' => $this->date_of_birth,
            'place_of_birth' => $this->place_of_birth,
            'residential_address' => $this->residential_address,
            'postal_address' => $this->postal_address,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'county' => $this->county,
            'country_of_origin' => $this->country_of_origin,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_email' => $this->emergency_contact_email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // You can also include deleted_at if you want for soft deletes
            'deleted_at' => $this->deleted_at,
        ];
    }
}
