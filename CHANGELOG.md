# Changelog

- **[Added]** Dashboard Monitoring, Target Calibration & Top Performers
  - Added **Stage 2: Monitoring, Coaching & Target Calibration (Per-Office View)** table in Dashboard (both Admin and Supervisor views).
  - Displays deliverable unit headcount, target calibration compliance bars, coaching revision logs, final evaluation completion counts, average numerical ratings, and SPMS status badges.
  - Added interactive client-side search filter (`filterOfficeLeaderboard`) and unit drill-down.
  - Added **Top Performing Personnel Leaderboard / Honor Roster** showcasing top-rated faculty and staff with rank medals (🥇 Gold, 🥈 Silver, 🥉 Bronze), plantilla designations, teaching indicators, and direct document inspect links.

- **[Changed]** Core Functions Standardization & Blank Category Weight Default
  - Standardized all remaining "Core Mandate" references to "Core Functions" across `CscExcelExporter.php` and document view templates for complete CSC SPMS compliance.
  - Ensured category percentage weights start empty/blank by default, allowing users to input and calibrate custom category distributions that sum to 100%.

- **[Added]** PTWG Calibration Comments in Form Remarks
  - Renamed document matrix review comment headers from `TWG COMMENTS` to `PTWG COMMENTS` across DPCR, OPCR, IPERF, and IPCR matrices.
  - Added quick `+ [PTWG]` stamp button and calibrated comment inputs with explicit role tags.

- **[Added]** Google Drive & Cloud Storage Evidence Integration
  - Enhanced the MOV (Means of Verification) Evidence modal (`_mov_modal.php`) with direct support for Google Drive, Microsoft OneDrive, Dropbox, and cloud storage links.
  - Added automatic cloud provider badge detection, direct drive link opening, and embedded iframe preview with external fallback.


- **[Added]** Synchronized Multi-Signatories (Header "APPROVED BY" & Footer "Final Rating by")
  - Synchronized signatory blocks between the top header (`APPROVED BY:`) and the bottom footer (`Final Rating by:`).
  - Clicking **"+ Add Signatory"** (available at both the header and footer) dynamically appends matching signatory blocks to both sections.
  - Two-way real-time data synchronization: Typing the Name and Position of any signatory in either section immediately mirrors and updates across the form.
  - Removing a signatory block (`✕ Remove`) cleanly deletes that signatory from both the header and footer containers simultaneously.
  - Preserves distinct signing dates (Target Approval date vs. Final Evaluation date) for every signatory.


- **[Changed]** IPERF Blank Deliverable Table by Default
  - Updated IPERF (Contract of Service and Job Order Personnel) templates and document initial blueprints to start with completely empty deliverable rows instead of hardcoded sample clerical entries.
  - Owners/employees now start with a clean slate to type in their specific contract duties, office PPAs, and expected outputs from scratch.
  - Cleaned Template 4 (`IPERF`) and all existing IPERF documents via migration `2026-10-10-060000_CleanIperfDefaultDeliverables.php`.


- **[Fixed]** Distribution Team Save 404 Redirect (`/account/process-queue`)
  - Fixed an issue where saving changes to a team (adding or removing members) redirected users to `http://localhost:8080/account/process-queue` with a 404 error.
  - Root cause: `Team::store()` used `redirect()->back()`, which was retrieving the URL of the background email queue poller stored in `_ci_previous_url`.
  - Updated `Team::store()` to explicitly redirect to `teams?team_id=[id]` with success/error flashdata.
  - Added safeguards to `AccountManagement::processQueueAjax` and `Routes.php` to prevent background polling endpoints from polluting session redirect URLs.

