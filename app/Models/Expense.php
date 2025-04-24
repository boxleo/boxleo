<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{


    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name','expense_date','category','amount','status',
        'office_id','office_id','department_id','payment_mode','comment','receipt'
    ];

    protected function office(){
        return $this->belongsTo(Office::class);
    }

    protected function department(){
        return $this->belongsTo(Department::class);
    }

    public static function calculateTotalAmount()
    {
        return self::sum('amount');
    }

      // Method to calculate expenses for the past one month
      public static function expensesPastMonth()
      {
          $oneMonthAgo = Carbon::now()->subMonth();

          return self::where('expense_date', '>=', $oneMonthAgo)->sum('amount');
      }
}
