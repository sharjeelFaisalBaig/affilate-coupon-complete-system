<header class="sticky top-0 z-40 border-b border-gray-100 bg-white/90 backdrop-blur-sm">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ \App\Models\PageSetting::urlFor($region, 'home') }}" class="flex shrink-0 items-center gap-2">
            @if ($generalSettings->logo_path)
                <img src="{{ Storage::url($generalSettings->logo_path) }}" alt="{{ $region->name }}" class="h-8 w-auto object-contain">
            @else
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                </span>
                <span class="text-lg font-bold text-gray-900">dealhub</span>
            @endif
        </a>

        @php $headerSearchTarget = str_starts_with($pageType ?? '', 'blog_') ? 'blogs' : 'stores'; @endphp
        <form action="{{ \App\Models\PageSetting::urlFor($region, $headerSearchTarget) }}" method="GET" class="hidden flex-1 justify-center md:flex">
            <div class="relative w-full max-w-96">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $headerSearchTarget === 'blogs' ? 'Search articles...' : 'Search for brands...' }}" autocomplete="off"
                       data-autosuggest-endpoint="{{ route($headerSearchTarget === 'blogs' ? 'public.suggest.blogs' : 'public.suggest.stores', $region->code) }}"
                       class="w-full rounded-full border border-gray-300 !py-2 pl-4 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="absolute right-1 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </form>

        <nav class="hidden shrink-0 items-center gap-6 text-sm font-medium text-gray-700 md:flex">
            @foreach (($headerMenu?->items ?? []) as $item)
                <a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="hover:text-emerald-600">{{ $item->title }}</a>
            @endforeach

            <form method="GET" action="{{ url('/') }}" onchange="window.location.href = '/' + this.region.value + '/'">
                <select name="region" class="!py-1.5 rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ($allRegions as $r)
                        <option value="{{ $r->code }}" @selected($r->id === $region->id)>{{ strtoupper($r->code) }}</option>
                    @endforeach
                </select>
            </form>
        </nav>

        <button type="button" data-toggle="#mobile-menu" aria-label="Open menu"
                class="flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 text-gray-600 md:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>
    </div>

    <div id="mobile-menu" class="hidden border-t border-gray-100 px-4 py-4 sm:px-6 md:hidden">
        <form action="{{ \App\Models\PageSetting::urlFor($region, $headerSearchTarget) }}" method="GET" class="mb-4">
            <div class="relative">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $headerSearchTarget === 'blogs' ? 'Search articles...' : 'Search for brands...' }}" autocomplete="off"
                       data-autosuggest-endpoint="{{ route($headerSearchTarget === 'blogs' ? 'public.suggest.blogs' : 'public.suggest.stores', $region->code) }}"
                       class="w-full rounded-full border border-gray-300 !py-2 pl-4 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="absolute right-1 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </form>

        <nav class="flex flex-col gap-3 text-sm font-medium text-gray-700">
            @foreach (($headerMenu?->items ?? []) as $item)
                <a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="hover:text-emerald-600">{{ $item->title }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ url('/') }}" onchange="window.location.href = '/' + this.region.value + '/'" class="mt-4">
            <label class="mb-1 block text-xs font-medium text-gray-500">Region</label>
            <select name="region" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @foreach ($allRegions as $r)
                    <option value="{{ $r->code }}" @selected($r->id === $region->id)>{{ $r->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
</header>
