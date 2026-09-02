@extends('admin.layouts.app')

@section('title', $store->exists ? 'Edit Store' : 'Add Store')

@push('head')
    @vite(['resources/js/blog-editor.js'])
@endpush

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $store->exists ? route('admin.stores.update', $store) : route('admin.stores.store') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if ($store->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Store/Brand Name</label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}" required maxlength="255"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optimal length: ~40 characters. Shown exactly as typed — no prefix/suffix added.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Store Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $store->slug) }}" placeholder="auto-generated from name if left blank"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URI slug, e.g. "amazon".</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">— None —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $store->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">About</label>
                <div data-quill-editor="about" style="min-height: 180px;" class="bg-white"></div>
                <textarea name="about" data-content-field="about" class="hidden">{{ old('about', $store->about) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Longer, structured "About {{ $store->name ?: 'Store' }}" content improves SEO — use headings to break it up.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Store Logo</label>
                @if ($store->logo_path)
                    <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="64" height="64" class="mb-2 h-16 w-16 rounded border border-gray-200 object-contain">
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Required dimensions: exactly 200x200px.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Website URL</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $store->website_url) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Affiliate URL</label>
                    <input type="url" name="affiliate_url" value="{{ old('affiliate_url', $store->affiliate_url) }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Used for this store's coupon/deal redirect links.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Expiry Date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($store->expiry_date)->format('Y-m-d')) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optional. After this date, this store and its offers stop showing anywhere on the frontend.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Star Rating (0–5)</label>
                    <input type="number" step="0.1" min="0" max="5" name="star_rating" value="{{ old('star_rating', $store->star_rating ?? 4.5) }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reviews Count</label>
                    <input type="number" min="0" name="reviews_count" value="{{ old('reviews_count', $store->reviews_count ?? 0) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $store->id ? $store->is_active : true))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Published</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $store->is_featured))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Featured Store</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $store->is_popular))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Popular Store</span>
                </label>
            </div>

            <fieldset class="rounded-md border border-gray-200 p-4">
                <legend class="px-1 text-sm font-medium text-gray-700">SEO</legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $store->meta_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $store->meta_keywords) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $store->meta_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Canonical URL</label>
                        <input type="url" name="canonical_url" value="{{ old('canonical_url', $store->canonical_url) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Title</label>
                        <input type="text" name="og_title" value="{{ old('og_title', $store->og_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Description</label>
                        <input type="text" name="og_description" value="{{ old('og_description', $store->og_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $store->id ? $store->robots_index : true))
                               class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Robots: Index</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $store->id ? $store->robots_follow : true))
                               class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Robots: Follow</span>
                    </label>
                </div>
            </fieldset>

            <fieldset class="rounded-md border border-gray-200 p-4 space-y-3">
                <legend class="px-1 text-sm font-medium text-gray-700">Store Script Injection</legend>
                @foreach ([
                    'head_start_script' => 'Start of <head>',
                    'head_end_script' => 'End of <head>',
                    'body_start_script' => 'Start of <body>',
                    'body_end_script' => 'End of <body>',
                ] as $field => $label)
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2"
                                  class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $store->$field) }}</textarea>
                    </div>
                @endforeach
            </fieldset>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                    {{ $store->exists ? 'Save Changes' : 'Create Store' }}
                </button>
                <a href="{{ route('admin.stores.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
