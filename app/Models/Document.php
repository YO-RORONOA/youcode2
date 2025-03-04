<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'candidate_id',
        'type',
        'file_path',
        'is_verified',
        'verification_notes',
    ];
    
    protected $casts = [
        'is_verified' => 'boolean',
    ];
    
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
