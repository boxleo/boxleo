<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Earning extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        // 'payslip_id',
        // 'title',
        // 'user_id',
        'label',
        // 'amount',
        'is_taxable',
        'is_recurring',
        'frequency',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    // given is_taxable is boolean 0 or 1 equivalent to true or false 

    // Accessor to return is_taxable as boolean true/false
    public function getIsTaxableAttribute($value)
    {
        return (bool) $value;
    }
}
