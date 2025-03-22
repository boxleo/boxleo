<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;

class Requisition extends Model
{
    use HasFactory;


    use SoftDeletes;

    protected $fillable = [
        'requesting_officer_id',
        'department_id',
        'status',
        'special_instructions',
        'budgeted_expenses',
        'funds_available',
        'is_line_manager',
        'is_coo',
        'is_hr',
        'is_finance_manager',
        'is_cfo',
        'user_id',
        'office_id',
        'unit_id',
        'total_value_of_items',
        'comment',
        'approver_type',
        'paid',
        'pop',
        'attachments'


    ];

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function user(){

        return $this->belongsTo(User::class);
    }


    public function getCreatedAtAttribute($value)

    {

        return Carbon::parse($value)->format('Y-m-d H:i:s'); // Change format as needed

    }


    public function requisitionLogs()
    {
        return $this->hasMany(RequisitionLog::class, 'requisition_id');
    }


   
}
