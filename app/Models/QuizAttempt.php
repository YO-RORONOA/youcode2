<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'candidate_id',
        'quiz_id',
        'start_time',
        'end_time',
        'score',
        'status',
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
    
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
    
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    
    public function getRemainingTimeAttribute()
    {
        if ($this->end_time) {
            return 0;
        }
        
        $endTime = $this->start_time->addMinutes($this->quiz->time_limit);
        $now = now();
        
        return $now->gt($endTime) ? 0 : $now->diffInSeconds($endTime);
    }
    
    public function getScorePercentAttribute()
    {
        if (!$this->score || !$this->quiz->total_points) {
            return 0;
        }
        
        return round(($this->score / $this->quiz->total_points) * 100, 2);
    }
}
