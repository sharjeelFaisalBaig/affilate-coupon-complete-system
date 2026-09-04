@extends('public.layouts.app')

@section('content')
    {{-- Hero: gradient band + search, matching the modern coupon-site pattern
         (a plain white page with no lede is what item 26's feedback flagged). --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500">
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-teal-300/20 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h1 class="animate-fade-up text-3xl font-extrabold text-white sm:text-4xl">{{ $heading }}</h1>
            <p class="animate-fade-up mx-auto mt-3 max-w-2xl text-sm text-emerald-50 sm:text-base">{{ $subheading }}</p>

            <form action="{{ \App\Models\PageSetting::urlFor($region, 'stores') }}" method="GET" class="animate-fade-up mx-auto mt-6 flex max-w-xl gap-2">
                <input type="search" name="q" placeholder="{{ $heroSearchPlaceholder }}" autocomplete="off"
                       data-autosuggest-endpoint="{{ route('public.suggest.stores', $region->code) }}"
                       class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm text-gray-900 shadow-lg focus:outline-none focus:ring-2 focus:ring-white">
                <button type="submit" class="shrink-0 rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:-translate-y-0.5 hover:bg-amber-600 hover:shadow-xl active:translate-y-0">
                    {{ $heroSearchButtonText }}
                </button>
            </form>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @forelse ($sections as $section)
            <div data-reveal class="{{ !$loop->first ? 'mt-10' : '' }} {{ $loop->even ? 'rounded-2xl bg-gradient-to-br from-gray-50 to-emerald-50/50 p-6' : '' }}">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 {{ $loop->first ? 'text-2xl' : '' }}">{{ $section->title }}</h2>
                    @if ($section->cta_label && $section->display_cta_url)
                        <a href="{{ $section->display_cta_url }}" @if ($section->cta_target === 'new_tab') target="_blank" rel="noopener" @endif
                           class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                            {{ $section->cta_label }}
                        </a>
                    @endif
                </div>

                @if ($section->content_type === 'store')
                    {{-- Trending Stores: 6 stores, 3 per row, subtitle reflects actual inventory. --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                        @foreach ($section->stores as $store)
                            @php
                                $hasCoupons = $store->active_coupons_count > 0;
                                $hasDeals = $store->active_deals_count > 0;
                                $subtitle = match (true) {
                                    $hasCoupons && $hasDeals => 'Coupons, Promo-Codes & Deals',
                                    $hasDeals => 'Deals',
                                    $hasCoupons => 'Coupons & Promo-Codes',
                                    default => 'Coupons, Promo-Codes & Deals',
                                };
                            @endphp
                            <a href="{{ route('public.store', [$region->code, $store->slug]) }}" class="flex items-center gap-3 rounded-lg p-2 hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-sm">
                                @if ($store->logo_path)
                                    <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="48" height="48" loading="lazy" class="h-12 w-12 rounded object-contain">
                                @else
                                    @include('public.partials.placeholder-image', ['class' => 'h-12 w-12 rounded'])
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $store->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $subtitle }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif ($section->content_type === 'deal')
                    {{-- Deals Section: 4 cards per row, hero-style, click opens affiliate link. --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($section->offers as $offer)
                            @include('public.partials.offer-card-hero', ['offer' => $offer])
                        @endforeach
                    </div>
                @else
                    {{-- Coupons Per Category: 5-column single row. --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($section->offers as $offer)
                            @include('public.partials.offer-card-compact', ['offer' => $offer])
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-400">
                No homepage sections have been configured yet for {{ $region->name }}. Add some under Admin → Homepage Sections.
            </div>
        @endforelse
    </div>
@endsection
