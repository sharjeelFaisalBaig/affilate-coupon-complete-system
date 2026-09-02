{{--
    Homepage category-section card. Expects $offer (with store loaded) and $region.
--}}
@php
    $store = $offer->store;
    $thumbnailPath = $store->logo_path;
    $badgeItems = collect();
    if ($offer->isVerified()) {
        $badgeItems->push(['label' => 'Verified', 'classes' => 'bg-sky-50 text-sky-700', 'check' => true]);
    }
    foreach ($offer->badges->where('name', '!=', 'Verified') as $badge) {
        $badgeItems->push(['label' => $badge->name, 'classes' => $badge->classes(), 'check' => false]);
    }
    $isCoupon = $offer->isCoupon();
    $redirectUrl = route('public.offer.redirect', [$region->code, $offer]);
@endphp
<div class="flex cursor-pointer flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
     @if ($isCoupon) data-coupon-cta data-offer-id="{{ $offer->id }}" data-redirect-url="{{ $redirectUrl }}"
     @else data-deal-cta data-redirect-url="{{ $redirectUrl }}" @endif>
    @if ($thumbnailPath)
        <img src="{{ Storage::url($thumbnailPath) }}" alt="{{ $store->name }}" width="72" height="28" loading="lazy" class="h-7 max-w-[100px] object-contain">
    @else
        @include('public.partials.placeholder-image', ['class' => 'h-7 w-16 rounded'])
    @endif

    <p class="mt-3 text-lg font-bold text-gray-900">{{ $offer->title }}</p>

    {{-- Each badge on its own line — 2 badges side-by-side reads as noise. --}}
    @if ($badgeItems->isNotEmpty())
        <div class="mt-1 flex flex-col items-start gap-1">
            @foreach ($badgeItems as $badge)
                <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $badge['classes'] }}">
                    @if ($badge['check']) &check; @endif{{ $badge['label'] }}
                </span>
            @endforeach
        </div>
    @endif

    <p class="mt-1 flex-1 text-sm text-gray-600">At {{ $store->name }}</p>

    @if ($offer->expiry_date)
        <p class="mt-1 text-xs text-gray-400">Expires {{ $offer->expiry_date->format('M j, Y') }}</p>
    @endif

    <a href="{{ route('public.store', [$region->code, $store->slug]) }}" onclick="event.stopPropagation()" class="mt-2 text-xs text-gray-700 underline hover:text-emerald-600">
        More {{ $store->name }} {{ $isCoupon ? 'coupons' : 'deals' }}
    </a>

    <p class="mt-1 text-xs text-gray-400">{{ $offer->usageLabel() }}</p>

    <div class="mt-3">
        @if ($isCoupon)
            <button type="button" class="block w-full rounded-md bg-emerald-500 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-600">
                Shop with code
            </button>
        @else
            <span class="block w-full rounded-md bg-sky-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-sky-700">
                View Deal
            </span>
        @endif
    </div>
</div>

@if ($isCoupon)
    @include('public.partials.offer-modal', ['offer' => $offer, 'store' => $store, 'redirectUrl' => $redirectUrl])
@endif
