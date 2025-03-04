<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_slot',
        'location',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the groups for the session.
     */
    public function groups()
    {
        return $this->hasMany(TestGroup::class, 'session_id');
    }
    
    /**
     * Check if this session has reached the maximum capacity.
     */
    public function isAtCapacity()
    {
        $groupsCapacity = $this->time_slot === 'morning' ? 3 : 3; // 3 groups per session
        return $this->groups()->count() >= $groupsCapacity;
    }
    
    /**
     * Get all tests associated with this session via groups.
     */
    public function tests()
    {
        return PresentielTest::whereHas('group', function ($query) {
            $query->where('session_id', $this->id);
        });
    }
}