@extends('admin.layouts.app')

@section('title', 'Featured & Popular Stores')

@section('content')
    <p class="mb-4 text-sm text-gray-500">
        Drag to reorder. To add or remove a store from these lists, edit the store and toggle "Featured" / "Popular".
    </p>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <h2 class="border-b border-gray-200 px-4 py-3 font-semibold">Featured Stores</h2>
            <ul data-sortable data-sortable-url="{{ route('admin.stores.reorder-featured') }}" class="divide-y divide-gray-100">
                @forelse ($featured as $store)
                    <li data-sort-id="{{ $store->id }}" class="cursor-move px-4 py-3 text-sm">{{ $store->name }}</li>
                @empty
                    <li class="px-4 py-6 text-center text-gray-400 text-sm">No featured stores yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <h2 class="border-b border-gray-200 px-4 py-3 font-semibold">Popular Stores</h2>
            <ul data-sortable data-sortable-url="{{ route('admin.stores.reorder-popular') }}" class="divide-y divide-gray-100">
                @forelse ($popular as $store)
                    <li data-sort-id="{{ $store->id }}" class="cursor-move px-4 py-3 text-sm">{{ $store->name }}</li>
                @empty
                    <li class="px-4 py-6 text-center text-gray-400 text-sm">No popular stores yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
