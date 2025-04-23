<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @author Xanders
 * @see https://www.linkedin.com/in/xanders-samoth-b2770737/
 */
class User extends Authenticatable

{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_connection' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        "avatar_url" => 'array',
        "status_name" => 'json'
    ];
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    public function getUserName(): string
    {
        return $this->firstname; // ou $this->email, selon votre logique
    }
    public function getStatus_name($lang = 'fr')
    {
        return $this->status_name[$lang] ?? null;
        // return $this->status_name[$lang] ?? null; // Retourne la description dans la langue demandée
    }

    /**
     * MANY-TO-MANY
     * Several roles for several users
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * MANY-TO-MANY
     * Several media_approbations for several users
     */
    public function media_approbations()
    {
        return $this->belongsToMany(Media::class);
    }

    /**
     * ONE-TO-MANY
     * One country for several users
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several users
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several medias for a user
     */
    public function medias()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * MANY-TO-ONE
     * Several carts for a user
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * MANY-TO-ONE
     * Several donations for a user
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * MANY-TO-ONE
     * Several payments for a user
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * MANY-TO-ONE
     * Several notifications for a user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * MANY-TO-ONE
     * Several sessions for a user
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
