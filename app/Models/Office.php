<?php

namespace App\Models;

use App\Http\Controllers\UnitController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes;

    protected $fillable =[
        'name','unit_id','phone','latitude', 'longitude', 'address'

    ];

    public function users(){
        return $this->hasMany(User::class);

    }

    public function unit(){
        return $this->belongsTo(Unit::class);
    }


    public function assets(){
        return $this->hasMany(Asset::class);

    }
}

