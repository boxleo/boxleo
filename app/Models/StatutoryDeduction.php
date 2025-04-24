<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatutoryDeduction extends Model
{
    use HasFactory, SoftDeletes;




    protected $fillable = [
        'payslip_id',
        'income_tax',
        'tax_relief',
        'paye',
        'nssf',
        'nhif',
        'housing_levy',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
