<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'birth_date',
        'phone',
        'address',
        'status',
    ];
    
    protected $casts = [
        'birth_date' => 'date',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
    
    public function presentielTests()
    {
        return $this->hasMany(PresentielTest::class);
    }
    
    public function hasSubmittedAllDocuments()
    {
        // Vérifier si tous les documents requis sont soumis
        $requiredTypes = ['id_card']; // Ajoutez d'autres types si nécessaire
        
        foreach ($requiredTypes as $type) {
            if (!$this->documents()->where('type', $type)->exists()) {
                return false;
            }
        }
        
        return true;
    }
    
    public function hasPassedQuiz()
    {
        return $this->quizAttempts()
            ->where('status', 'passed')
            ->exists();
    }
}
