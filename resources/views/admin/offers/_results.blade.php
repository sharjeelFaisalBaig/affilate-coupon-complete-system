<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Store</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Discount</th>
                <th class="px-4 py-3">Badges</th>
                <th class="px-4 py-3">Uses</th>
                <th class="px-4 py-3">Expiry</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($offers as $offer)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $offer->title }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        <a href="{{ route('admin.offers.manage-order', $offer->store) }}" class="hover:text-emerald-600" data-no-ajax>{{ $offer->store->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ ucfirst($offer->offer_type) }}</td>
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
                    <td class="px-4 py-3 text-gray-500">{{ number_format($offer->clicks) }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ optional($offer->expiry_date)->format('M j, Y') ?? '—' }}
                        @if ($offer->isExpired())
                            <span class="ml-1 rounded bg-red-50 px-1.5 py-0.5 text-xs text-red-700">Expired</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($offer->is_active)
                            <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.offers.edit', $offer) }}" class="font-medium text-emerald-600 hover:text-emerald-700" data-no-ajax>Edit</a>
                        <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="ml-3 inline"
                              onsubmit="return confirm('Delete this offer?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-6 text-center text-gray-400">No offers match.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $offers->links() }}</div>
