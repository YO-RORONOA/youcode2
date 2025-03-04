<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'session_id',
        'capacity'
    ];

    /**
     * Get the session that owns the group.
     */
    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    /**
     * Get the tests for the group.
     */
    public function tests()
    {
        return $this->hasMany(PresentielTest::class, 'group_id');
    }
    
    /**
     * Check if this group has reached its capacity.
     */
    public function isAtCapacity()
    {
        return $this->tests()->count() >= $this->capacity;
    }
}