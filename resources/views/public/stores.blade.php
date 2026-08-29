@extends('public.layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $heading }}</h1>
        @if ($subheading)
            <p class="mt-2 text-sm text-gray-500">{{ $subheading }}</p>
        @endif

        <div data-ajax-filter data-base-url="{{ route('public.stores', $region->code) }}" class="mt-6">
            {{-- Row 1: category cascade filter + search + explicit Filter button --}}
            <form data-ajax-filter-form class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Category</label>
                    <div class="flex flex-wrap gap-2">
                        @include('partials.category-cascade', [
                            'categories' => $categories,
                            'paramName' => 'category_id',
                            'selectedId' => $selectedCategoryId,
                        ])
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search for a store..."
                           class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                @if (request()->hasAny(['q', 'category_id']))
                    <div class="flex flex-col justify-end">
                        <span class="mb-1 block text-xs font-medium text-transparent select-none" aria-hidden="true">Clear</span>
                        <a href="{{ route('public.stores', $region->code) }}" data-no-ajax
                           class="flex h-[2.625rem] items-center text-sm text-gray-500 hover:text-gray-700">Clear</a>
                    </div>
                @endif
            </form>

            {{-- Row 2: 2-per-row store cards with split coupon/deal counts --}}
            <div data-ajax-filter-results class="mt-6">
                @include('public.partials.stores-results')
            </div>
        </div>

        {{-- Row 3: A-Z alphabetical directory, independent of the filters above --}}
        @if ($directory->isNotEmpty())
            <div class="mt-12 border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-gray-900">Browse All Stores A&ndash;Z</h2>
                <div class="mt-4 space-y-6">
                    @foreach (range('A', 'Z') as $letter)
                        @continue($directory->get($letter, collect())->isEmpty())
                        <div>
                            <h3 class="text-2xl font-bold text-gray-300">{{ $letter }}</h3>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                @foreach ($directory[$letter] as $store)
                                    <a href="{{ route('public.store', [$region->code, $store->slug]) }}" class="text-sm text-gray-600 hover:text-emerald-600">{{ $store->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
