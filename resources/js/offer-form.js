// Toggles the coupon "code" field based on offer_type, for every
// offer form on the page (the create page has one; the slide-over
// drawers on the index page can have many).

// Maps a Promotion Type's discount_format (the real, admin-managed
// taxonomy) onto the legacy discount_type enum every other part of the
// app (Offer::displayLabelFor, sorting, seeders) still reads from, so
// picking a Promotion Type is now the single source of truth in the UI.
const DISCOUNT_FORMAT_TO_TYPE = {
    percentage: 'percentage',
    flat: 'flat',
    deal: 'other',
    custom_text: 'other',
};

function syncOfferForm(form) {
    const typeSelect = form.querySelector('[data-offer-type]');
    const codeWrapper = form.querySelector('[data-code-wrapper]');
    const promotionTypeSelect = form.querySelector('[data-promotion-type]');
    const discountTypeHidden = form.querySelector('[data-discount-type-hidden]');
    const discountValueWrapper = form.querySelector('[data-discount-value-wrapper]');
    const discountValueInput = form.querySelector('[data-discount-value-input]');
    const discountLabel = form.querySelector('[data-discount-label]');
    const badgeLabelHint = form.querySelector('[data-badge-label-required-hint]');
    const urlLabel = form.querySelector('[data-destination-url-label]');
    const urlHint = form.querySelector('[data-destination-url-hint]');

    if (typeSelect && codeWrapper) {
        const sync = () => {
            const isCoupon = typeSelect.value === 'coupon';
            codeWrapper.classList.toggle('hidden', !isCoupon);

            if (urlLabel) urlLabel.textContent = isCoupon ? 'Destination URL (merchant page)' : 'Destination URL (deal page)';
            if (urlHint) {
                urlHint.textContent = isCoupon
                    ? 'Opened in a new tab when the shopper clicks "Show Promo Code" — the code modal stays open in this tab.'
                    : 'Where the shopper is sent when they click "View Deal".';
            }
        };
        typeSelect.addEventListener('change', sync);
        sync();
    }

    if (promotionTypeSelect && discountTypeHidden) {
        const sync = () => {
            const selectedOption = promotionTypeSelect.selectedOptions[0];
            const format = selectedOption?.getAttribute('data-discount-format');
            if (!format) return;

            discountTypeHidden.value = DISCOUNT_FORMAT_TO_TYPE[format] ?? 'other';

            const isTextOnly = format === 'deal' || format === 'custom_text';
            if (discountValueWrapper) discountValueWrapper.classList.toggle('hidden', isTextOnly);
            if (discountValueInput) discountValueInput.required = !isTextOnly;
            if (badgeLabelHint) badgeLabelHint.classList.toggle('hidden', !isTextOnly);

            if (discountLabel) {
                discountLabel.textContent = format === 'percentage' ? 'Discount Value (%)' : 'Discount Value (USD $)';
            }
        };
        promotionTypeSelect.addEventListener('change', sync);
        sync();
    }

    const badgeGroup = form.querySelector('[data-max-badges]');
    if (badgeGroup) {
        const max = Number(badgeGroup.getAttribute('data-max-badges'));
        const checkboxes = Array.from(badgeGroup.querySelectorAll('[data-badge-checkbox]'));

        const syncBadgeLimit = () => {
            const checkedCount = checkboxes.filter((c) => c.checked).length;
            checkboxes.forEach((c) => {
                if (!c.checked) c.disabled = checkedCount >= max;
            });
        };

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', syncBadgeLimit));
        syncBadgeLimit();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-offer-form]').forEach(syncOfferForm);
});
