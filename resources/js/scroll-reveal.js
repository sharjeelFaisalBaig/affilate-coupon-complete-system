/**
 * Fades/slides [data-reveal] elements in as they enter the viewport. Falls
 * back to immediately marking everything visible if IntersectionObserver
 * isn't available, so content is never permanently hidden.
 */
document.addEventListener('DOMContentLoaded', () => {
    const targets = document.querySelectorAll('[data-reveal]');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );

    targets.forEach((el) => observer.observe(el));
});
