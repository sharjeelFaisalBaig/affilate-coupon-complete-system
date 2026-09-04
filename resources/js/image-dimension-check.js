/**
 * Real-time client-side image dimension validation: on a file input carrying
 * data-required-width/data-required-height, reads the chosen file's actual
 * pixel dimensions before submit and shows a pass/fail message plus the
 * uploaded dimensions, so the admin doesn't have to submit the form to find
 * out a logo/image is the wrong size. The server-side `dimensions:` rule
 * remains the authoritative guard — this is a helpful preview only.
 */
function initImageDimensionCheck() {
    document.querySelectorAll('input[type="file"][data-required-width]').forEach((input) => {
        const result = input.closest('div')?.querySelector('[data-dimension-check-result]');
        if (!result) return;

        const requiredWidth = parseInt(input.dataset.requiredWidth, 10);
        const requiredHeight = parseInt(input.dataset.requiredHeight, 10);

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                result.textContent = '';
                return;
            }

            const url = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => {
                const { naturalWidth: width, naturalHeight: height } = img;
                URL.revokeObjectURL(url);

                if (width === requiredWidth && height === requiredHeight) {
                    result.textContent = `Uploaded: ${width}×${height}px — matches the required size.`;
                    result.className = 'mt-1 text-xs text-emerald-600';
                } else {
                    result.textContent = `Uploaded: ${width}×${height}px — required exactly ${requiredWidth}×${requiredHeight}px.`;
                    result.className = 'mt-1 text-xs text-red-600';
                }
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                result.textContent = '';
            };

            img.src = url;
        });
    });
}

document.addEventListener('DOMContentLoaded', initImageDimensionCheck);
