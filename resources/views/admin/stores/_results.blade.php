<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Store</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Offers</th>
                <th class="px-4 py-3">Rating</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($stores as $store)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $store->name }}
                        @if ($store->is_featured)<span class="ml-1 rounded bg-amber-50 px-1.5 py-0.5 text-xs text-amber-700">Featured</span>@endif
                        @if ($store->is_popular)<span class="ml-1 rounded bg-sky-50 px-1.5 py-0.5 text-xs text-sky-700">Popular</span>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $store->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $store->offers_count }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $store->star_rating }}</td>
                    <td class="px-4 py-3">
                        <form action="{{ route('admin.stores.toggle-active', $store) }}" method="POST" data-no-ajax>
                            @csrf
                            <button type="submit"
                                    class="rounded-full px-2 py-1 text-xs font-medium {{ $store->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $store->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.offers.manage-order', $store) }}" class="font-medium text-gray-500 hover:text-gray-700" data-no-ajax>Offers</a>
                        <a href="{{ route('admin.stores.edit', $store) }}" class="ml-3 font-medium text-emerald-600 hover:text-emerald-700" data-no-ajax>Edit</a>
                        <form action="{{ route('admin.stores.destroy', $store) }}" method="POST" class="ml-3 inline"
                              onsubmit="return confirm('Delete this store? This is only possible if it has no active coupons or deals.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No stores found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $stores->links() }}</div>
