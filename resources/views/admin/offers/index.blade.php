@extends('admin.layouts.app')

@section('title', 'Coupons & Deals')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">All coupons and deals across every store in this region.</p>
        <a href="{{ route('admin.offers.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
            + Add Offer
        </a>
    </div>

    <div data-ajax-filter data-base-url="{{ route('admin.offers.index') }}">
        <form data-ajax-filter-form class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Title, code, store, category, badge..."
                       class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Store</label>
                <select name="store_id" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Stores</option>
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected(request('store_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Store Category</label>
                <div class="flex flex-wrap gap-2">
                    @include('partials.category-cascade', [
                        'categories' => $storeCategories,
                        'paramName' => 'store_category_id',
                        'selectedId' => request('store_category_id'),
                    ])
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Promotion Type</label>
                <select name="promotion_type_id" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Types</option>
                    @foreach ($promotionTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('promotion_type_id') == $type->id)>{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Badge</label>
                <select name="badge_id" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Badges</option>
                    @foreach ($badges as $badge)
                        <option value="{{ $badge->id }}" @selected(request('badge_id') == $badge->id)>{{ $badge->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Status</label>
                <select name="status" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Sort By</label>
                <select name="sort" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>Latest</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                    <option value="discount_high" @selected(request('sort') === 'discount_high')>Discount High to Low</option>
                    <option value="discount_low" @selected(request('sort') === 'discount_low')>Discount Low to High</option>
                    <option value="most_used" @selected(request('sort') === 'most_used')>Most Used</option>
                    <option value="most_unused" @selected(request('sort') === 'most_unused')>Most Unused</option>
                    <option value="expiry" @selected(request('sort') === 'expiry')>Expiry Date</option>
                </select>
            </div>
            @if (request()->hasAny(['q', 'store_id', 'store_category_id', 'promotion_type_id', 'badge_id', 'status', 'sort']))
                <div class="flex flex-col justify-end">
                    <span class="mb-1 block text-xs font-medium text-transparent select-none" aria-hidden="true">Clear</span>
                    <a href="{{ route('admin.offers.index') }}" data-no-ajax
                       class="flex h-[2.625rem] items-center text-sm text-gray-500 hover:text-gray-700">Clear</a>
                </div>
            @endif
        </form>

        <div data-ajax-filter-results>
            @include('admin.offers._results')
        </div>
    </div>
@endsection