- **[Added]** Dynamic Approval Disabling on Score Adjustments & Calibration
  - Implemented automatic validation on the **"Approve Rating"** (`#btn-approve`) and **"TWG Approve"** (`#btn-twg-approve`) buttons during evaluation.
  - If the Head of Office (HoO) or TWG adjusts or calibrates any score away from baseline ratings, the **"Approve"** button is automatically disabled with an explanatory tooltip (`Cannot approve directly: adjusted score(s) present`).
  - The evaluator has the final say on when to click **"Return for Revision"** (or **"Disapprove"**) to return the document with notes/remarks to the subordinate for revision.
  - **Reversion Re-enables Approval:** If the evaluator removes/reverts all of their adjustments back to the original submitted scores, the **"Approve"** button automatically becomes active and functional again in real time.
- **[Changed]** Score Adjustment Indicators (Corner Notification Marker)
  - Replaced bulky and cramped text pills under narrow score cells with a sleek, subtle **corner notification dot** (`.score-notif-marker` & `.score-notif-dot`).
  - Rating cells remain 100% clean and uncluttered for normal ratings. When a score has been adjusted by **Head of Office (HoO)** (🟢 emerald dot) or **TWG** (🟡 amber dot), an elegant glowing corner pip appears.
  - Hovering over the corner dot displays the adjustment tooltip (`was 4 ➔ now 3`), and clicking it opens the comprehensive 3-tier **Rating Attribution Breakdown Modal** (`#spms-score-audit-modal`).
  - Score Adjustment Guide Banner above the table is updated to reflect the notification dot indicator legend.
  - Fixed z-index stacking order so indicator pings remain scoped within table cells and never bleed through open modals or overlays.
  - Completely hidden during printing and exporting for official CSC compliance.
- **[Fixed]** Rating Dimension Disabler (N/A) UI Visibility & Cube Clutter
  - Fixed design error where the `N/A` dimension disabler buttons were persistently floating on top of all rating cubes and score attribution badges during the Evaluation Phase and in read-only document view modes.
  - Restricted interactive `N/A` toggle buttons (`toggleQte`) strictly to the **Target Drafting Phase** (`canEditTargets`), allowing authors to configure unmeasured dimensions during target and rubric definition.
  - In Evaluation, Review, Supervisor, and TWG modes, `N/A` toggle buttons are completely omitted. Deliverables with disabled dimensions render a clean, read-only `N/A` placeholder, while active dimensions render clean numeric inputs without overlapping corner buttons.
  - Updated score attribution modal and row serialization to cleanly handle both `X` and `N/A` values.
- **[Added]** University President & IPERF Sample Performance Accounts
  - Implemented the dedicated **University President** executive evaluator account (`president@test.com`, position: `University President`, unit: `Office of the President`, role: `Supervisor`).
  - Added dedicated **IPERF (Individual Performance Evaluation and Review Form)** Job Order / Contractual sample accounts:
    - `iperf@test.com` (*Danilo Ocampo*, Administrative Aide, HRDO, doc_type: `IPERF`, role: `Employee`).
    - `staff.gso1@test.com` (*Nestor Pascual*, Administrative Aide, General Services Office, doc_type: `IPERF`, role: `Employee`).
    - `staff.gso2@test.com` (*Leonora Villar*, Administrative Assistant, General Services Office, doc_type: `IPERF`, role: `Employee`).
  - **CSC SPMS Executive Gating & Delegation:**
    - Clarified the operational distinction between **HRDO / System Administrator** (`admin@test.com` - PMT Secretariat / Technical System Administration) and the **University President** (`president@test.com` - Head of Agency / Executive Evaluator).
    - Tier 0: HRDO Admin releases institutional evaluation cycles to the Vice Presidents (Executive Leadership Team: `OVPAA`, `OVPAF`).
    - Tier 0.5: Vice Presidents' institutional OPCRs automatically route to the **University President** on `/ratings` for executive rating, calibration, remarks, and approval.
    - Tier 1: Once approved, Vice Presidents cascade target basis commitments down to the 15 Academic College Deans (DPCR).
    - Tier 2 & 3: Deans cascade to Department Chairs, who in turn cascade to Department Faculty and Staff (IPCR/IPERF).
  - **Executive Ratings Dashboard Support:**
    - Configured `/ratings` to load root evaluation cycles automatically for supervisors without personal subordinate folders (such as the University President), providing direct review roster access to the Vice Presidents' OPCRs.
    - Added database migrations (`2026-10-10-030000_AddPresidentAccountAndPlantilla.php`, `2026-10-10-040000_AddIperfSampleAccount.php`) and updated `MasterSeeder.php` and `DepartmentSeeder.php`.
