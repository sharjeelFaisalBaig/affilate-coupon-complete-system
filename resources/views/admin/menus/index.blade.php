@extends('admin.layouts.app')

@section('title', 'Menus')

@section('content')
    <p class="mb-4 text-sm text-gray-500">
        Menu structure is fixed (1 Header Menu + 3 Footer Menus) — you can add, remove, and reorder items within each, but not the menus themselves.
    </p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($menus as $menu)
            <a href="{{ route('admin.menus.edit', $menu) }}"
               class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-emerald-300 hover:shadow">
                <p class="font-semibold text-gray-900">{{ $menu->name }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $menu->items_count }} {{ $menu->items_count === 1 ? 'item' : 'items' }}</p>
                <span class="mt-3 inline-block text-sm font-medium text-emerald-600">Manage items &rarr;</span>
            </a>
        @endforeach
    </div>
@endsection
