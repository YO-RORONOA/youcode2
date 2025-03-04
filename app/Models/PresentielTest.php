<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresentielTest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'candidate_id',
        'staff_id',
        'date',
        'location',
        'status',
        'notes',
    ];
    
    protected $casts = [
        'date' => 'datetime',
    ];
    
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
    
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
