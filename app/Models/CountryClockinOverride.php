<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryClockinOverride extends Model
{
    protected $fillable = [
        'country_clockin_setting_id', 'override_date', 'clockin_time',
        'clockout_time', 'reason', 'type', 'is_active', 'created_by',
    ];

    protected $casts = [
        'override_date' => 'date',
        'is_active'     => 'boolean',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(CountryClockinSetting::class, 'country_clockin_setting_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
