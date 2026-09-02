@extends('admin.layouts.app')

@section('title', 'Promotions')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Promotions are managed one store at a time. Drag rows to reorder how they appear on that store's page.</p>
        <a href="{{ route('admin.offers.create', ['store_id' => $selectedStore?->id]) }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
            + Add Promotion
        </a>
    </div>

    <div data-ajax-filter data-base-url="{{ route('admin.offers.index') }}">
        <form data-ajax-filter-form class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Store</label>
                <select name="store_id" required class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected($selectedStore?->id === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Title, code, feature..."
                       class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="flex flex-col justify-end">
                <span class="mb-1 block text-xs font-medium text-transparent select-none" aria-hidden="true">Search</span>
                @include('partials.ajax-search-button')
            </div>
        </form>

        <div data-ajax-filter-results>
            @include('admin.offers._results')
        </div>
    </div>
@endsection
