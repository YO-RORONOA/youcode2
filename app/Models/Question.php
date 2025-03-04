<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'quiz_id',
        'content',
        'points',
    ];
    
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
    
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
    
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    
    public function correctOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }
}
