// SPMS Header & Table Utilities

// 7. Keep frozen table headers (data-frozen-header) pixel-aligned with their
// scrollable body (data-frozen-body) by matching the body's real scrollbar width.
// A ResizeObserver on the body catches window resizes, tab switches, and
// filtered rows toggling the scrollbar on/off - no manual re-sync calls needed.
document.addEventListener('DOMContentLoaded', () => {
    const pairs = [];
    document.querySelectorAll('[data-frozen-body]').forEach(body => {
        const header = body.previousElementSibling;
        if (header && header.hasAttribute('data-frozen-header')) {
            pairs.push({ header, body });
        }
    });

    if (pairs.length === 0) return;

    const sync = () => {
        pairs.forEach(({ header, body }) => {
            header.style.paddingRight = (body.offsetWidth - body.clientWidth) + 'px';
        });
    };

    const ro = new ResizeObserver(sync);
    pairs.forEach(({ body }) => ro.observe(body));
    sync();
});

// 8. On localhost there's no real cron running the email-queue-draining Spark
// commands (UpdateStatuses/CheckFolderDeadlines), so this keeps the queue
// moving in the background on every authenticated page. Only runs in
// development (see the SPMS_ENV check below) - on a real server the cron
// commands are the sole drain, avoiding two triggers racing on the same rows.
// Also guarded to pages that render the header (i.e. an active session), since
// the endpoint requires one and would otherwise just fail quietly on login/signup.
function processBackgroundEmails() {
    const formData = new FormData();

    apiPost('/account/process-queue', formData, {
        onSuccess: (data) => {
            if (data.queue_state === 'working' && data.remaining > 0) {
                setTimeout(processBackgroundEmails, 2000);
            }
        },
        onError: () => {}
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Development only - on a real server the email queue is drained by cron
    // (spms:update-statuses/spms:check-folder-deadlines) instead of client polling.
    if (window.SPMS_ENV === 'development' && document.getElementById('profile-btn-mobile')) {
        processBackgroundEmails();
    }
});

// =========================================================================
// 9. SLIDING ACTIVE INDICATOR FOR TOP NAVIGATION
// =========================================================================
function initHeaderNavIndicator() {
    const container = document.getElementById('header-nav-container');
    const indicator = document.getElementById('header-nav-indicator');
    if (!container || !indicator) return;

    const links = Array.from(container.querySelectorAll('.header-nav-link'));
    if (links.length === 0) return;

    const activeLink = container.querySelector('.header-nav-active');

    function calculateRect(target) {
        return {
            left: target.offsetLeft,
            top: target.offsetTop,
            width: target.offsetWidth,
            height: target.offsetHeight
        };
    }

    function setIndicator(target, animate = true) {
        if (!target) return;
        const rect = calculateRect(target);
        if (rect.width === 0) return;

        if (animate) {
            indicator.style.transition = 'transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), width 0.24s cubic-bezier(0.16, 1, 0.3, 1), height 0.18s ease, opacity 0.15s ease';
        } else {
            indicator.style.transition = 'none';
        }

        indicator.style.transform = `translate3d(${rect.left}px, ${rect.top}px, 0)`;
        indicator.style.width = `${rect.width}px`;
        indicator.style.height = `${rect.height}px`;
        indicator.style.opacity = '1';
    }

    window.moveHeaderNavIndicator = function (target, animate = true) {
        if (!target) return;
        links.forEach(l => {
            l.classList.remove('header-nav-active', 'text-zinc-900', 'dark:text-white');
            l.classList.add('header-nav-inactive', 'text-zinc-500', 'dark:text-zinc-400');
        });
        target.classList.remove('header-nav-inactive', 'text-zinc-500', 'dark:text-zinc-400');
        target.classList.add('header-nav-active', 'text-zinc-900', 'dark:text-white');

        setIndicator(target, animate);

        try {
            const rect = calculateRect(target);
            sessionStorage.setItem('spms_last_nav_pill', JSON.stringify({
                uri: target.getAttribute('data-nav-uri') || '',
                left: rect.left,
                top: rect.top,
                width: rect.width,
                height: rect.height
            }));
        } catch {}
    };

    // Check if navigating from a different tab stored in sessionStorage
    let lastPill = null;
    try {
        const raw = sessionStorage.getItem('spms_last_nav_pill');
        if (raw) lastPill = JSON.parse(raw);
    } catch {}

    if (!activeLink) {
        // Outside the primary nav tabs (e.g., Notifications, Profile)
        // If coming from a nav tab, fade out the indicator gracefully from its last position
        if (lastPill && typeof lastPill.left === 'number') {
            indicator.style.transition = 'none';
            indicator.style.transform = `translate3d(${lastPill.left}px, ${lastPill.top}px, 0)`;
            indicator.style.width = `${lastPill.width}px`;
            indicator.style.height = `${lastPill.height}px`;
            indicator.style.opacity = '1';

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    indicator.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    indicator.style.opacity = '0';
                });
            });
        } else {
            indicator.style.opacity = '0';
        }

        try {
            sessionStorage.removeItem('spms_last_nav_pill');
        } catch {}

        links.forEach(link => {
            link.addEventListener('click', function () {
                window.moveHeaderNavIndicator(link, true);
            });
        });

        window.addEventListener('resize', () => {
            const current = container.querySelector('.header-nav-active');
            if (!current) {
                indicator.style.opacity = '0';
            } else {
                setIndicator(current, false);
            }
        }, { passive: true });

        return;
    }

    const currentUri = activeLink.getAttribute('data-nav-uri') || '';

    if (lastPill && lastPill.uri && lastPill.uri !== currentUri && typeof lastPill.left === 'number') {
        // Set pill at previous tab position with no transition
        indicator.style.transition = 'none';
        indicator.style.transform = `translate3d(${lastPill.left}px, ${lastPill.top}px, 0)`;
        indicator.style.width = `${lastPill.width}px`;
        indicator.style.height = `${lastPill.height}px`;
        indicator.style.opacity = '1';

        // Smoothly glide to the current active tab on next paint frame
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                setIndicator(activeLink, true);
            });
        });
    } else {
        // Render directly in place
        setIndicator(activeLink, false);
    }

    // Save current active tab position
    try {
        const rect = calculateRect(activeLink);
        sessionStorage.setItem('spms_last_nav_pill', JSON.stringify({
            uri: currentUri,
            left: rect.left,
            top: rect.top,
            width: rect.width,
            height: rect.height
        }));
    } catch {}

    // Attach click listener for instant sliding response
    links.forEach(link => {
        link.addEventListener('click', function () {
            window.moveHeaderNavIndicator(link, true);
        });
    });

    window.addEventListener('resize', () => {
        const current = container.querySelector('.header-nav-active') || activeLink;
        setIndicator(current, false);
    }, { passive: true });
}

document.addEventListener('DOMContentLoaded', initHeaderNavIndicator);