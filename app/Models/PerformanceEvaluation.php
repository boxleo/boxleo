<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceEvaluation extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'evaluator_id',
        'department_id',
        'evaluation_date',
        'attendance',
        'problems_solved',
        'reports_submitted',
        'knowledge_of_work',
        'team_work',
        'reliability_visibility',
        'productivity',                                                                                                                                                                                                                                                                                                                                             
        'discipline',
        'quality_of_work',
        'communication',
        'total_score',
        'percentage',
        'leadership'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: The evaluator (the doer of the rating)
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Optionally, link directly to the department if needed
    // public function department()
    // {
    //     return $this->belongsTo(Department::class);
    // }


    // write function to return formatted date and time for created_at
    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }


    // write a function to calcaluate total score and percentage for the evaluation
  

    // public function calculateTotalScore()
    // {
    //     $totalScore = $this->attendance + $this->problems_solved + $this->reports_submitted + $this->knowledge_of_work + $this->team_work + $this->reliability_visibility + $this->productivity + $this->discipline + $this->quality_of_work + $this->communication;
    //     $this->total_score = $totalScore;
    //     $this->percentage = ($totalScore / 100) * 100;
    //   
}
