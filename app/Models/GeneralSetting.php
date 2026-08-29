<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'footer_text',
        'footer_disclaimer',
        'rights_text',
        'logo_path',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public static function forRegion(int $regionId): self
    {
        return self::firstOrNew(['region_id' => $regionId]);
    }
}
