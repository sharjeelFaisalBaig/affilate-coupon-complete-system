<footer class="border-t border-gray-100 bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-4">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $footerAboutMenu?->name ?? 'About Us' }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @foreach (($footerAboutMenu?->items ?? []) as $item)
                        <li><a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="hover:text-emerald-600">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $footerConnectMenu?->name ?? 'Connect' }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @foreach (($footerConnectMenu?->items ?? []) as $item)
                        <li><a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="hover:text-emerald-600">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $footerShopMenu?->name ?? 'Shop Coupons' }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @foreach (($footerShopMenu?->items ?? []) as $item)
                        <li><a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="hover:text-emerald-600">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="text-sm text-gray-500">
                @if ($generalSettings->footer_text)
                    <div class="prose prose-sm max-w-none">{!! $generalSettings->footer_text !!}</div>
                @else
                    <p>dealhub finds coupon codes, discount sales and promotions for e-commerce stores listed in our <a href="{{ route('public.stores', $region->code) }}" class="text-emerald-600 hover:underline">store directory</a>.</p>
                @endif
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6 text-center text-xs text-gray-400">
            @if ($generalSettings->footer_disclaimer)
                <p><strong>Disclaimer:</strong> {{ $generalSettings->footer_disclaimer }}</p>
            @endif
            <p class="mt-2">{{ $generalSettings->rights_text ?: '© 2015 – '.date('Y').' dealhub. All rights reserved.' }}</p>
        </div>
    </div>
</footer>
