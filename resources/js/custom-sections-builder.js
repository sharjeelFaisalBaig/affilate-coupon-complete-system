// Repeatable, titled rich-text section builder for the Store form's Row 9
// ("Admin-managed rich text sections" per the SRS). Each row gets its own
// Quill instance, initialized on demand via blog-editor.js's exposed
// window.initQuillEditors() since it doesn't exist in the DOM until added.

function initCustomSectionsBuilder() {
    const container = document.querySelector('[data-custom-sections-list]');
    const addButton = document.querySelector('[data-custom-section-add]');
    if (!container || !addButton) return;

    let index = Number(container.getAttribute('data-custom-sections-next-index') || '0');

    addButton.addEventListener('click', () => {
        const key = `custom_section_${index}`;
        const row = document.createElement('div');
        row.className = 'space-y-2 rounded-md border border-gray-200 p-3';
        row.innerHTML = `
            <div class="flex items-start gap-2">
                <input type="text" name="custom_sections[${index}][title]" placeholder="Section Title"
                       class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <button type="button" data-custom-section-remove class="shrink-0 text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
            </div>
            <div data-quill-editor="${key}" style="min-height: 120px;" class="bg-white"></div>
            <textarea name="custom_sections[${index}][content]" data-content-field="${key}" class="hidden"></textarea>
        `;
        container.appendChild(row);
        index += 1;

        if (window.initQuillEditors) window.initQuillEditors();
    });

    container.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-custom-section-remove]');
        if (removeButton) {
            removeButton.closest('div.space-y-2').remove();
        }
    });
}

document.addEventListener('DOMContentLoaded', initCustomSectionsBuilder);
