@extends('public.layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ $heading }}</h1>
            <form method="GET" class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles..."
                       class="rounded-full border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </form>
        </div>
        @if ($subheading)
            <p class="mt-2 text-sm text-gray-500">{{ $subheading }}</p>
        @endif

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('public.blogs', $region->code) }}" class="rounded-full border px-3 py-1 text-xs font-medium {{ !request('category') ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-600' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('public.blogs', $region->code) }}?category={{ $category->slug }}"
                   class="rounded-full border px-3 py-1 text-xs font-medium {{ request('category') === $category->slug ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-600' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        @if ($featured)
            <a href="{{ route('public.blog', [$region->code, $featured->slug]) }}" class="mt-6 flex flex-col gap-4 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 sm:flex-row">
                @if ($featured->featured_image)
                    <img src="{{ Storage::url($featured->featured_image) }}" alt="{{ $featured->title }}" width="320" height="180" loading="lazy" class="h-[180px] w-full rounded-lg object-cover sm:w-[320px]">
                @else
                    @include('public.partials.placeholder-image', ['class' => 'h-[180px] w-full rounded-lg sm:w-[320px]', 'iconClass' => 'h-10 w-10'])
                @endif
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">{{ $featured->blogCategory?->name }}</p>
                    <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $featured->title }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $featured->excerpt }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ optional($featured->published_at)->format('M j, Y') }} &middot; {{ $featured->reading_time_minutes }} min read</p>
                </div>
            </a>
        @endif

        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($blogs as $blog)
                <a href="{{ route('public.blog', [$region->code, $blog->slug]) }}" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                    @if ($blog->featured_image)
                        <img src="{{ Storage::url($blog->featured_image) }}" alt="{{ $blog->title }}" width="400" height="220" loading="lazy" class="mb-3 h-[180px] w-full rounded-lg object-cover">
                    @else
                        <div class="mb-3">
                            @include('public.partials.placeholder-image', ['class' => 'h-[180px] w-full rounded-lg', 'iconClass' => 'h-10 w-10'])
                        </div>
                    @endif
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">{{ $blog->blogCategory?->name }}</p>
                    <h2 class="mt-1 font-bold text-gray-900">{{ $blog->title }}</h2>
                    <p class="mt-2 text-xs text-gray-400">{{ optional($blog->published_at)->format('M j, Y') }} &middot; {{ $blog->reading_time_minutes }} min read</p>
                </a>
            @empty
                <p class="text-gray-400">No blog posts found.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $blogs->links() }}</div>
    </div>
@endsection
