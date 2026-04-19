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