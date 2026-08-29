@extends('admin.layouts.app')

@section('title', 'Manage Order — ' . $store->name)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.offers.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">&larr; All Coupons &amp; Deals</a>
            <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ $store->name }}</h2>
        </div>
        <a href="{{ route('admin.offers.create', ['store_id' => $store->id]) }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
            + Add Offer
        </a>
    </div>

    <p class="mb-3 text-sm text-gray-500">Drag rows to change the display order on the storefront.</p>

    <div class="overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Discount</th>
                    <th class="px-4 py-3">Badges</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody data-sortable data-sortable-url="{{ route('admin.offers.reorder') }}" class="divide-y divide-gray-100">
                @foreach ($offers as $offer)
                    <tr data-sort-id="{{ $offer->id }}" class="cursor-move hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $offer->title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ucfirst($offer->offer_type) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $offer->code ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            @if ($offer->discount_type === 'flat') ${{ number_format($offer->discount_value, 2) }}
                            @elseif ($offer->discount_type === 'percentage') {{ rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.') }}%
                            @else {{ $offer->badge_label ?? '—' }} @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            @foreach ($offer->badges as $badge)
                                <span class="rounded px-1.5 py-0.5 text-xs {{ $badge->classes() }}">{{ $badge->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            @if ($offer->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.offers.edit', $offer) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this offer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if ($offers->isEmpty())
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No offers for this store yet.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
