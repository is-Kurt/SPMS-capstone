/**
 * Navigation & Form Interaction Guards
 * 
 * 1. Form Double-Submission & Spam-Click Prevention: Prevents rapid duplicate
 *    POST/submit actions from creating duplicate records or requests.
 * 2. Action Button Click Debouncing: Throttles rapid button clicks.
 * 3. Unsaved Changes Guard: Warns the user if they try to leave or close the tab
 *    while document edits are unsaved (AppState.isDirty).
 * 4. Smooth BFCache Preservation: Lets browser Back/Forward navigation restore
 *    pages instantaneously from memory cache without forcing a cold reload.
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. UNSAVED WORK PROTECTION (Standard graceful prompt)
    // =========================================================================
    window.addEventListener('beforeunload', function (e) {
        if (window.AppState && (window.AppState.isDirty || window.AppState.dirty)) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });

    // =========================================================================
    // 2. FORM DOUBLE-SUBMISSION & SPAM-CLICK PREVENTION
    // =========================================================================
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        // Skip if submission was already prevented
        if (e.defaultPrevented) return;

        // If form is already actively submitting, block duplicate trigger
        if (form.getAttribute('data-submitting') === 'true') {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        form.setAttribute('data-submitting', 'true');

        // Provide visual feedback and temporarily disable submit buttons
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(btn => {
            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        });

        // Fail-safe auto reset after 4 seconds in case of client-side validation errors
        setTimeout(() => {
            form.removeAttribute('data-submitting');
            submitButtons.forEach(btn => {
                btn.removeAttribute('disabled');
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            });
        }, 4000);
    });

    // =========================================================================
    // 3. ACTION BUTTON CLICK DEBOUNCING (Prevents rapid multi-clicks on mutations)
    // =========================================================================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button[type="submit"], button.btn-action, .btn-debounce');
        if (!btn) return;

        if (btn.hasAttribute('data-click-locked')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        // Lock button for 400ms to ignore rapid spam clicks and provide active visual feedback
        btn.setAttribute('data-click-locked', 'true');
        btn.classList.add('opacity-75');
        setTimeout(() => {
            btn.removeAttribute('data-click-locked');
            btn.classList.remove('opacity-75');
        }, 400);
    }, true);

    // =========================================================================
    // IMMEDIATE BUTTON TACTILE FEEDBACK (Buttons never feel dead after clicking)
    // =========================================================================
    document.addEventListener('pointerdown', function (e) {
        const btn = e.target.closest('button, [role="button"], .btn, .tab-btn, .tab-btn-doc, input[type="submit"], input[type="button"]');
        if (!btn || btn.disabled) return;
        btn.classList.add('btn-clicked-feedback');
    }, { passive: true });

    const removeBtnFeedback = () => {
        document.querySelectorAll('.btn-clicked-feedback').forEach(b => {
            b.classList.remove('btn-clicked-feedback');
        });
    };

    document.addEventListener('pointerup', removeBtnFeedback, { passive: true });
    document.addEventListener('pointercancel', removeBtnFeedback, { passive: true });

    // =========================================================================
    // 4. INSTANT NAVIGATION VISUAL RESPONSE & SUBTLE TRANSITION
    // =========================================================================
    document.addEventListener('click', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (e.defaultPrevented) return;

        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        if (link.getAttribute('target') === '_blank') return;
        if (link.hasAttribute('download')) return;

        try {
            const targetUrl = new URL(href, window.location.href);
            // Same origin only
            if (targetUrl.origin !== window.location.origin) return;
            // Ignore if clicking the exact current URL (including search params)
            if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search) return;

            // 1. Immediate Visual Response on Clicked Link (0ms)
            link.classList.add('opacity-80', 'scale-[0.98]');

            // Special handling for Header Navigation Pills: instant sliding indicator
            if (link.classList.contains('header-nav-link')) {
                if (typeof window.moveHeaderNavIndicator === 'function') {
                    window.moveHeaderNavIndicator(link, true);
                }
            }

            // Special handling for Sidebar Folder Items: instant active outline & indicator
            if (link.classList.contains('sidebar-folder-item')) {
                document.querySelectorAll('.sidebar-folder-item').forEach(item => {
                    item.classList.remove('bg-white', 'dark:bg-zinc-800', 'text-zinc-900', 'dark:text-white', 'border', 'border-zinc-200', 'dark:border-zinc-700/60', 'shadow-xs');
                    item.classList.add('text-zinc-600', 'dark:text-zinc-400', 'hover:text-zinc-900', 'dark:hover:text-white', 'hover:bg-zinc-100', 'dark:hover:bg-zinc-800/40');
                    const indicator = item.querySelector('.bg-amber-500');
                    if (indicator) indicator.remove();
                });
                link.classList.remove('text-zinc-600', 'dark:text-zinc-400', 'hover:text-zinc-900', 'dark:hover:text-white', 'hover:bg-zinc-100', 'dark:hover:bg-zinc-800/40');
                link.classList.add('bg-white', 'dark:bg-zinc-800', 'text-zinc-900', 'dark:text-white', 'border', 'border-zinc-200', 'dark:border-zinc-700/60', 'shadow-xs');
                if (!link.querySelector('.bg-amber-500')) {
                    const ind = document.createElement('span');
                    ind.className = 'absolute left-0 inset-y-2.5 w-1 bg-amber-500 rounded-r-full';
                    link.prepend(ind);
                }
            }

            // 2. Immediate Subtle Transition on Main Content (0ms)
            const contentContainers = document.querySelectorAll('.spms-content-slide');
            contentContainers.forEach(container => {
                container.classList.add('spms-nav-transitioning');
            });

            // 3. Safety fallback to reset transition state if navigation is aborted or takes too long
            setTimeout(() => {
                contentContainers.forEach(c => c.classList.remove('spms-nav-transitioning'));
                link.classList.remove('opacity-80', 'scale-[0.98]');
            }, 3500);

        } catch (err) {}
    });

    // Reset transitioning state on BFCache restore (Back/Forward)
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('.spms-content-slide').forEach(c => {
            c.classList.remove('spms-nav-transitioning');
        });
    });

})();
