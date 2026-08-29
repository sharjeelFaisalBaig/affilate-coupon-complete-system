<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Coupons Platform Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/drag-sort.js', 'resources/js/offer-form.js', 'resources/js/script-injection-form.js', 'resources/js/faq-builder.js', 'resources/js/homepage-section-form.js', 'resources/js/homepage-section-picker.js', 'resources/js/category-cascade.js', 'resources/js/ajax-filters.js'])
    @stack('head')
</head>
<body class="h-full text-gray-900 antialiased">
    <div class="flex h-full min-h-screen">
        <!-- Sidebar -->
        <aside class="hidden w-64 shrink-0 flex-col border-r border-gray-200 bg-white lg:flex">
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-6">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                </span>
                <span class="text-lg font-bold">Coupons<span class="text-emerald-500">CMS</span></span>
            </div>

            <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-4 text-sm">
                @php
                    // Grouped so nothing manageable ends up reachable from two
                    // different places — the Pages group is the ONLY entry
                    // point for Homepage/Contact/Static Pages/Page Settings,
                    // each of which used to also sit here as its own top-level item.
                    $navGroups = [
                        [
                            'label' => null,
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
                            ],
                        ],
                        [
                            'label' => 'Catalog',
                            'items' => [
                                ['label' => 'Categories', 'route' => 'admin.categories.index', 'match' => 'admin.categories.*'],
                                ['label' => 'Stores', 'route' => 'admin.stores.index', 'match' => 'admin.stores.*'],
                                ['label' => 'Coupons & Deals', 'route' => 'admin.offers.index', 'match' => 'admin.offers.*'],
                            ],
                        ],
                        [
                            'label' => 'Taxonomies',
                            'items' => [
                                ['label' => 'Promotion Types', 'route' => 'admin.promotion-types.index', 'match' => 'admin.promotion-types.*'],
                                ['label' => 'Badges', 'route' => 'admin.badges.index', 'match' => 'admin.badges.*'],
                                ['label' => 'Currencies', 'route' => 'admin.currencies.index', 'match' => 'admin.currencies.*'],
                            ],
                        ],
                        [
                            'label' => 'Pages',
                            'items' => [
                                // The ONLY entry point for page management — Homepage
                                // Sections, Page Settings, Contact Page agendas, and
                                // Static Pages are all reached from here, never as
                                // separate top-level items, so nothing is manageable
                                // from two different places in the sidebar.
                                ['label' => 'Pages', 'route' => 'admin.pages-overview.index', 'match' => ['admin.pages-overview.*', 'admin.homepage-sections.*', 'admin.page-settings.*', 'admin.contact-page.*', 'admin.static-pages.*']],
                            ],
                        ],
                        [
                            'label' => 'Blog',
                            'items' => [
                                ['label' => 'Blog Categories', 'route' => 'admin.blog-categories.index', 'match' => 'admin.blog-categories.*'],
                                ['label' => 'Blogs', 'route' => 'admin.blogs.index', 'match' => 'admin.blogs.*'],
                            ],
                        ],
                        [
                            'label' => 'Region Settings',
                            'items' => [
                                ['label' => 'Regions', 'route' => 'admin.regions.index', 'match' => 'admin.regions.*'],
                                ['label' => 'Menus', 'route' => 'admin.menus.index', 'match' => 'admin.menus.*'],
                                ['label' => 'General Settings', 'route' => 'admin.general-settings.edit', 'match' => 'admin.general-settings.*'],
                            ],
                        ],
                        [
                            'label' => 'System',
                            'items' => [
                                ['label' => 'Script Injections', 'route' => 'admin.script-injections.index', 'match' => 'admin.script-injections.*'],
                                ['label' => 'Affiliate Networks', 'route' => 'admin.affiliate-networks.index', 'match' => 'admin.affiliate-networks.*'],
                                ['label' => 'Contact Messages', 'route' => 'admin.contact-messages.index', 'match' => 'admin.contact-messages.*'],
                            ],
                        ],
                    ];
                @endphp

                @foreach ($navGroups as $group)
                    @php
                        $visibleItems = collect($group['items'])->filter(fn ($item) => Route::has($item['route']));
                    @endphp
                    @if ($visibleItems->isNotEmpty())
                        <div>
                            @if ($group['label'])
                                <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $group['label'] }}</p>
                            @endif
                            <div class="space-y-0.5">
                                @foreach ($visibleItems as $item)
                                    <a href="{{ route($item['route']) }}"
                                       class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs(...(array) $item['match']) ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <!-- Topbar -->
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>

                <div class="flex items-center gap-4">
                    @if(isset($allRegions) && isset($activeRegion))
                        <form method="POST" action="{{ route('admin.region.switch') }}">
                            @csrf
                            <select name="region_id" onchange="this.form.submit()"
                                    class="rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach ($allRegions as $r)
                                    <option value="{{ $r->id }}" @selected($activeRegion->id === $r->id)>{{ strtoupper($r->code) }} — {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <div class="relative">
                        <button type="button" data-toggle="#user-menu" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            {{ auth()->user()?->name }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                        </button>
                        <div id="user-menu" class="hidden absolute right-0 z-50 mt-2 w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
