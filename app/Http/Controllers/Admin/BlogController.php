<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Region;
use App\Models\Store;
use App\Support\BlogContentProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $blogs = Blog::where('region_id', $region->id)->with('blogCategory')->latest('published_at')->paginate(20);

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.blogs.form', [
            'blog' => new Blog(),
            'blogCategories' => BlogCategory::where('region_id', $region->id)->orderBy('name')->get(),
            'stores' => Store::where('region_id', $region->id)->orderBy('name')->get(),
            'selectedStores' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog = Blog::create($data);
        $this->syncRelatedStores($request, $blog);

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post created.');
    }

    public function edit(Request $request, Blog $blog): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blog->region_id);

        return view('admin.blogs.form', [
            'blog' => $blog,
            'blogCategories' => BlogCategory::where('region_id', $blog->region_id)->orderBy('name')->get(),
            'stores' => Store::where('region_id', $blog->region_id)->orderBy('name')->get(),
            'selectedStores' => $blog->relatedStores->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blog->region_id);

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog->update($data);
        $this->syncRelatedStores($request, $blog);

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post updated.');
    }

    public function destroy(Request $request, Blog $blog): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blog->region_id);

        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post deleted.');
    }

    private function syncRelatedStores(Request $request, Blog $blog): void
    {
        $storeIds = collect($request->input('store_ids', []))
            ->filter(fn ($id) => Store::where('id', $id)->where('region_id', $blog->region_id)->exists())
            ->values();

        $blog->relatedStores()->sync(
            $storeIds->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index + 1]])->all()
        );
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'schema_type' => ['required', 'in:Article,BlogPosting'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'string'],
        ]);

        $processed = BlogContentProcessor::extractToc($data['content']);
        $data['content'] = $processed['content'];
        $data['toc'] = $processed['toc'];
        $data['reading_time_minutes'] = BlogContentProcessor::estimateReadingTimeMinutes($processed['content']);

        $data['faqs'] = collect($data['faqs'] ?? [])
            ->filter(fn ($faq) => filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->values()
            ->all();

        $data['is_published'] = $request->boolean('is_published');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');
        $data['auto_compress_images'] = $request->boolean('auto_compress_images');
        $data['convert_to_webp'] = $request->boolean('convert_to_webp');
        $data['enable_amp'] = $request->boolean('enable_amp');
        $data['related_stores_auto_link'] = $request->boolean('related_stores_auto_link');

        return $data;
    }
}
