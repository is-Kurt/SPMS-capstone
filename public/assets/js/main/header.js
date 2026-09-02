document.addEventListener('DOMContentLoaded', function () {
    // State management
    let mobileNavMenuOpen = false;
    let mobileProfileMenuOpen = false; // Added state for mobile profile
    let profileDropdownOpen = false;

    // DOM elements
    const mobileNavBtn = document.querySelector('.mobile-nav-btn');
    const mobileProfileBtn = document.querySelector('.mobile-profile-btn');
    const mobileNavMenu = document.querySelector('.mobile-nav-menu');
    const mobileProfileMenu = document.querySelector('.mobile-profile-menu');
    const profileBtn = document.querySelector('.profile-btn');
    const profileDropdown = document.querySelector('.profile-dropdown');

    // Toggle mobile nav menu
    if (mobileNavBtn && mobileNavMenu) {
        mobileNavBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            mobileNavMenuOpen = !mobileNavMenuOpen;
            
            // Close profile menu if it's open
            if (mobileNavMenuOpen) {
                mobileProfileMenuOpen = false;
                mobileProfileMenu?.classList.add('hidden');
            }

            mobileNavMenu.classList.toggle('hidden', !mobileNavMenuOpen);
            
            // Toggle icons
            document.querySelector('.menu-icon')?.classList.toggle('hidden', mobileNavMenuOpen);
            document.querySelector('.close-icon')?.classList.toggle('hidden', !mobileNavMenuOpen);
        });
    }

    // Toggle mobile profile menu
    if (mobileProfileBtn && mobileProfileMenu) {
        mobileProfileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            mobileProfileMenuOpen = !mobileProfileMenuOpen;

            // Close nav menu if it's open
            if (mobileProfileMenuOpen) {
                mobileNavMenuOpen = false;
                mobileNavMenu?.classList.add('hidden');
                document.querySelector('.menu-icon')?.classList.remove('hidden');
                document.querySelector('.close-icon')?.classList.add('hidden');
            }

            mobileProfileMenu.classList.toggle('hidden', !mobileProfileMenuOpen);
        });
    }

    // Toggle desktop profile dropdown
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdownOpen = !profileDropdownOpen;
            profileDropdown.classList.toggle('hidden', !profileDropdownOpen);
        });
    }

    // Close everything when clicking outside
    document.addEventListener('click', function () {
        // Desktop Close
        profileDropdownOpen = false;
        profileDropdown?.classList.add('hidden');

        // Mobile Nav Close
        mobileNavMenuOpen = false;
        mobileNavMenu?.classList.add('hidden');
        document.querySelector('.menu-icon')?.classList.remove('hidden');
        document.querySelector('.close-icon')?.classList.add('hidden');

        // Mobile Profile Close
        mobileProfileMenuOpen = false;
        mobileProfileMenu?.classList.add('hidden');
    });
});

document.addEventListener('click', (e) => {
    // 1. Check if the clicked element is one of our nav links
    const navLink = e.target.closest('.nav-link');
    if (!navLink) return;

    // 2. Prevent the actual page refresh
    e.preventDefault();

    const url = navLink.href;
    const uri = navLink.getAttribute('data-uri');

    // 3. Update the URL in the address bar without reloading
    window.history.pushState({ path: url }, '', url);

    // 4. Update the Active UI state (CSS)
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('bg-black/15', 'shadow-inner');
        link.classList.add('hover:bg-black/10');
    });
    navLink.classList.add('bg-black/15', 'shadow-inner');
    navLink.classList.remove('hover:bg-black/10');

    // 5. Trigger your global UI sync event
    // This tells your document table and sidebar to fetch new data
    document.dispatchEvent(new CustomEvent('sync-ui', { 
        detail: { filter: uri || 'all' } 
    }));
});

// 6. Handle the browser "Back" and "Forward" buttons
window.addEventListener('popstate', () => {
    // When the user hits back, trigger the refresh again based on the previous URL
    document.dispatchEvent(new CustomEvent('sync-ui'));
});

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