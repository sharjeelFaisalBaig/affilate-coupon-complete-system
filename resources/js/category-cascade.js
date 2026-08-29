// Store/Brand category cascade filter (up to 4 levels), shared by the admin
// Offers listing and the public Promo Codes / Stores Listing pages, per the
// SRS: selecting a category reveals a "Select sub category" dropdown below
// it if that category has children; a lone child auto-selects itself; every
// level always offers an "All" option. The deepest non-"All" selection is
// written into a hidden input (`data-cascade-value`) that filter forms read
// like any other field.

function buildCascade(container) {
    const paramLabel = container.dataset.cascadeLabel || 'Subcategory';
    const dataEl = container.querySelector('[data-cascade-data]');
    const tree = JSON.parse(dataEl.textContent);
    const hiddenInput = container.querySelector('[data-cascade-value]');
    const selectsWrap = container.querySelector('[data-cascade-selects]');

    const byParent = {};
    const byId = {};
    tree.forEach((cat) => {
        const key = cat.parent_id ? String(cat.parent_id) : 'root';
        byParent[key] = byParent[key] || [];
        byParent[key].push(cat);
        byId[String(cat.id)] = cat;
    });

    function buildSelect(level, parentKey, selectedId) {
        const options = byParent[parentKey];
        if (!options || !options.length) return null;

        const select = document.createElement('select');
        select.dataset.cascadeLevel = String(level);
        select.className = 'block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';

        const allOpt = document.createElement('option');
        allOpt.value = '';
        allOpt.textContent = level === 1 ? 'All Categories' : `All ${paramLabel}`;
        select.appendChild(allOpt);

        options.forEach((cat) => {
            const opt = document.createElement('option');
            opt.value = String(cat.id);
            opt.textContent = cat.name;
            if (selectedId && String(selectedId) === String(cat.id)) opt.selected = true;
            select.appendChild(opt);
        });

        select.addEventListener('change', () => onLevelChange(level));
        return select;
    }

    function clearLevelsAfter(level) {
        Array.from(selectsWrap.querySelectorAll('[data-cascade-level]')).forEach((el) => {
            if (Number(el.dataset.cascadeLevel) > level) el.remove();
        });
    }

    function updateHiddenValue() {
        const selects = Array.from(selectsWrap.querySelectorAll('[data-cascade-level]'));
        let value = '';
        selects.forEach((s) => {
            if (s.value) value = s.value;
        });
        if (hiddenInput.value !== value) {
            hiddenInput.value = value;
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function appendNextLevelIfAny(level, selectedId) {
        const children = byParent[String(selectedId)];
        if (!children || !children.length) return;

        const select = buildSelect(level, String(selectedId), null);
        if (!select) return;
        selectsWrap.appendChild(select);

        if (children.length === 1) {
            select.value = String(children[0].id);
            onLevelChange(level);
        }
    }

    function onLevelChange(level) {
        clearLevelsAfter(level);
        const select = selectsWrap.querySelector(`[data-cascade-level="${level}"]`);
        const selectedId = select ? select.value : '';

        updateHiddenValue();

        if (selectedId) {
            appendNextLevelIfAny(level + 1, selectedId);
        }
    }

    // Restore initial state from the hidden input's starting value (e.g. a
    // filtered URL was loaded directly) by walking up the parent chain.
    const initialId = hiddenInput.value || null;
    const chain = [];
    let walker = initialId ? byId[String(initialId)] : null;
    while (walker) {
        chain.unshift(walker);
        walker = walker.parent_id ? byId[String(walker.parent_id)] : null;
    }

    const rootSelect = buildSelect(1, 'root', chain[0]?.id);
    if (rootSelect) {
        selectsWrap.appendChild(rootSelect);
        for (let i = 0; i < chain.length; i++) {
            const nextLevel = i + 2;
            const select = buildSelect(nextLevel, String(chain[i].id), chain[i + 1]?.id);
            if (!select) break;
            selectsWrap.appendChild(select);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-category-cascade]').forEach(buildCascade);
});
