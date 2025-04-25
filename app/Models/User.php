<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Optional if you want email verification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Import Spatie trait
use Filament\Models\Contracts\FilamentUser; // Import Filament contract
use Filament\Panel; // Import Panel
use Laravel\Passport\HasApiTokens; // Import Passport trait

class User extends Authenticatable implements FilamentUser // Implement FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // Use traits

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number', // Add field
        'nationality',   // Add field
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

    /**
     * Define ability to access Filament Panel.
     * Both Admin and User can login to the panel, but permissions control what they see/do.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access if user has either 'Admin' or 'User' role
        // Adjust this logic if you have more panels or different access rules
        return $this->hasAnyRole(['Admin', 'User']);
    }
}