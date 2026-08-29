<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_name',
        'author_avatar',
        'published_at',
        'reading_time_minutes',
        'toc',
        'faqs',
        'is_published',
        'meta_title',
        'meta_description',
        'og_title',
        'og_image',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'schema_type',
        'auto_compress_images',
        'convert_to_webp',
        'enable_amp',
        'related_stores_auto_link',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'toc' => 'array',
            'faqs' => 'array',
            'is_published' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'auto_compress_images' => 'boolean',
            'convert_to_webp' => 'boolean',
            'enable_amp' => 'boolean',
            'related_stores_auto_link' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function relatedStores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'blog_store')
            ->withPivot('sort_order')
            ->orderBy('blog_store.sort_order');
    }
}
