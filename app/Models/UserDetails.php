<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id", "kin", "kin_contact", "gender", "payment_mode", "bank_name", "bank_branch", "bank_account", "mpesa_no", "nhif_no","national_id", "nssf_no", "kra_pin","marital_status","spouse","spouse_no","staffID","nationality","country","region", "date_of_birth", "place_of_birth", "residential_address", "postal_address", "postal_code", "city", "county", "country_of_origin", "emergency_contact_name", "emergency_contact_relationship", "emergency_contact_phone", "emergency_contact_email"
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}

