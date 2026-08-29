@extends('admin.layouts.app')

@section('title', 'Promotion Types')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">The 3 system defaults (Percentage, Flat Rate, Deals) can't be deleted. Create as many custom types as you need (e.g. Shipping, Buy 1 Get 1 Free) — each is text-driven and can be renamed or removed if unused.</p>
        <a href="{{ route('admin.promotion-types.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
            + Add Type
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Type Title</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Discount Format</th>
                    <th class="px-4 py-3">Promotions</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($types as $type)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $type->title }}
                            @if ($type->is_system_default)
                                <span class="ml-1 rounded bg-sky-50 px-1.5 py-0.5 text-xs font-normal text-sky-700">System</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $type->slug }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ucfirst(str_replace('_', ' ', $type->discount_format)) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $type->offers_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.promotion-types.edit', $type) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            @unless ($type->is_system_default)
                                <form action="{{ route('admin.promotion-types.destroy', $type) }}" method="POST" class="ml-3 inline"
                                      onsubmit="return confirm('Delete this promotion type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No promotion types yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
