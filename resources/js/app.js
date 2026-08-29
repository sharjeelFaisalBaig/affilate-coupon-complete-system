// Generic vanilla-JS UI helpers shared across admin and public pages.
// No framework (no Alpine/jQuery/React/Vue) — plain DOM APIs only.

function qs(selector, scope = document) {
    return scope.querySelector(selector);
}

function qsa(selector, scope = document) {
    return Array.from(scope.querySelectorAll(selector));
}

// [data-toggle="#target-id"] click toggles the `.hidden` class on the target,
// and closes when clicking outside of both the trigger and the target itself
// (not some ancestor wrapper — hiding the wrong element was what made the
// admin user-menu vanish permanently after any click elsewhere on the page).
function initToggles() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-toggle]');

        if (trigger) {
            const target = document.querySelector(trigger.getAttribute('data-toggle'));
            if (target) {
                target.classList.toggle('hidden');
                event.stopPropagation();
            }
            return;
        }

        qsa('[data-toggle]').forEach((triggerEl) => {
            const target = document.querySelector(triggerEl.getAttribute('data-toggle'));
            if (
                target &&
                !target.classList.contains('hidden') &&
                !target.contains(event.target) &&
                !triggerEl.contains(event.target)
            ) {
                target.classList.add('hidden');
            }
        });
    });
}

// [data-modal-open="#modal-id"] / [data-modal-close] open and close a modal overlay.
function initModals() {
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');
        if (opener) {
            const modal = document.querySelector(opener.getAttribute('data-modal-open'));
            if (modal) modal.classList.remove('hidden');
            return;
        }

        const closer = event.target.closest('[data-modal-close]');
        if (closer) {
            const modal = closer.closest('[data-modal]');
            if (modal) modal.classList.add('hidden');
        }
    });
}

// [data-coupon-cta][data-redirect-url][data-offer-id] click behavior:
//   1. The CURRENT tab navigates to the merchant via our tracked redirect
//      (same-tab, so the click counter increments before the shopper leaves).
//   2. A NEW tab opens pointing back at our own page with a `revealOffer`
//      query flag — the new tab gets browser focus (standard behavior for
//      window.open from a user gesture) and auto-shows the code modal via
//      initRevealFromQueryString() below, so the shopper lands looking at
//      the code instead of having to switch tabs to find it.
function initCouponCta() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-coupon-cta]');
        if (!button) return;

        const redirectUrl = button.getAttribute('data-redirect-url');
        const offerId = button.getAttribute('data-offer-id');

        if (offerId) {
            const revealUrl = new URL(window.location.href);
            revealUrl.searchParams.set('revealOffer', offerId);
            window.open(revealUrl.toString(), '_blank');
        }

        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    });
}

// On load, if the URL carries ?revealOffer=ID (see initCouponCta above),
// open that offer's modal immediately without requiring another click.
function initRevealFromQueryString() {
    const offerId = new URLSearchParams(window.location.search).get('revealOffer');
    if (!offerId) return;

    const modal = document.querySelector(`#offer-modal-${offerId}`);
    if (modal) modal.classList.remove('hidden');
}

// [data-copy="CODE_TEXT"] copies text to clipboard and flips its label briefly.
function initCopyButtons() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');
        if (!button) return;

        const text = button.getAttribute('data-copy');
        try {
            await navigator.clipboard.writeText(text);
        } catch {
            const helper = document.createElement('textarea');
            helper.value = text;
            document.body.appendChild(helper);
            helper.select();
            document.execCommand('copy');
            helper.remove();
        }

        const original = button.getAttribute('data-copy-label') || button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => {
            button.textContent = original;
        }, 1500);
    });
}

// [data-tabs] wraps [data-tab-trigger="key"] buttons and [data-tab-panel="key"] panels.
function initTabs() {
    qsa('[data-tabs]').forEach((wrapper) => {
        const triggers = qsa('[data-tab-trigger]', wrapper);
        const panels = qsa('[data-tab-panel]', wrapper);

        const activate = (key) => {
            triggers.forEach((t) => t.classList.toggle('is-active', t.getAttribute('data-tab-trigger') === key));
            // Inline style avoids relying on Tailwind's utility cascade order
            // when a panel also carries a static `grid`/`flex` display class.
            panels.forEach((p) => {
                p.style.display = p.getAttribute('data-tab-panel') === key ? '' : 'none';
            });
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => activate(trigger.getAttribute('data-tab-trigger')));
        });

        if (triggers.length) activate(triggers[0].getAttribute('data-tab-trigger'));
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initToggles();
    initModals();
    initCouponCta();
    initRevealFromQueryString();
    initCopyButtons();
    initTabs();
});
