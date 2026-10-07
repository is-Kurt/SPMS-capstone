# Changelog

### Oct 8, 2026
- **[Added]** Evidence file encryption
  - Encrypted all uploaded evidence photos and files on disk so they can't be opened directly.
  - Automatically decrypts when viewed on the website.
- **[Removed]** Login page clutter
  - Cleaned up the extra boxes and long privacy text from the login page.
  - Removed the "Welcome back" popup after logging in.
- **[Changed]** Loading screen transition
  - Replaced the loading screen logo with the official BSU seal and a spinning ring.
  - Removed artificial wait times so it only shows while actually loading.
- **[Fixed]** Duplicate Return button
  - Removed the extra Return button on the right side of the document page.
- **[Added]** Sign-in animation & notifications
  - Added smooth entrance animations on login.
  - Added loading spinner to the login button.
  - Moved sign-out alerts into the floating top-right notification system.
- **[Changed]** Mobile navigation & theme cleanup
  - Converted mobile folder menu into a slide-out drawer.
  - Cleaned up mobile lifecycle view so it doesn't swipe sideways.
  - Replaced legacy green backgrounds with charcoal/zinc and gold palette.

---

## Past Updates

### Oct 5, 2026
- **[Added]** Mobile Audit Trail
  - Replaced the wide table with mobile cards so no text gets cut off.
  - Shows user avatar, name, role, email, action badges, and device platform (`::1 (Win10)`).
  - Rearranged top counters into 2 clean rows on mobile screens.
  - Added quick category filter buttons and a collapsible date filter drawer.
  - Added a one-tap CSV export button next to the page title.
- **[Added]** Dark and Light Mode
  - Automatically matches device settings (uses your phone/PC default).
  - Added sun/moon theme switch to the document workspace (OPCR/DPCR/IPCR).
  - Added theme switch to all login and signup pages.
  - Fixed dashboard icons and badges so they use soft colors instead of dark boxes in light mode.
  - Removed harsh borders and shiny badge from login boxes.
- **[Changed]** Mobile UI phase 2
  - Added mobile notifications page.
  - Added mobile folder dropdown menu.
  - Improved dashboard layout for phone screens.
  - Added notification tests.

### Oct 3, 2026
- **[Fixed]** Design fix & mobile adjustments
  - First round of mobile screen adjustments.
  - Fixed light mode colors.
  - Updated security packages.

### Oct 1, 2026
- **[Removed]** Remove node_modules from repo tracking
  - Updated gitignore to stop tracking node modules.
- **[Added]** TWG assignment
  - Added option to assign Technical Working Group (TWG) members.

### Sep 26, 2026
- **[Changed]** Merging local
  - Merged local UI changes.
- **[Fixed]** Off-positioned buttons
  - Fixed misaligned buttons across pages.
- **[Fixed]** Resolve merge conflicts and update libraries
  - Fixed merge conflicts and updated text editor libraries.
- **[Fixed]** Late Furnishings 4.0
  - Fixed form calculations and small layout bugs.

### Sep 25, 2026
- **[Changed]** Furnishings 4.0
  - Polished form layouts and table displays.

### Sep 24, 2026
- **[Added]** Cloudflare Turnstile protection
  - Added Cloudflare verification to login page to block spam.(Kurt)

### Sep 19, 2026
- **[Added]** Furnishings 3.0
  - Added Excel export for official CSC forms.
  - Added CSC rubric guide drawer.
  - Added automated background checker for deadlines.

### Sep 15, 2026
- **[Added]** Furnishings 2.0
  - Added approval routing presets.
  - Improved folder editing popups.

### Sep 13, 2026
- **[Added]** Furnishings
  - Added departments and campus units.
  - Updated user directory and invitations tabs.
  - Polished ratings and team management pages.

### Sep 10, 2026
- **[Changed]** Admin ratings UI improvement
  - Cleaned up the ratings page for admins.
- **[Added]** Design rework and adding user manual
  - Added user manual document.
  - Updated dashboard design.
- **[Added]** Activity log tracking
  - Added activity log table to track user actions.

### Sep 8, 2026
- **[Fixed]** Dashboard fix to each account
  - Adjusted dashboard view for each user role.

### Sep 5, 2026
- **[Added]** Notification function
  - Added notifications and bell icon counter.
- **[Fixed]** Minor fix
  - Fixed button clicks and layout spacing.
- **[Added]** Feature report
  - Added feature report file.
- **[Fixed]** Dashboard fix
  - Fixed card numbers on dashboard.
- **[Added]** Admin dashboard trial
  - Tested admin dashboard layout.
- **[Changed]** Logo updates
  - Updated BSU and SPMS logos and seal.
- **[Fixed]** Double check
  - Checked page access and permissions.
- **[Added]** AR report and pagination
  - Added report file and table page numbers.
- **[Added]** Mobile display and header requirements
  - Added official header and mobile navigation bar.

### Sep 4, 2026
- **[Added]** Auto naming of forms
  - Forms automatically name themselves based on user and semester.
- **[Added]** Rubrics matrix
  - Added 5-point CSC rating rubric.
- **[Fixed]** Form update and minor security fixes
  - Fixed form fields and security checks.
- **[Changed]** System overhaul
  - Redesigned form editor and rating calculations.

### Sep 3, 2026
- **[Changed]** Success indicators active during target setting
  - Allowed editing success indicators while setting targets.
- **[Changed]** BSU green color theme
  - Changed system colors to BSU green.
- **[Fixed]** Button fix
  - Fixed button styles and hover colors.
- **[Added]** Initial mock setup
  - Initial project setup.
