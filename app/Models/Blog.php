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
        'sort_order',
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
        'robots_index',
        'robots_follow',
        'schema_type',
        'auto_compress_images',
        'convert_to_webp',
        'enable_amp',
        'auto_link_related_blogs',
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
            'auto_link_related_blogs' => 'boolean',
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

    /**
     * Manually-picked related posts, shown in the sidebar only when
     * auto_link_related_blogs is off — when it's on, the sidebar instead
     * shows same-category posts ordered by updated_at DESC (computed in
     * Public\BlogController, not stored).
     */
    public function relatedBlogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_related_blog', 'blog_id', 'related_blog_id')
            ->withPivot('sort_order')
            ->orderBy('blog_related_blog.sort_order');
    }
}