- **[Added]** Role-Based Score Attribution & Calibration Audit Trail (Ratee vs. Supervisor vs. TWG)
  - Implemented real-time score attribution on deliverable Quality (Q), Timeliness (T), and Efficiency (E) rating cells across all form types (IPCR, DPCR, OPCR, IPERF).
  - Distinguishes **Ratee Self-Rating** (🔵 `Self: 5`), **Head of Office Evaluation** (🟢 `Head: 4`), and **TWG Calibration** (🟡 `TWG: 3`).
  - Added visual score adjustment delta pills (e.g. `5 ➔ 4`) when a supervisor or TWG calibrates a score from the ratee's initial rating.
  - Added an interactive **Rating Attribution Breakdown Modal** (`#spms-score-audit-modal`) when clicking any score badge, displaying a step-by-step audit history of who rated or adjusted the score.
  - **Strict CSC Print & Export Compliance:** All badges, adjustment pills, and popovers are screen-only (`.print-hide`), ensuring browser printouts, PDF downloads, and `.xlsx` Excel exports cleanly contain only the single active evaluated whole number score.
- **[Added]** Google Drive & Cloud Evidence Links in Means of Verification (MOV)
  - Extended the Evidence modal to support attaching external Google Drive share links, OneDrive, Dropbox, and cloud URLs alongside encrypted physical files.
  - Added interactive mode switcher tab (`Upload File` vs. `Google Drive / Link`) with automatic provider recognition and live Google Drive badge indicators.
  - Implemented in-app preview for Google Drive documents, spreadsheets, and files via embeddable preview viewport with direct "Open in Google Drive" external fallback.
  - Added database migration to persist cloud URLs (`is_link`, `link_url`) in `document_attachments` with full activity logging and permission controls.

### Oct 9, 2026
- **[Fixed]** AJAX CSRF Cookie & FormData Payload Desynchronization
  - Resolved `SecurityException: The action you requested is not allowed` on Cycle Release / Cascade and other AJAX actions.
  - Set `Cookie::$secure = false` in `app/Config/Cookie.php` to prevent browsers from rejecting CSRF cookies on local HTTP development.
  - Enhanced `apiPost` in `api.js` and the Axios request/response interceptors in `config.js` to automatically append `csrf_test_name` to `FormData` and object payloads alongside HTTP headers.
- **[Fixed]** Evaluation Folder Creation & CSRF Token Desync
  - Added missing `csrf_field()` token to `#form-create-folder`, `#form-edit-folder`, and `#form-create-file` modals.
  - Resolved `SecurityException: The action you requested is not allowed` caused by `$regenerate = true` desynchronizing AJAX tokens with background polling.
  - Eliminated route conflict where `$routes->match(['post', 'delete'], 'folder')` collided with `$routes->post('folder', 'Folder::store')`.
  - Added user-facing toast alerts on modal submission failures so users receive immediate actionable feedback instead of silent failure.
  - Fixed PHP 8.2 null deprecation warnings when sanitizing optional folder deadline date inputs.
- **[Changed]** Category Standardization ("Core Mandate" to "Core Functions")
  - Renamed `CORE MANDATE` to `CORE FUNCTIONS` across OPCR documents, templates, basis matrix, and Excel exports to strictly align with CSC SPMS standard terminology.
