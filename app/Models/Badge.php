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
     * Starting rows seeded per region — an open-ended, admin-editable
     * taxonomy (no cap on how many can exist), not protected defaults
     * (only protected from deletion while assigned to an active promotion).
     */
    public const SEED_NAMES = ['Verified', 'Exclusive', 'Top Deals'];

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
            'Top Deals' => 'bg-amber-50 text-amber-700',
            'Exclusive' => 'bg-purple-50 text-purple-700',
            'Verified' => 'bg-sky-50 text-sky-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
