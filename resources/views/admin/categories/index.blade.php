@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Drag rows to reorder how categories appear on the storefront.</p>
        <a href="{{ route('admin.categories.create') }}"
           class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Category
        </a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name or slug"
                   class="rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Status</label>
            <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Published</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Draft</option>
            </select>
        </div>
        <div class="flex flex-col justify-end">
            <span class="mb-1 block text-xs font-medium text-transparent select-none" aria-hidden="true">Filter</span>
            <button type="submit" class="flex h-[2.625rem] items-center rounded-md bg-gray-900 px-4 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
        </div>
        @if (request()->hasAny(['q', 'status']))
            <div class="flex flex-col justify-end">
                <span class="mb-1 block text-xs font-medium text-transparent select-none" aria-hidden="true">Clear</span>
                <a href="{{ route('admin.categories.index') }}"
                   class="flex h-[2.625rem] items-center text-sm text-gray-500 hover:text-gray-700">Clear</a>
            </div>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Active Stores</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody data-sortable data-sortable-url="{{ route('admin.categories.reorder') }}" class="divide-y divide-gray-100">
                @forelse ($categories as $category)
                    <tr data-sort-id="{{ $category->id }}" class="cursor-move hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->slug }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->active_stores_count }}</td>
                        <td class="px-4 py-3">
                            @if ($category->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Published</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No categories match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
