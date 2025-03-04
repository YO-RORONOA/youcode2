<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'staff_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];
    
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
    ];
    
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    
    public function isAvailableAt($datetime)
    {
        if (!$this->is_available) {
            return false;
        }
        
        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        
        return $this->date->format('Y-m-d') === $date &&
               $time >= $this->start_time->format('H:i:s') &&
               $time <= $this->end_time->format('H:i:s');
    }
}
