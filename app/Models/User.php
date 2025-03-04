<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;



class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the staff profile associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Get the candidate profile associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function candidate()
    {
        return $this->hasOne(Candidate::class);
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->roles()->where('name', $role)->exists()) {
                    return true;
                }
            }
            return false;
        }
        
        return $this->roles()->where('name', $roles)->exists();
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        // Mapping des rôles aux permissions
        $rolePermissions = [
            'Admin' => ['*'], // L'administrateur a toutes les permissions
            'CME' => ['view_candidates', 'evaluate_tests', 'manage_availability'],
            'Coach' => ['view_candidates', 'evaluate_tests', 'manage_availability'],
            'candidate' => ['submit_documents', 'take_quiz', 'view_tests'],
            'administrative' => ['view_candidates', 'view_documents', 'schedule_tests'],
        ];
        
        foreach ($this->roles as $role) {
            if (isset($rolePermissions[$role->name])) {
                if (in_array('*', $rolePermissions[$role->name]) || 
                    in_array($permission, $rolePermissions[$role->name])) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get the notifications for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all unread notifications for the user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false)->get();
    }

    /**
     * Assign a role to the user.
     *
     * @param string|Role $role
     * @return void
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        if (!$this->hasRole($role->name)) {
            $this->roles()->attach($role);
        }
    }

    /**
     * Remove a role from the user.
     *
     * @param string|Role $role
     * @return void
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        $this->roles()->detach($role);
    }

    /**
     * Get user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Set default role for new users.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($user) {
            // Assigner automatiquement le rôle "candidate" aux nouveaux utilisateurs
            $candidateRole = Role::where('name', 'candidate')->first();
            
            if ($candidateRole) {
                $user->roles()->attach($candidateRole);
            }
        });
    }
}

