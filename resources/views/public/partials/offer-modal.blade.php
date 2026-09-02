{{-- Expects $offer, $store, $redirectUrl --}}
<div id="offer-modal-{{ $offer->id }}" data-modal class="hidden fixed inset-0 z-40 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50"></div>
    <div class="relative w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
        <button type="button" data-modal-close class="absolute right-4 top-4 text-gray-400 hover:text-gray-600">&times;</button>
        <p class="text-sm text-gray-500">{{ $store->name }}</p>
        <p class="mt-1 text-lg font-bold text-gray-900">{{ $offer->title }}</p>

        <div class="mt-4 flex items-center rounded-md border-2 border-dashed border-emerald-400 bg-emerald-50 p-3">
            <span class="flex-1 truncate font-mono text-lg font-semibold text-emerald-700">{{ $offer->code }}</span>
            <button type="button" data-copy="{{ $offer->code }}" data-copy-label="Copy"
                    class="ml-3 shrink-0 rounded-md bg-emerald-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-600">
                Copy
            </button>
        </div>

        <p class="mt-3 text-xs text-gray-400">We've opened {{ $store->name }} in another tab — keep this tab open to copy your code.</p>

        <a href="{{ $redirectUrl }}" target="_blank" rel="noopener sponsored"
           class="mt-4 block w-full rounded-md bg-gray-900 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-gray-800">
            Continue to {{ $store->name }}
        </a>
    </div>
</div>
