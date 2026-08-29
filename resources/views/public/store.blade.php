@extends('public.layouts.app')

@push('schema')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Store',
        'name' => $store->name,
        'url' => url()->current(),
        'aggregateRating' => $store->star_rating > 0 ? [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $store->star_rating,
            'bestRating' => '5',
        ] : null,
    ], JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">{{ $h1 }}</h1>
        <p class="mx-auto mt-2 max-w-2xl text-center text-sm text-gray-500">
            dealhub is a shopping community that curates offers for brands we think you'll love. When you buy through our links, we may earn a commission.
        </p>

        <div data-ajax-filter data-base-url="{{ route('public.store', [$region->code, $store->slug]) }}" class="mt-6">
            <form data-ajax-filter-form class="flex flex-wrap items-center justify-center gap-3">
                <select name="filter" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="" @selected(!request('filter'))>All Types</option>
                    <option value="coupon" @selected(request('filter') === 'coupon')>Coupon Codes ({{ $couponCount }})</option>
                    <option value="deal" @selected(request('filter') === 'deal')>Deals ({{ $dealCount }})</option>
                </select>
                <select name="promotion_type" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Promotion Types</option>
                    @foreach ($promotionTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('promotion_type') == $type->id)>{{ $type->title }}</option>
                    @endforeach
                </select>
                <select name="badge" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Badges</option>
                    @foreach ($badges as $badge)
                        <option value="{{ $badge->id }}" @selected(request('badge') == $badge->id)>{{ $badge->name }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="" @selected(!request('sort'))>Default Order</option>
                    <option value="discount_high" @selected(request('sort') === 'discount_high')>High to Low Discount</option>
                    <option value="discount_low" @selected(request('sort') === 'discount_low')>Low to High Discount</option>
                    <option value="most_used" @selected(request('sort') === 'most_used')>Most Used</option>
                    <option value="most_unused" @selected(request('sort') === 'most_unused')>Most Unused</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                    <option value="expiry" @selected(request('sort') === 'expiry')>Expiry Date</option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search this store's codes..."
                       class="rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </form>

            {{-- Main paginated offer grid — keeps our own page-number pagination, not the reference site's "load more" button --}}
            <div data-ajax-filter-results class="mt-6">
                @include('public.partials.store-offers-results')
            </div>
        </div>

        {{-- "Why search for {Store} coupons?" banner --}}
        @if ($store->banner_heading || $store->banner_text || $store->banner_image)
            <div class="mt-12 grid grid-cols-1 gap-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:grid-cols-2 lg:items-center">
                <div>
                    @if ($store->banner_heading)
                        <h2 class="text-lg font-bold text-gray-900">{{ $store->banner_heading }}</h2>
                    @endif
                    @if ($store->banner_text)
                        <div class="prose prose-emerald mt-2 max-w-none text-sm">{!! $store->banner_text !!}</div>
                    @endif
                    @if ($store->banner_button_text && $store->banner_button_url)
                        <a href="{{ $store->banner_button_url }}"
                           class="mt-4 inline-block rounded-md bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                            {{ $store->banner_button_text }}
                        </a>
                    @endif
                </div>
                <div class="order-first lg:order-last">
                    @if ($store->banner_image)
                        <img src="{{ Storage::url($store->banner_image) }}" alt="{{ $store->banner_heading ?: $store->name }}" width="480" height="270" loading="lazy" class="w-full rounded-lg object-cover">
                    @else
                        @include('public.partials.placeholder-image', ['class' => 'aspect-video w-full rounded-lg'])
                    @endif
                </div>
            </div>
        @endif

        {{-- Store info card: left = logo/title/link/rating/reviews/about, right = auto-calculated savings stats + overview --}}
        <div class="mt-12 grid grid-cols-1 gap-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:grid-cols-2">
            <div>
                <div class="flex flex-col items-center gap-2 sm:flex-row sm:items-start">
                    @if ($store->logo_path)
                        <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="120" height="60" class="h-[60px] w-[120px] shrink-0 object-contain">
                    @else
                        @include('public.partials.placeholder-image', ['class' => 'h-[60px] w-[120px] shrink-0 rounded-lg'])
                    @endif
                    <div class="text-center sm:text-left">
                        <p class="font-bold text-gray-900">{{ $store->fullTitle() ?: $store->name }}</p>
                        @if ($store->website_url)
                            <a href="{{ $store->website_url }}" target="_blank" rel="noopener" class="text-xs text-emerald-600 hover:underline">{{ $store->website_url }}</a>
                        @endif
                        <div class="mt-1 flex items-center justify-center gap-1 text-amber-500 sm:justify-start">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="{{ $i <= round($store->star_rating) ? 'currentColor' : '#e5e7eb' }}" class="h-4 w-4"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.958z"/></svg>
                            @endfor
                            <span class="ml-1 text-sm text-gray-500">{{ number_format($store->star_rating, 1) }} / 5 ({{ number_format($store->reviews_count) }} reviews)</span>
                        </div>
                    </div>
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-900">About {{ $store->name }}</h2>
                @if ($store->about)
                    <div class="prose prose-emerald mt-2 max-w-none text-sm">{!! $store->about !!}</div>
                @else
                    <p class="mt-2 text-sm text-gray-400">No description available yet.</p>
                @endif
            </div>
            <div>
                <div class="grid grid-cols-2 gap-3 rounded-lg bg-gray-50 p-4 text-sm">
                    <div><p class="text-xs text-gray-400">Verified Discount Codes</p><p class="font-bold text-gray-900">{{ $savingsStats['verified_codes'] }}</p></div>
                    <div><p class="text-xs text-gray-400">Total Coupons</p><p class="font-bold text-gray-900">{{ $savingsStats['total_coupons'] }}</p></div>
                    <div><p class="text-xs text-gray-400">Best Discount Today</p><p class="font-bold text-gray-900">{{ $savingsStats['best_discount_today'] }}</p></div>
                    <div><p class="text-xs text-gray-400">Average Shopper Savings</p><p class="font-bold text-gray-900">{{ $savingsStats['average_savings'] }}</p></div>
                    <div class="col-span-2"><p class="text-xs text-gray-400">Last Coupon Added</p><p class="font-bold text-gray-900">{{ $savingsStats['last_coupon_added'] }}</p></div>
                </div>
                @if ($store->curate_right_content)
                    <div class="prose prose-emerald mt-4 max-w-none text-sm">{!! $store->curate_right_content !!}</div>
                @endif
            </div>
        </div>

        {{-- More Verified {Store} Discount Codes — admin-curated picks --}}
        @if ($featuredOffers->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-lg font-bold text-gray-900">More Verified {{ $store->name }} Discount Codes</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredOffers as $offer)
                    @include('public.partials.offer-card', ['offer' => $offer, 'hideStoreLink' => true])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Competitor stores, with live active-code counts --}}
        @if ($store->relatedStores->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-lg font-bold text-gray-900">{{ $store->name }} Competitor Coupon Codes</h2>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($store->relatedStores as $related)
                        <a href="{{ route('public.store', [$region->code, $related->slug]) }}" class="flex flex-col items-center gap-2 rounded-lg border border-gray-200 p-3 text-center hover:bg-gray-50">
                            @if ($related->logo_path)
                                <img src="{{ Storage::url($related->logo_path) }}" alt="{{ $related->name }}" width="64" height="32" loading="lazy" class="h-8 max-w-[64px] object-contain">
                            @else
                                @include('public.partials.placeholder-image', ['class' => 'h-8 w-8 rounded-full'])
                            @endif
                            <span class="text-xs font-medium text-gray-700">{{ $related->name }}</span>
                            <span class="text-xs text-gray-400">{{ $related->activeOfferCount }} {{ $related->activeOfferCount === 1 ? 'code' : 'codes' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- FAQ --}}
        @if (!empty($store->faqs))
            <div class="mt-12">
                <h2 class="text-lg font-bold text-gray-900">{{ $store->name }} Coupon FAQ</h2>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($store->faqs as $faq)
                        <details class="rounded-md border border-gray-200 p-3">
                            <summary class="cursor-pointer text-sm font-medium text-gray-900">{{ $faq['question'] }}</summary>
                            <p class="mt-2 text-sm text-gray-600">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Row 9: admin-managed titled rich-text sections --}}
        @if (!empty($store->custom_sections))
            <div class="mt-12 space-y-8">
                @foreach ($store->custom_sections as $section)
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $section['title'] }}</h2>
                        <div class="prose prose-emerald mt-2 max-w-none text-sm">{!! $section['content'] !!}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
