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
        'group_id',
        'date',
        'location',
        'test_type',
        'status',
    ];
    
    protected $casts = [
        'date' => 'datetime',
    ];
    
    /**
     * Get the candidate that owns the test.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
    
    /**
     * Get the staff assigned to the test.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    
    /**
     * Get the group this test belongs to (for CME tests).
     */
    public function group()
    {
        return $this->belongsTo(TestGroup::class);
    }
    
    /**
     * Get the comments for this test.
     */
    public function comments()
    {
        return $this->hasMany(TestComment::class, 'presentiel_test_id');
    }
    
    /**
     * Check if this test is a group test (CME).
     */
    public function isGroupTest()
    {
        return $this->test_type === 'cme';
    }
    
    /**
     * Get the test duration in minutes.
     */
    public function getDurationAttribute()
    {
        switch ($this->test_type) {
            case 'technical':
                return 20;
            case 'administrative':
                return 15;
            case 'cme':
                return 60; // Group session, typically longer
            default:
                return 30;
        }
    }
}