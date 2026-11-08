/**
 * Full text on hover for text that is cut off (2026-11-08). Fields and labels keep a fixed size and end long text
 * with "…" (Tailwind truncate / line-clamp, or CSS text-overflow: ellipsis); hovering one that is actually cut shows
 * the whole text as a tooltip. A title the page set itself is left alone. One listener for the whole app, so text
 * that is added or changed later (lists, dropdowns, rows refreshed by script) is covered too.
 */
(function () {
    const CUT = '.truncate, [class*="line-clamp-"]';
    function candidate(target) {
        if (!(target instanceof Element)) return null;
        const el = target.closest(CUT);
        if (el) return el;
        return getComputedStyle(target).textOverflow === 'ellipsis' ? target : null;
    }
    function isCut(el) {
        return el.scrollWidth > el.clientWidth + 1 || el.scrollHeight > el.clientHeight + 1;
    }
    document.addEventListener('mouseover', e => {
        const el = candidate(e.target);
        if (!el) return;
        if (el.hasAttribute('title') && !el.dataset.autoTitle) return;   // the page's own title wins
        const text = (el.textContent || '').replace(/\s+/g, ' ').trim();   // as written (not CSS upper-case)
        if (text && isCut(el)) {
            if (el.title !== text) el.title = text;
            el.dataset.autoTitle = '1';
        } else if (el.dataset.autoTitle) {                                // no longer cut: no tooltip
            el.removeAttribute('title');
            delete el.dataset.autoTitle;
        }
    }, { passive: true });
})();
