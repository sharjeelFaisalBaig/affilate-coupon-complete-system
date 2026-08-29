{{--
    "Deals Section" card per the SRS: thumbnail, store name linking to the
    store detail page, used count, up to 2 badges, expiry date, deal title.
    The store logo links to the store detail page; the rest of the card
    opens the deal's affiliate link in a new focused tab and increments the
    used count on click (two sibling links, not nested, to stay valid HTML).
    Expects $offer (with store loaded) and $region.
--}}
@php
    $store = $offer->store;
    $redirectUrl = route('public.offer.redirect', [$region->code, $offer]);
@endphp
<div class="flex flex-col rounded-xl border border-gray-200 bg-white shadow-sm hover:border-emerald-300 hover:shadow">
    <a href="{{ route('public.store', [$region->code, $store->slug]) }}" class="flex h-48 items-center justify-center border-b border-gray-100 p-6">
        @if ($store->logo_path)
            <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="160" height="80" loading="lazy" class="max-h-full max-w-full object-contain">
        @else
            @include('public.partials.placeholder-image', ['class' => 'h-20 w-20 rounded', 'iconClass' => 'h-8 w-8'])
        @endif
    </a>
    <a href="{{ $redirectUrl }}" target="_blank" rel="noopener sponsored" class="block p-4">
        {{-- Each badge on its own line — 2 badges side-by-side reads as noise. --}}
        @if ($offer->badges->isNotEmpty())
            <div class="flex flex-col items-start gap-1">
                @foreach ($offer->badges as $badge)
                    <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $badge->classes() }}">{{ $badge->name }}</span>
                @endforeach
            </div>
        @endif
        @if ($offer->expiry_date)
            <p class="mt-1 text-xs text-gray-400">Expires {{ $offer->expiry_date->format('M j, Y') }}</p>
        @endif
        <p class="mt-1 text-xs text-gray-400">
            {{ $store->name }} code &middot; {{ $offer->usageLabel() }}
        </p>
        <p class="mt-1 font-bold text-gray-900">{{ $offer->title }} at {{ $store->name }}</p>
    </a>
</div>
