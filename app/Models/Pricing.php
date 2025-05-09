<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @author Xanders
 * @see https://www.linkedin.com/in/xanders-samoth-b2770737/
 */
class Pricing extends Model
{
    use HasFactory,Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Translatable properties.
     */
    protected $translatable = ['deadline'];
    protected $casts = ["deadline" => 'array'];
    /**
     * MANY-TO-ONE
     * Several orders for a pricing
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * MANY-TO-ONE
     * Several donations for a pricing
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
}
