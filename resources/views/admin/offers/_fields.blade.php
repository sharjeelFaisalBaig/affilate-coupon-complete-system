{{--
    Expects $offer (App\Models\Offer, possibly new) and $badges (Collection<Badge>) in scope.
    $stores/$selectedStore/$promotionTypes are only passed by the global create/edit
    forms — the store-scoped manage-order drawers omit them since the store is
    already fixed by context, and just post a hidden store_id alongside this partial.
--}}
@php
    $selectedBadgeIds = old('badge_ids', $offer->exists ? $offer->badges->pluck('id')->all() : []);
@endphp

@if (isset($stores))
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Store / Brand</label>
        <select name="store_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">— Select a store —</option>
            @foreach ($stores as $storeOption)
                <option value="{{ $storeOption->id }}" @selected(old('store_id', $selectedStore?->id) == $storeOption->id)>{{ $storeOption->name }}</option>
            @endforeach
        </select>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Offer Type</label>
        <select name="offer_type" data-offer-type required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="coupon" @selected(old('offer_type', $offer->offer_type) === 'coupon')>Coupon Code</option>
            <option value="deal" @selected(old('offer_type', $offer->offer_type) === 'deal')>Deal (no code)</option>
        </select>
    </div>

    <div data-code-wrapper>
        <label class="mb-1 block text-sm font-medium text-gray-700">Coupon Code</label>
        <input type="text" name="code" value="{{ old('code', $offer->code) }}"
               class="block w-full rounded-md border-gray-300 uppercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700" data-destination-url-label>Affiliate Link / CTA URL</label>
    <input type="url" name="destination_url" value="{{ old('destination_url', $offer->destination_url) }}" required
           placeholder="https://www.merchant.com/deal-page"
           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    <p class="mt-1 text-xs text-gray-400" data-destination-url-hint>
        Coupon: opened in a new tab when the shopper clicks the CTA, while the code modal stays open here. Deal: where "View Deal" sends the shopper.
    </p>
</div>

@if (isset($promotionTypes))
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Promotion Type</label>
        <select name="promotion_type_id" data-promotion-type required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">— Select a promotion type —</option>
            @foreach ($promotionTypes as $promotionType)
                <option value="{{ $promotionType->id }}" data-discount-format="{{ $promotionType->discount_format }}"
                        @selected(old('promotion_type_id', $offer->promotion_type_id) == $promotionType->id)>{{ $promotionType->title }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-400">Drives the Discount Value field below (percentage, flat currency, or text-only).</p>
    </div>
@endif

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
    <input type="text" name="title" value="{{ old('title', $offer->title) }}" required maxlength="255"
           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    <p class="mt-1 text-xs text-gray-400">Keep to ~60 characters so it doesn't wrap awkwardly on cards.</p>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Short Description</label>
    <textarea name="description" rows="2" maxlength="500"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $offer->description) }}</textarea>
    <p class="mt-1 text-xs text-gray-400">Optimal length: ~120 characters.</p>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Promotion Image / Thumbnail</label>
    <p class="mb-1 text-xs text-gray-400">* Optimal size: 400x400px.</p>
    @if ($offer->image_path)
        <img src="{{ Storage::url($offer->image_path) }}" alt="" width="80" height="80" class="mb-2 h-20 w-20 rounded border border-gray-200 object-cover">
    @endif
    <input type="file" name="image" accept="image/*"
           class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
</div>

<input type="hidden" name="discount_type" data-discount-type-hidden value="{{ old('discount_type', $offer->discount_type ?? 'percentage') }}">

<div data-discount-value-wrapper>
    <label data-discount-label class="mb-1 block text-sm font-medium text-gray-700">Discount Value</label>
    <input type="number" step="0.01" min="0" name="discount_value" data-discount-value-input
           value="{{ old('discount_value', $offer->discount_value) }}"
           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">
        Badge Label Override <span data-badge-label-required-hint class="hidden text-red-500">(required for text-only promotion types — e.g. "BOGO", "Free Shipping")</span>
    </label>
    <input type="text" name="badge_label" value="{{ old('badge_label', $offer->badge_label) }}"
           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" name="start_date" value="{{ old('start_date', optional($offer->start_date)->format('Y-m-d')) }}"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Expiry Date &amp; Time</label>
        <input type="datetime-local" name="expiry_date" value="{{ old('expiry_date', optional($offer->expiry_date)->format('Y-m-d\TH:i')) }}"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
</div>

@if ($offer->exists)
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Usage Counter</label>
        <input type="number" min="0" name="clicks" value="{{ old('clicks', $offer->clicks) }}"
               class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <p class="mt-1 text-xs text-gray-400">Increments automatically on shopper clicks — editable here if you need to correct it.</p>
    </div>
@endif

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Terms &amp; Conditions</label>
    <textarea name="terms" rows="2"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('terms', $offer->terms) }}</textarea>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Promotion Badges (max 2)</label>
    <div data-max-badges="2" class="flex flex-wrap gap-4">
        @foreach ($badges as $badge)
            <label class="flex items-center gap-2">
                <input type="checkbox" name="badge_ids[]" value="{{ $badge->id }}" data-badge-checkbox
                       @checked(in_array($badge->id, $selectedBadgeIds))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">{{ $badge->name }}</span>
            </label>
        @endforeach
    </div>
</div>

<label class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1"
           @checked(old('is_active', $offer->id ? $offer->is_active : true))
           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
    <span class="text-sm text-gray-700">Published</span>
</label>

