<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'application_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin'
            || strtolower($this->name ?? '') === 'admin'
            || strtolower($this->email ?? '') === 'admin@gmail.com'
            || $this->id === 10;
    }

    public function isAccount(): bool
    {
        return in_array(strtolower(trim($this->role ?? '')), ['account', 'accounts'], true);
    }

    public function isTelecaller(): bool
    {
        return strtolower($this->role ?? '') === 'telecaller' || empty($this->role);
    }
}
