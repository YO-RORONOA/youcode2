<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentiel_test_id',
        'staff_id',
        'comment',
        'rating'
    ];

    /**
     * Get the test that owns the comment.
     */
    public function test()
    {
        return $this->belongsTo(PresentielTest::class, 'presentiel_test_id');
    }

    /**
     * Get the staff that owns the comment.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}