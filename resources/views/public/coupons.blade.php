@extends('public.layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $heading }}</h1>
        @if ($subheading)
            <p class="mt-2 text-sm text-gray-500">{{ $subheading }}</p>
        @endif

        <div data-ajax-filter data-base-url="{{ route('public.coupons', $region->code) }}" class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[30%_1fr]">
            <aside>
                <form data-ajax-filter-form class="space-y-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Search</p>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search promo codes..."
                               class="mt-2 block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Filter by Store Category</p>
                        <div data-cascade-selects-wrap class="mt-2 flex flex-col gap-2">
                            @include('partials.category-cascade', [
                                'categories' => $storeCategories,
                                'paramName' => 'store_category_id',
                                'selectedId' => $selectedStoreCategoryId,
                            ])
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Promotion Type</p>
                        <select name="promotion_type" class="mt-2 block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Types</option>
                            @foreach ($promotionTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('promotion_type') == $type->id)>{{ $type->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Badges</p>
                        <select name="badge" class="mt-2 block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Badges</option>
                            @foreach ($badges as $badge)
                                <option value="{{ $badge->id }}" @selected(request('badge') == $badge->id)>{{ $badge->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Sort By</p>
                        <select name="sort" class="mt-2 block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Latest</option>
                            <option value="discount_high" @selected(request('sort') === 'discount_high')>High to Low Discount</option>
                            <option value="discount_low" @selected(request('sort') === 'discount_low')>Low to High Discount</option>
                            <option value="most_used" @selected(request('sort') === 'most_used')>Most Used</option>
                            <option value="most_unused" @selected(request('sort') === 'most_unused')>Most Unused</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="expiry" @selected(request('sort') === 'expiry')>Expiry Date</option>
                        </select>
                    </div>

                    <a href="{{ route('public.coupons', $region->code) }}" data-no-ajax class="block text-center text-sm text-gray-500 hover:text-gray-700">Clear all</a>
                </form>
            </aside>

            <div data-ajax-filter-results>
                @include('public.partials.coupons-results')
            </div>
        </div>
    </div>
@endsection
