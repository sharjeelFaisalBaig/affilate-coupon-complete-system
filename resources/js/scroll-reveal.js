/**
 * Fades/slides [data-reveal] elements in as they enter the viewport. Falls
 * back to immediately marking everything visible if IntersectionObserver
 * isn't available, so content is never permanently hidden.
 *
 * A [data-reveal-group] ancestor (e.g. a card grid) staggers its direct
 * [data-reveal] children — each gets an increasing transition-delay so a
 * whole row/grid cascades in rather than popping together, capped so a long
 * grid doesn't leave late cards waiting several seconds.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        Array.from(group.querySelectorAll(':scope > [data-reveal]')).forEach((el, index) => {
            el.style.transitionDelay = `${Math.min(index * 70, 420)}ms`;
        });
    });

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
