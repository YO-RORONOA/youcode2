<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'question_option_id',
        'answer_text',
        'is_correct',
    ];
    
    protected $casts = [
        'is_correct' => 'boolean',
    ];
    
    public function quizAttempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }
    
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    
    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}