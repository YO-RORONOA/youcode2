<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'time_limit',
        'passing_score',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
    
    public function getTotalPointsAttribute()
    {
        return $this->questions->sum('points');
    }
}