- **[Changed]** Executive Cycle Release Terminology (Admin to VP)
  - Updated evaluation cycle distribution terminology for Administrators from "Cascade" to **"Cycle Release"** (e.g., "Cycle Release Management", "Release to Selected Team", "Released to Vice Presidents", "Revoke Cycle Release").
  - Retained strict Civil Service Commission (CSC) **"Cascade"** terminology for Supervisors (VPAA, Deans, Chairs) cascading commitments down through OPCR -> DPCR -> IPCR.
  - Dynamically customized right-sidebar panels, monitoring hub status tiles, roster tooltips, dialogs, and notifications according to the active role.
- **[Changed]** Blank Default & Required Category Percentage Weights (Core, Strategic, Support)
  - Category percentage weights are no longer pre-filled with hardcoded percentages (60%/25%/15% or 70%/20%/10%) and now load completely blank (`—` / `( ____ %)`).
  - Category weights are now strictly **REQUIRED** for target setting (non-IPERF forms):
    - Rendered with prominent required visual cues: red asterisk (`*`), dashed border, and validation tooltips.
    - Real-time client-side validation flags missing weights or weights that do not total 100% exactly.
    - Pre-submission gating in `lockFolderTarget` blocks target submission and automatically focuses any blank weight input.
    - Server-side validation in `Folder::submitTarget` validates that weights are present, non-zero, and sum to 100% before transitioning folders out of target drafting.
    - IPERF documents continue adhering to flat 100% Core committed outputs per CSC COS/JO guidelines.
- **[Added]** Screen-Only Row-Level "TWG Comments" Column
  - Added a dedicated "TWG Comments" column directly into the deliverable rows across all form types (IPCR, DPCR, OPCR, and IPERF).
  - Row-Level Only: TWG calibration comments are attached directly to individual deliverable rows; no general or overall TWG comment box is created, keeping the bottom summary remarks strictly for the Evaluator/PMT.
  - Print-Hide Guarantee: Styled with `.print-hide` and `.twg-col` (`display: none !important;` under `@media print`) so physical paper prints and downloaded PDFs omit the TWG column completely, maintaining official Civil Service Commission (CSC) print compliance.
  - Role-Based Permissions: Editable by users with the `TWG` or `Admin` role (`$canEditTwg`), while ratees and supervisors see comments in read-only mode. Includes a quick `+ [TWG]` stamp button for TWG reviewers.
  - Data Persistence: Autosaved and preserved per row as `twg_comment` in `tabs[0].formData.categories[category][row]`. Authorized TWG autosave requests in [Document.php](file:///c:/Users/Deus/Downloads/SPMS-capstone-main/app/Controllers/Document.php).
- **[Changed]** Omit "ACT" Column Outside Target Phase
  - Removed the `ACT` (row delete action) column from `<colgroup>`, `<thead>`, and deliverable table rows during the evaluation phase (and whenever target drafting is locked).
  - Eliminates empty, non-functional action cells during evaluation and supervisor/TWG review, reclaiming horizontal screen space for accomplishments, ratings, remarks, and TWG comments.
- **[Added]** Numerical & Adjectival Rating ("Grades & Evaluation") in Admin Master List
  - Added dedicated **Numerical Rating** (Grade) and **Adjectival Rating** (Evaluation) columns to the Administrator Master List table:
    - Numerical Rating: Displays the 2-decimal final rating score (e.g., `4.85`) or `—` for unevaluated personnel.
    - Adjectival Rating: Displays standardized CSC performance badges showing both full label and official abbreviation (`Outstanding (O)`, `Very Satisfactory (VS)`, `Satisfactory (S)`, `Unsatisfactory (US)`, `Poor (P)`, or `Not Yet Rated`).
  - Mobile Card Roster: Added a dedicated Grade and Evaluation footer row to personnel cards on mobile screens.
  - Search Integration: Enhanced the Master List search filter so administrators can search personnel by rating score or adjectival status (e.g., typing "outstanding", "VS", or "4.8").
  - Excel Master List Export (`.xlsx`): Updated `CscExcelExporter::exportMasterlist` to include both Numerical Rating and Adjectival Rating columns with aligned headers and styling.

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
