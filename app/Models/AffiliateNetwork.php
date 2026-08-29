<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'network_name',
        'tracking_id',
        'api_key',
        'api_secret',
        'is_active',
        'last_synced_at',
        'sync_status',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
