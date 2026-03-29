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