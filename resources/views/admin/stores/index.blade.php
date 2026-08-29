@extends('admin.layouts.app')

@section('title', 'Stores')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            <a href="{{ route('admin.stores.classification') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Featured & Popular
            </a>
            <a href="{{ route('admin.stores.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                + Add Store
            </a>
        </div>
    </div>

    <div data-ajax-filter data-base-url="{{ route('admin.stores.index') }}">
        <form data-ajax-filter-form class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, title, description..."
                   class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <div class="flex flex-wrap gap-2">
                @include('partials.category-cascade', [
                    'categories' => $categories,
                    'paramName' => 'category_id',
                    'selectedId' => request('category_id'),
                ])
            </div>
            <select name="letter" class="rounded-md border-gray-300 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">A–Z</option>
                @foreach (range('A', 'Z') as $letter)
                    <option value="{{ $letter }}" @selected(request('letter') === $letter)>{{ $letter }}</option>
                @endforeach
            </select>
            <select name="promotions" class="rounded-md border-gray-300 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Any Promotions</option>
                <option value="coupons" @selected(request('promotions') === 'coupons')>Coupons Only</option>
                <option value="deals" @selected(request('promotions') === 'deals')>Deals Only</option>
                <option value="both" @selected(request('promotions') === 'both')>Both</option>
                <option value="none" @selected(request('promotions') === 'none')>None</option>
            </select>
            <select name="status" class="rounded-md border-gray-300 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            @if (request()->hasAny(['q', 'category_id', 'letter', 'promotions', 'status']))
                <a href="{{ route('admin.stores.index') }}" data-no-ajax class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div data-ajax-filter-results>
            @include('admin.stores._results')
        </div>
    </div>
@endsection
