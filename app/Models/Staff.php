<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'photo_profile',
        'speciality',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
    
    public function presentielTests()
    {
        return $this->hasMany(PresentielTest::class);
    }
    
    public function checkAvailability($date)
    {
        $dateObj = is_string($date) ? new \DateTime($date) : $date;
        
        return $this->availabilities()
            ->whereDate('date', $dateObj->format('Y-m-d'))
            ->where('is_available', true)
            ->exists();
    }
}