/**
 * Navigation & Back-Button Security Guards
 * 
 * 1. BFCache Invalidation: Ensures that when a user clicks the browser Back/Forward
 *    buttons, the page is re-verified against the server to prevent post-logout
 *    confidentiality leaks and stale/outdated form state.
 * 2. Unsaved Work Protection: Prompts the user before leaving if there are unsaved edits.
 * 3. Double-Click & Spam Prevention: Throttles duplicate button submissions.
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. BACK / FORWARD CACHE (BFCACHE) REVALIDATION
    // =========================================================================
    window.addEventListener('pageshow', function (event) {
        // If the page was restored from browser in-memory snapshot (BFCache)
        // or navigated via back_forward history traversal:
        const navEntries = (window.performance && window.performance.getEntriesByType)
            ? window.performance.getEntriesByType('navigation')
            : [];
        const isBackForward = navEntries.length > 0 && navEntries[0].type === 'back_forward';

        if (event.persisted || isBackForward) {
            // Force a fresh request from the server to verify session validity
            // and fetch updated document statuses.
            window.location.reload();
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
            btn.classList.add('opacity-70', 'cursor-not-allowed');
        });

        // Fail-safe auto reset after 4 seconds in case of client-side validation errors
        setTimeout(() => {
            form.removeAttribute('data-submitting');
            submitButtons.forEach(btn => {
                btn.removeAttribute('disabled');
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
            });
        }, 4000);
    });

    // =========================================================================
    // 3. ACTION BUTTON CLICK DEBOUNCING (Prevents rapid multi-clicks)
    // =========================================================================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button[type="submit"], button.btn-action, .btn-debounce');
        if (!btn) return;

        if (btn.hasAttribute('data-click-locked')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        // Lock button for 500ms to ignore rapid spam clicks
        btn.setAttribute('data-click-locked', 'true');
        setTimeout(() => {
            btn.removeAttribute('data-click-locked');
        }, 500);
    }, true);

    // =========================================================================
    // 4. BULLETPROOF WORKSPACE BACK-BUTTON TRAP
    // =========================================================================
    // Traps the user inside the authenticated workspace as their session origin.
    // Creates a multi-level state buffer so rapid double-clicking or spamming
    // the browser Back button cannot break out to landing or login.
    const path = window.location.pathname;
    const isAuthWorkspace = path.startsWith('/folders') || 
                            path.startsWith('/ratings') || 
                            path.startsWith('/teams') || 
                            path.startsWith('/accounts') ||
                            path.startsWith('/profile');

    if (isAuthWorkspace && window.history && window.history.pushState) {
        const trapToCurrent = () => {
            window.history.pushState({ spmsRoot: true }, document.title, window.location.href);
        };

        // Create buffer of 5 history layers on arrival
        if (!window.history.state || !window.history.state.spmsRoot) {
            for (let i = 0; i < 5; i++) {
                trapToCurrent();
            }
        }

        // Whenever any popstate occurs (single click, double click, spam), immediately replenish
        window.addEventListener('popstate', function () {
            trapToCurrent();
        });
    }

})();
