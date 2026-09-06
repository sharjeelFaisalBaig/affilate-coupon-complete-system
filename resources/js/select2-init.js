import $ from 'jquery';
import select2 from 'select2';

window.$ = window.jQuery = $;
select2(window, $);

/**
 * Initializes Select2 (with search built in) on every `<select data-select2-enable>`
 * — single or multiple, admin or public. New elements added later (e.g. an
 * AJAX-swapped filter results fragment) are picked up by re-running this on
 * DOMContentLoaded only; ajax-filters.js swaps don't currently inject new
 * selects, so a one-time init is sufficient.
 */
function initSelect2() {
    $('select[data-select2-enable]').each(function () {
        const $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) return;

        $el.select2({
            width: '100%',
            placeholder: $el.data('placeholder') || 'Select...',
            allowClear: !$el.prop('multiple') && !$el.prop('required'),
        });
    });
}

document.addEventListener('DOMContentLoaded', initSelect2);
