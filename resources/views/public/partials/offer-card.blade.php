{{--
    Listing/store-detail card — used on /coupons and the store detail page.
    Expects $offer (with store loaded) and $region. Pass $hideStoreLink=true
    on the store detail page (SRS: same card layout minus the store link,
    since we're already on that store's page).
--}}
@php
    $store = $offer->store;
    $hideStoreLink = $hideStoreLink ?? false;
    // The promotion's own uploaded thumbnail overrides the store's default
    // logo for this card only — falls back to the store logo, then a
    // generic placeholder, if the promotion has no image of its own.
    $thumbnailPath = $offer->image_path ?: $store->logo_path;
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
<div class="flex flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-2">
        <p class="text-lg font-bold text-gray-900">{{ $offer->displayLabelFor($region) }}</p>
        @if ($thumbnailPath)
            <img src="{{ Storage::url($thumbnailPath) }}" alt="{{ $store->name }}" width="72" height="28" loading="lazy"
                 class="h-7 max-w-[72px] shrink-0 object-contain">
        @else
            @include('public.partials.placeholder-image', ['class' => 'h-7 w-16 rounded'])
        @endif
    </div>

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

    <p class="mt-2 flex-1 text-sm text-gray-600">{{ $offer->title }} at {{ $store->name }}</p>

    @if ($offer->expiry_date)
        <p class="mt-1 text-xs text-gray-400">Expires {{ $offer->expiry_date->format('M j, Y') }}</p>
    @endif

    <div class="mt-4 border-t border-gray-100 pt-3">
        <div class="flex justify-end">
            @if ($isCoupon)
                <button type="button"
                        data-coupon-cta
                        data-offer-id="{{ $offer->id }}"
                        data-redirect-url="{{ $redirectUrl }}"
                        class="btn-ribbon rounded-md bg-emerald-500 px-6 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-600">
                    Show Coupon Code
                </button>
            @else
                <a href="{{ $redirectUrl }}" target="_blank" rel="noopener sponsored"
                   class="btn-ribbon rounded-md bg-sky-600 px-6 py-2 text-center text-sm font-semibold text-white hover:bg-sky-700">
                    View Deal
                </a>
            @endif
        </div>

        @unless ($hideStoreLink)
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <a href="{{ route('public.store', [$region->code, $store->slug]) }}" class="text-xs text-gray-500 hover:text-emerald-600">
                    More {{ $store->name }} {{ $isCoupon ? 'coupon codes' : 'deals' }}
                </a>
            </div>
        @endunless
        <p class="mt-1 text-xs text-gray-400">{{ $offer->usageLabel() }}</p>
    </div>
</div>

@if ($isCoupon)
    @include('public.partials.offer-modal', ['offer' => $offer, 'store' => $store, 'redirectUrl' => $redirectUrl])
@endif
