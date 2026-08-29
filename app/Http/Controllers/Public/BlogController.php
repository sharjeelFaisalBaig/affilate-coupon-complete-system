<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request, Region $region): View
    {
        $query = Blog::where('region_id', $region->id)->where('is_published', true)->with('blogCategory');

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhereHas('blogCategory', fn ($cq) => $cq->where('name', 'like', "%{$search}%")));
        }

        if ($request->filled('category')) {
            $query->whereHas('blogCategory', fn ($q) => $q->where('slug', $request->string('category')));
        }

        $featured = (clone $query)->orderByDesc('published_at')->first();

        $blogs = $query->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = BlogCategory::where('region_id', $region->id)->where('is_active', true)->orderBy('sort_order')->get();
        $settings = PageSetting::forPage($region->id, 'blogs');

        return view('public.blogs', [
            'region' => $region,
            'pageType' => 'blog_listing',
            'featured' => $request->filled('q') || $request->filled('category') ? null : $featured,
            'blogs' => $blogs,
            'categories' => $categories,
            'heading' => $settings?->heading ?: $region->name.' Blog',
            'subheading' => $settings?->subheading,
            'seoTitle' => $settings?->meta_title ?: $region->name.' Blog — Shopping Tips, Deals & Savings Guides',
            'seoDescription' => $settings?->meta_description ?: 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
            'ogTitle' => $settings?->og_title,
            'ogImage' => $settings?->og_image,
            'robotsIndex' => $settings?->robots_index ?? true,
            'robotsFollow' => $settings?->robots_follow ?? true,
        ]);
    }

    public function show(Region $region, string $blogSlug): View
    {
        $blog = Blog::where('region_id', $region->id)->where('slug', $blogSlug)->where('is_published', true)
            ->with(['blogCategory', 'relatedStores'])
            ->firstOrFail();

        $relatedBlogs = Blog::where('region_id', $region->id)->where('is_published', true)
            ->where('id', '!=', $blog->id)
            ->when($blog->blog_category_id, fn ($q) => $q->where('blog_category_id', $blog->blog_category_id))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('public.blog', [
            'region' => $region,
            'pageType' => 'blog_detail',
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
            'seoTitle' => $blog->meta_title ?: $blog->title,
            'seoDescription' => $blog->meta_description ?: $blog->excerpt,
            'canonicalUrl' => $blog->canonical_url,
            'robotsIndex' => $blog->robots_index,
            'robotsFollow' => $blog->robots_follow,
            'ogImage' => $blog->og_image,
        ]);
    }
}
