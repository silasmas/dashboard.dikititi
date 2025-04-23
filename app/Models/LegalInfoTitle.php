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
class LegalInfoTitle extends Model
{
    use HasFactory, Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Translatable properties.
     */
    protected $translatable = ['title'];
    protected $casts = [
        'title' => 'array', // Cast le champ translations en tableau
    ];
    public function getLegalInfoTitle_title($lang = 'fr')
    {
        return $this->title[$lang] ?? null; // Retourne la description dans la langue demandée
    }
    /**
     * ONE-TO-MANY
     * One legal info subject for several legal infos titles
     */
    public function legal_info_subject()
    {
        return $this->belongsTo(LegalInfoSubject::class);
    }

    /**
     * MANY-TO-ONE
     * Several legal infos contents for a legal infos titles
     */
    public function legal_info_contents()
    {
        return $this->hasMany(LegalInfoContent::class);
    }
}
