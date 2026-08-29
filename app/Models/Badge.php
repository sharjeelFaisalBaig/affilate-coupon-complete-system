<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    /**
     * SRS §8/§10 caps the system at exactly 2 badges (its own badge filter
     * dropdown lists only Verified/Exclusive/All) — kept as plain
     * admin-editable rows, not protected defaults (SRS only protects them
     * from deletion while in use, not from renaming/deactivation).
     */
    public const SEED_NAMES = ['Verified', 'Exclusive'];

    protected $fillable = [
        'region_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_badge');
    }

    public static function seedDefaultsFor(Region $region): void
    {
        foreach (self::SEED_NAMES as $name) {
            self::firstOrCreate(['region_id' => $region->id, 'name' => $name]);
        }
    }

    /**
     * Purely presentational — the 4 seeded names get their historical pill
     * colors, anything custom an admin adds falls back to a neutral style.
     */
    public function classes(): string
    {
        return match ($this->name) {
            'Top Code' => 'bg-amber-50 text-amber-700',
            "Editor's Pick" => 'bg-emerald-50 text-emerald-700',
            'Exclusive' => 'bg-purple-50 text-purple-700',
            'Verified' => 'bg-sky-50 text-sky-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
