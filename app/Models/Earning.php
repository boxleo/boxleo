<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;


    protected $fillable = [
        'payslip_id',
        'title',
        'amount',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
