{{-- Results fragment for /stores Row 2 — also returned directly for AJAX filter requests. --}}
<div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
    @forelse ($stores as $store)
        <a href="{{ route('public.store', [$region->code, $store->slug]) }}" class="flex items-center gap-3 rounded-lg border-b border-gray-100 px-2 py-3 hover:translate-x-1 hover:bg-gray-50">
            @if ($store->logo_path)
                <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="48" height="48" loading="lazy" class="h-12 w-12 shrink-0 rounded object-contain">
            @else
                @include('public.partials.placeholder-image', ['class' => 'h-12 w-12 rounded'])
            @endif
            <div>
                <p class="font-medium text-gray-900">{{ $store->name }} coupons</p>
                <p class="text-xs text-gray-400">
                    {{ $store->active_coupons_count }} coupon{{ $store->active_coupons_count === 1 ? '' : 's' }}
                    &middot; {{ $store->active_deals_count }} deal{{ $store->active_deals_count === 1 ? '' : 's' }}
                </p>
            </div>
        </a>
    @empty
        <p class="text-gray-400">No stores found.</p>
    @endforelse
</div>

<div class="mt-6">{{ $stores->links() }}</div>
