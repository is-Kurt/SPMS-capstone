# Changelog

All notable changes, features, and fixes across the Benguet State University Strategic Performance Management System (SPMS) codebase.

## 👥 Project Contributors & Committers
| Contributor | Git Handle | Email | Focus Areas & Key Contributions |
| :--- | :--- | :--- | :--- |
| **is-Kurt** | `@is-Kurt` | `skurto123o@gmail.com` | Core CI4 Framework Architecture, Role-Based Access Control (RBAC), Authentication & Turnstile Security, Automated Email Queue & Reminders, Auto-Save Data Loss Prevention, Approval Routing Presets, Dimension Gating (`N/A`), System Hardening |
| **Nanashi** | `@Nanashi` | `nnanashidayo000@gmail.com` | Official CSC Excel Exporter (`.xlsx`) & Uneditable Protection, Score Attribution Audit Trail & Modal, Mobile UI Overhaul & Responsive Drawer, Dark/Light Themes, University President & IPERF Workflows, Stage 2 Monitoring Hub & Honor Roster |
| **PeroroFaust** | `@PeroroFaust` | `nnanashidayo000@gmail.com` | Role Access & Route Guard Permissions Security Audits |

---

### Oct 21, 2026
- **[Changed]** Team Management Subtabs Order & Default Active Panel — *Committed by @is-Kurt*
  - Reordered Team Management tabs in `teams/index.php` so **Selected Members** is positioned first on the left and active by default.
  - Configured `#panel-selected` to open on initial view with active badge indicators, keeping directory search under **Available Users**.
- **[Changed]** Folder Supervising Authorities & Dashboard Master List UI Streamlining — *Committed by @is-Kurt*
  - Reorganized Supervising Authorities & Approval Status card in Folder view into clean subtabs (**Target Setting** vs **Evaluation Phase**) with dedicated sections for supervising authorities and TWG calibration.
  - Removed supervisor consensus summary pill badge from folder supervising authorities card header.
  - Streamlined Admin Dashboard Master List: unified header card, full-width search input, and personnel table into a single cohesive card container directly below Executive Overview.
  - Removed redundant 4-stat metric boxes and appointments dropdown from Master List toolbar in favor of interactive pill filter buttons with live counts.
  - Removed redundant CSC description paragraph, executive eyebrow oversight badge, and `Evaluator Queue` toolbar button.
- **[Fixed]** Cascaded by Admin Scope Isolation & Subordinate Routing Cleanup — *Committed by @Nanashi*
  - Corrected `is_cascaded_by_me` logic in `DocumentFolderModel.php` to strictly require `pf.parent_folder_id IS NULL`, ensuring only folders directly cascaded from the root evaluation cycle by the Administrator (e.g. Vice Presidents / executive direct reports) appear under **Cascaded by Admin**.
  - Prevented intermediate supervisor cascades (e.g. Vice President cascading to Deans, or Deans cascading to Chairs) from attaching the root cycle container owner as a Next-in-Rank Calibrator routing in `Folder::cascadeTeam()`.
  - Added strict executive role guards (`doc_type === 'opcr'` / Vice President / CAO) to `ensureAdminRoutingForExecutiveFolder()`, preventing automated Admin routing attachments on non-executive subordinate submissions (Deans, Chairs, Faculty).
  - Added self-healing routine to purge any legacy or erroneous Admin evaluation routings on subordinate folders (`pf.parent_folder_id IS NOT NULL`), cleanly isolating Deans and departmental staff into the **Overall (All Users)** scope.
- **[Added]** Admin Ratings Dashboard Dual Scope: Cascaded vs. Overall (All Users) — *Committed by @Nanashi*
  - Enhanced Admin Ratings dashboard (`/ratings`) to provide complete institutional oversight across all colleges, departments, and subordinates in an evaluation cycle.
  - Divided the Admin view into two intuitive scopes via a segmented toggle button:
    1. **Cascaded by Admin**: Focuses on direct subordinates and commitments directly cascaded by the Administrator/President with full approval authority.
    2. **Overall (All Users)**: Shows all employee submissions across the entire university (including subordinate levels cascaded by Deans and Department Chairs).
  - Added visual tags on table rows (`Directly Cascaded` vs `Subordinate Review`) and customized action buttons (`Review Targets` / `Open Evaluation` for direct reviews vs `Monitor Targets` / `Monitor Review` for subordinate oversight).
  - Subordinate rows open in the established read-only **Monitoring View** via existing RBAC logic in `Document.php` and `document/show.php`.
  - Dynamically updates queue tab counter badges and institutional header banners when switching between scopes without requiring page reloads.

### Oct 20, 2026
- **[Changed]** Admin Cascaded Approval & Executive Gating Alignment — *Committed by @Nanashi*
  - Aligned executive evaluation workflow with the institutional configuration where the University President and System Administrator are the same entity.
  - Updated `Folder::cascadeTeam()` and `ensureAdminRoutingForExecutiveFolder()` so Vice Presidential OPCR folders and any folders cascaded by Admin route directly to the Admin evaluator in `evaluation_routings`, removing the redirection to a separate President user account.
  - Enabled **Approve Target**, **Return Target for Revision**, **Calculate**, **Return Evaluation**, and **Approve Rating** actions for the Admin on folders cascaded by the Admin (`$isCascadedByMe`), while maintaining read-only `Monitoring View` on folders cascaded by other supervisors (Deans and Department Chairs).
  - Configured `/ratings` dashboard to cleanly display the Admin's cascaded commitments and executive OPCR review queue with an executive banner.
  - Added migration `2026-10-20-230000_UpdateAdminCascadedRoutings.php` and automated synchronization on `/ratings` to re-point existing VP evaluation routings to the active Admin.
- **[Removed]** Separate President Account (`president@test.com`) Decommissioning — *Committed by @Nanashi*
  - Fully deleted and purged the separate `president@test.com` (*Felipe Comila*) account, plantilla, user roles, notifications, and evaluator assignments.
  - Removed president account seeding from `MasterSeeder.php` and `DepartmentSeeder.php` to prevent recreation upon database seeding.
  - Added automatic purge routine `BaseController::purgeRetiredPresidentAccount()` executed on all requests to ensure `president@test.com` is permanently cleaned from active databases and all evaluation routings transfer seamlessly to the active Admin.
- **[Added]** Evaluation Folder Deletion in Folder Management & Database Purge — *Committed by @Nanashi*
  - Added a dedicated **Delete Folder** action button in Folder Management for both **Active Folders** (alongside Edit and Close & Archive) and **Archived Folders** (alongside Restore Folder).
  - Updated `Folder::destroy()` to permit Administrators to permanently delete any evaluation cycle (active or archived) on demand, removing the previous 5-year retention lockout.
  - Automatically cascades folder deletion through all child/grandchild folders, documents, attachments, evaluation routings, and notifications, resetting active folder session context smoothly.
  - Enhanced delete confirmation modal (`deleteModal.js`) with smart redirect support when deleting from the archived view.
  - Executed a complete database purge of all existing evaluation folders, child documents, attachments, and routings to provide a fresh start.

### Oct 13, 2026
- **[Changed]** Upgraded In-App User Guide Modal & Complete Operations Manual — *Committed by @Nanashi*
  - Upgraded in-app guide modal (`user_guide_modal.php`) with 6 streamlined steps, role-aware quick-jump shortcuts (Faculty & Staff, Deans & Chairs, Evidence & Drive, and Quick FAQs).
  - Aligned documentation with recent CSC Excel Exporter updates (removed deprecated browser print/PDF instructions, added `.xlsx` direct printing tips and default unlock password `spms`).
  - Added full Google Drive, OneDrive, and Dropbox cloud storage link guidance for large file portfolios in the MOV drawer.
  - Documented score adjustment indicator dots (🟢 HoO / 🟡 PTWG), dynamic approval disabling rules, and dimension gating (`N/A`).
  - Created comprehensive `docs/USER_OPERATIONS_MANUAL.md` covering end-to-end operational workflows for Faculty (IPCR/IPERF), Supervisors (DPCR), and HRDO Admins (cycles, plantilla, units, accounts), completing Capstone Phase 7.3.

### Oct 11, 2026
- **[Changed]** Standardized Official CSC Excel Export & Read-Only Protected Spreadsheets — *Committed by @Nanashi*
  - Removed the browser print / "Export PDF" button from document viewers and template editor headers to eliminate cropped margins, awkward page breaks, and browser print discrepancies.
  - Standardized **Export Excel** as the official export method across the system, ensuring 100% Civil Service Commission (CSC) spreadsheet fidelity, preserved formulas, and uncropped formatting.
  - Added **Worksheet & Workbook Protection** (uneditable mode) upon export (default unlock password: `spms`), locking all cells and formulas against tampering while preserving full viewing, copying, and printing capabilities.
- **[Added]** Dashboard Monitoring, Target Calibration & Top Performers — *Committed by @Nanashi*
  - Added **Stage 2: Monitoring, Coaching & Target Calibration (Per-Office View)** table in Dashboard (both Admin and Supervisor views).
  - Displays deliverable unit headcount, target calibration compliance bars, coaching revision logs, final evaluation completion counts, average numerical ratings, and SPMS status badges.
  - Added interactive client-side search filter (`filterOfficeLeaderboard`) and unit drill-down.
  - Added **Top Performing Personnel Leaderboard / Honor Roster** showcasing top-rated faculty and staff with rank medals (🥇 Gold, 🥈 Silver, 🥉 Bronze), plantilla designations, teaching indicators, and direct document inspect links.
- **[Changed]** Core Functions Standardization & Blank Category Weight Default — *Committed by @Nanashi*
  - Standardized all remaining "Core Mandate" references to "Core Functions" across `CscExcelExporter.php` and document view templates for complete CSC SPMS compliance.
  - Ensured category percentage weights start empty/blank by default, allowing users to input and calibrate custom category distributions that sum to 100%.
- **[Added]** PTWG Calibration Comments in Form Remarks — *Committed by @Nanashi*
  - Renamed document matrix review comment headers from `TWG COMMENTS` to `PTWG COMMENTS` across DPCR, OPCR, IPERF, and IPCR matrices.
  - Added quick `+ [PTWG]` stamp button and calibrated comment inputs with explicit role tags.
- **[Added]** Google Drive & Cloud Storage Evidence Integration — *Committed by @Nanashi*
  - Enhanced the MOV (Means of Verification) Evidence modal (`_mov_modal.php`) with direct support for Google Drive, Microsoft OneDrive, Dropbox, and cloud storage links.
  - Added automatic cloud provider badge detection, direct drive link opening, and embedded iframe preview with external fallback.

### Oct 10, 2026
- **[Added]** Synchronized Multi-Signatories (Header "APPROVED BY" & Footer "Final Rating by") — *Committed by @Nanashi*
  - Synchronized signatory blocks between the top header (`APPROVED BY:`) and the bottom footer (`Final Rating by:`).
  - Clicking **"+ Add Signatory"** (available at both the header and footer) dynamically appends matching signatory blocks to both sections.
  - Two-way real-time data synchronization: Typing the Name and Position of any signatory in either section immediately mirrors and updates across the form.
  - Removing a signatory block (`✕ Remove`) cleanly deletes that signatory from both the header and footer containers simultaneously.
  - Preserves distinct signing dates (Target Approval date vs. Final Evaluation date) for every signatory.
- **[Changed]** IPERF Blank Deliverable Table by Default — *Committed by @Nanashi*
  - Updated IPERF (Contract of Service and Job Order Personnel) templates and document initial blueprints to start with completely empty deliverable rows instead of hardcoded sample clerical entries.
  - Owners/employees now start with a clean slate to type in their specific contract duties, office PPAs, and expected outputs from scratch.
  - Cleaned Template 4 (`IPERF`) and all existing IPERF documents via migration `2026-10-10-060000_CleanIperfDefaultDeliverables.php`.
- **[Fixed]** Distribution Team Save 404 Redirect (`/account/process-queue`) — *Committed by @Nanashi*
  - Fixed an issue where saving changes to a team (adding or removing members) redirected users to `http://localhost:8080/account/process-queue` with a 404 error.
  - Root cause: `Team::store()` used `redirect()->back()`, which was retrieving the URL of the background email queue poller stored in `_ci_previous_url`.
  - Updated `Team::store()` to explicitly redirect to `teams?team_id=[id]` with success/error flashdata.
  - Added safeguards to `AccountManagement::processQueueAjax` and `Routes.php` to prevent background polling endpoints from polluting session redirect URLs.
- **[Added]** Dynamic Approval Disabling on Score Adjustments & Calibration — *Committed by @Nanashi*
  - Implemented automatic validation on the **"Approve Rating"** (`#btn-approve`) and **"TWG Approve"** (`#btn-twg-approve`) buttons during evaluation.
  - If the Head of Office (HoO) or TWG adjusts or calibrates any score away from baseline ratings, the **"Approve"** button is automatically disabled with an explanatory tooltip (`Cannot approve directly: adjusted score(s) present`).
  - The evaluator has the final say on when to click **"Return for Revision"** (or **"Disapprove"**) to return the document with notes/remarks to the subordinate for revision.
  - **Reversion Re-enables Approval:** If the evaluator removes/reverts all of their adjustments back to the original submitted scores, the **"Approve"** button automatically becomes active and functional again in real time.
- **[Changed]** Score Adjustment Indicators (Corner Notification Marker) — *Committed by @Nanashi*
  - Replaced bulky and cramped text pills under narrow score cells with a sleek, subtle **corner notification dot** (`.score-notif-marker` & `.score-notif-dot`).
  - Rating cells remain 100% clean and uncluttered for normal ratings. When a score has been adjusted by **Head of Office (HoO)** (🟢 emerald dot) or **TWG** (🟡 amber dot), an elegant glowing corner pip appears.
  - Hovering over the corner dot displays the adjustment tooltip (`was 4 ➔ now 3`), and clicking it opens the comprehensive 3-tier **Rating Attribution Breakdown Modal** (`#spms-score-audit-modal`).
  - Score Adjustment Guide Banner above the table is updated to reflect the notification dot indicator legend.
  - Fixed z-index stacking order so indicator pings remain scoped within table cells and never bleed through open modals or overlays.
  - Completely hidden during printing and exporting for official CSC compliance.
- **[Fixed]** Rating Dimension Disabler (N/A) UI Visibility & Cube Clutter — *Committed by @is-Kurt*
  - Fixed design error where the `N/A` dimension disabler buttons were persistently floating on top of all rating cubes and score attribution badges during the Evaluation Phase and in read-only document view modes.
  - Restricted interactive `N/A` toggle buttons (`toggleQte`) strictly to the **Target Drafting Phase** (`canEditTargets`), allowing authors to configure unmeasured dimensions during target and rubric definition.
  - In Evaluation, Review, Supervisor, and TWG modes, `N/A` toggle buttons are completely omitted. Deliverables with disabled dimensions render a clean, read-only `N/A` placeholder, while active dimensions render clean numeric inputs without overlapping corner buttons.
  - Updated score attribution modal and row serialization to cleanly handle both `X` and `N/A` values.
- **[Added]** University President & IPERF Sample Performance Accounts — *Committed by @Nanashi*
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
- **[Added]** Role-Based Score Attribution & Calibration Audit Trail (Ratee vs. Supervisor vs. TWG) — *Committed by @Nanashi*
  - Implemented real-time score attribution on deliverable Quality (Q), Timeliness (T), and Efficiency (E) rating cells across all form types (IPCR, DPCR, OPCR, IPERF).
  - Distinguishes **Ratee Self-Rating** (🔵 `Self: 5`), **Head of Office Evaluation** (🟢 `Head: 4`), and **TWG Calibration** (🟡 `TWG: 3`).
  - Added visual score adjustment delta pills (e.g. `5 ➔ 4`) when a supervisor or TWG calibrates a score from the ratee's initial rating.
  - Added an interactive **Rating Attribution Breakdown Modal** (`#spms-score-audit-modal`) when clicking any score badge, displaying a step-by-step audit history of who rated or adjusted the score.
  - **Strict CSC Print & Export Compliance:** All badges, adjustment pills, and popovers are screen-only (`.print-hide`), ensuring browser printouts, PDF downloads, and `.xlsx` Excel exports cleanly contain only the single active evaluated whole number score.

### Oct 9, 2026
- **[Fixed]** AJAX CSRF Cookie & FormData Payload Desynchronization — *Committed by @is-Kurt*
  - Resolved `SecurityException: The action you requested is not allowed` on Cycle Release / Cascade and other AJAX actions.
  - Set `Cookie::$secure = false` in `app/Config/Cookie.php` to prevent browsers from rejecting CSRF cookies on local HTTP development.
  - Enhanced `apiPost` in `api.js` and the Axios request/response interceptors in `config.js` to automatically append `csrf_test_name` to `FormData` and object payloads alongside HTTP headers.
- **[Fixed]** Evaluation Folder Creation & CSRF Token Desync — *Committed by @is-Kurt*
  - Added missing `csrf_field()` token to `#form-create-folder`, `#form-edit-folder`, and `#form-create-file` modals.
  - Resolved `SecurityException: The action you requested is not allowed` caused by `$regenerate = true` desynchronizing AJAX tokens with background polling.
  - Eliminated route conflict where `$routes->match(['post', 'delete'], 'folder')` collided with `$routes->post('folder', 'Folder::store')`.
  - Added user-facing toast alerts on modal submission failures so users receive immediate actionable feedback instead of silent failure.
  - Fixed PHP 8.2 null deprecation warnings when sanitizing optional folder deadline date inputs.
- **[Changed]** Category Standardization ("Core Mandate" to "Core Functions") — *Committed by @Nanashi*
  - Renamed `CORE MANDATE` to `CORE FUNCTIONS` across OPCR documents, templates, basis matrix, and Excel exports to strictly align with CSC SPMS standard terminology.
- **[Changed]** Executive Cycle Release Terminology (Admin to VP) — *Committed by @Nanashi*
  - Updated evaluation cycle distribution terminology for Administrators from "Cascade" to **"Cycle Release"** (e.g., "Cycle Release Management", "Release to Selected Team", "Released to Vice Presidents", "Revoke Cycle Release").
  - Retained strict Civil Service Commission (CSC) **"Cascade"** terminology for Supervisors (VPAA, Deans, Chairs) cascading commitments down through OPCR -> DPCR -> IPCR.
  - Dynamically customized right-sidebar panels, monitoring hub status tiles, roster tooltips, dialogs, and notifications according to the active role.
- **[Changed]** Blank Default & Required Category Percentage Weights (Core, Strategic, Support) — *Committed by @Nanashi*
  - Category percentage weights are no longer pre-filled with hardcoded percentages (60%/25%/15% or 70%/20%/10%) and now load completely blank (`—` / `( ____ %)`).
  - Category weights are now strictly **REQUIRED** for target setting (non-IPERF forms):
    - Rendered with prominent required visual cues: red asterisk (`*`), dashed border, and validation tooltips.
    - Real-time client-side validation flags missing weights or weights that do not total 100% exactly.
    - Pre-submission gating in `lockFolderTarget` blocks target submission and automatically focuses any blank weight input.
    - Server-side validation in `Folder::submitTarget` validates that weights are present, non-zero, and sum to 100% before transitioning folders out of target drafting.
    - IPERF documents continue adhering to flat 100% Core committed outputs per CSC COS/JO guidelines.
- **[Added]** Screen-Only Row-Level "PTWG Comments" Column — *Committed by @Nanashi*
  - Added a dedicated "PTWG Comments" column directly into the deliverable rows across all form types (IPCR, DPCR, OPCR, and IPERF).
  - Row-Level Only: PTWG calibration comments are attached directly to individual deliverable rows; no general or overall PTWG comment box is created, keeping the bottom summary remarks strictly for the Evaluator/PMT.
  - Print-Hide Guarantee: Styled with `.print-hide` and `.twg-col` (`display: none !important;` under `@media print`) so physical paper prints and downloaded PDFs omit the PTWG column completely, maintaining official Civil Service Commission (CSC) print compliance.
  - Role-Based Permissions: Editable by users with the `TWG` or `Admin` role (`$canEditTwg`), while ratees and supervisors see comments in read-only mode. Includes a quick `+ [PTWG]` stamp button for PTWG reviewers.
  - Data Persistence: Autosaved and preserved per row as `twg_comment` in `tabs[0].formData.categories[category][row]`. Authorized PTWG autosave requests in `Document.php`.
- **[Changed]** Omit "ACT" Column Outside Target Phase — *Committed by @Nanashi*
  - Removed the `ACT` (row delete action) column from `<colgroup>`, `<thead>`, and deliverable table rows during the evaluation phase (and whenever target drafting is locked).
  - Eliminates empty, non-functional action cells during evaluation and supervisor/PTWG review, reclaiming horizontal screen space for accomplishments, ratings, remarks, and PTWG comments.
- **[Added]** Numerical & Adjectival Rating ("Grades & Evaluation") in Admin Master List — *Committed by @Nanashi*
  - Added dedicated **Numerical Rating** (Grade) and **Adjectival Rating** (Evaluation) columns to the Administrator Master List table:
    - Numerical Rating: Displays the 2-decimal final rating score (e.g., `4.85`) or `—` for unevaluated personnel.
    - Adjectival Rating: Displays standardized CSC performance badges showing both full label and official abbreviation (`Outstanding (O)`, `Very Satisfactory (VS)`, `Satisfactory (S)`, `Unsatisfactory (US)`, `Poor (P)`, or `Not Yet Rated`).
  - Mobile Card Roster: Added a dedicated Grade and Evaluation footer row to personnel cards on mobile screens.
  - Search Integration: Enhanced the Master List search filter so administrators can search personnel by rating score or adjectival status (e.g., typing "outstanding", "VS", or "4.8").
  - Excel Master List Export (`.xlsx`): Updated `CscExcelExporter::exportMasterlist` to include both Numerical Rating and Adjectival Rating columns with aligned headers and styling.

### Oct 8, 2026
- **[Added]** Evidence File Encryption — *Committed by @Nanashi*
  - Encrypted all uploaded evidence photos and files on disk using AES-256-CBC so files cannot be directly read from the storage directory.
  - Automatically streams and decrypts evidence on demand when authenticated users inspect attachments in the browser.
- **[Removed]** Login Page Clutter — *Committed by @Nanashi*
  - Removed unnecessary boxes and redundant privacy policy text blocks from the login page.
  - Removed the obstructive "Welcome back" pop-up banner after authenticating.
- **[Changed]** Loading Screen Transition — *Committed by @Nanashi*
  - Replaced the loading screen graphic with the official Benguet State University (BSU) seal and a high-performance spinning ring indicator.
  - Removed artificial wait delays so transitions dismiss immediately as soon as page assets finish loading.
- **[Fixed]** Duplicate Return Button — *Committed by @Nanashi*
  - Cleaned up document navigation and removed redundant Return buttons from the document editor header.
- **[Added]** Sign-In Animation & Toast Notifications — *Committed by @Nanashi*
  - Added smooth entrance fade-in animations on the login portal.
  - Added interactive loading spinner state to the Sign In submit button.
  - Converted logout alerts and session messages into floating top-right notification toasts.
- **[Changed]** Mobile Navigation & Theme Cleanup — *Committed by @Nanashi*
  - Converted mobile folder navigation menu into an animated slide-out drawer.
  - Cleaned up mobile lifecycle timeline view to eliminate horizontal overflow.
  - Upgraded theme color tokens with zinc, emerald, and amber palette.

---

## Past Updates

### Oct 5, 2026
- **[Added]** Mobile Audit Trail Cards — *Committed by @Nanashi*
  - Converted audit trail table into responsive card layout for small displays.
  - Displays user avatar, name, role badge, email, action type, and client IP/platform details.
  - Organized summary counters into clean 2-row layout on mobile.
  - Added collapsible date filter drawer and instant CSV export button.
- **[Added]** Dark and Light Mode System Support — *Committed by @Nanashi*
  - Added system theme detection honoring OS-level light/dark mode preferences.
  - Added interactive theme toggle switch to document workspace (OPCR/DPCR/IPCR).
  - Extended theme switching to authentication portals (login, register, forgot password).
  - Standardized dashboard cards and badges with soft pastel accents in light mode.
- **[Changed]** Mobile UI Phase 2 Enhancements — *Committed by @Nanashi*
  - Added dedicated mobile notifications screen with unread badge counters.
  - Added mobile folder action popover menu.
  - Streamlined dashboard cards for mobile portrait viewports.

### Oct 3, 2026
- **[Fixed]** Mobile Screen Alignment & Light Theme Adjustments — *Committed by @Nanashi*
  - Phase 1 mobile layout and alignment adjustments across all core screens.
  - Refined light mode contrast and typography.
  - Updated application security packages and dev safeguards.
- **[Removed]** Node Modules Tracking Cleanup — *Committed by @Nanashi*
  - Removed tracked temporary dependency artifacts.

### Oct 1, 2026
- **[Removed]** Node Modules Tracking — *Committed by @is-Kurt*
  - Removed accidental `node_modules` commits from git history and updated `.gitignore`.
- **[Added]** Technical Working Group (TWG) Assignment — *Committed by @Nanashi*
  - Added dedicated interface for assigning TWG calibration reviewers to evaluation cycles.

### Sep 26, 2026
- **[Fixed]** Merge Conflicts & Local Layout Alignment — *Committed by @is-Kurt & @Nanashi*
  - Resolved merge conflicts and upgraded rich text editor libraries.
  - Fixed off-positioned action buttons across document header toolbars.
  - Form calculation safeguards and decimal precision alignment.
- **[Fixed]** Late Furnishings 4.0 — *Committed by @Nanashi*
  - Resolved lingering form calculations and small layout alignment bugs.

### Sep 25, 2026
- **[Changed]** Form Layout Furnishings & Aesthetics — *Committed by @Nanashi*
  - Polished deliverable row layout, table header styling, and subtotal badges.

### Sep 24, 2026
- **[Added]** Cloudflare Turnstile Spam & Bot Protection — *Committed by @is-Kurt*
  - Integrated Cloudflare Turnstile challenge on authentication forms with local development bypass guards.

### Sep 19, 2026
- **[Added]** Official CSC Excel Exporter (`.xlsx`) — *Committed by @Nanashi*
  - Built `CscExcelExporter` to stream official Civil Service Commission formatted spreadsheets.
  - Integrated CSC 5-point rating rubric guide drawer.
  - Added automated background checker for evaluation deadlines.

### Sep 15, 2026
- **[Added]** Approval Routing Presets — *Committed by @Nanashi*
  - Introduced customizable multi-tier approval routing presets.
  - Enhanced folder editing and creation dialogs with validation.

### Sep 13, 2026
- **[Added]** Campus Units & Department Structure — *Committed by @Nanashi*
  - Added organizational departments, campus units, and plantilla designations.
  - Added user invitations and onboarding directory management.

### Sep 10, 2026
- **[Changed]** Administrator Ratings UI Refactor — *Committed by @Nanashi*
  - Redesigned ratings monitoring interface for system administrators.
- **[Added]** In-App User Manual & Activity Logs — *Committed by @Nanashi*
  - Added comprehensive in-app user guide document.
  - Added centralized activity log database table tracking user actions and events.

### Sep 8, 2026
- **[Fixed]** Role-Specific Dashboard Perspectives — *Committed by @Nanashi*
  - Tailored dashboard metric cards and status widgets specifically for Admin, Supervisor, and Employee roles.

### Sep 5, 2026
- **[Added]** Notification System & Header Bell Counter — *Committed by @Nanashi*
  - Added in-app notifications hub with unread indicator badges.
- **[Fixed]** Dashboard Metric Calculations & Pagination — *Committed by @Nanashi*
  - Corrected aggregate score formulas on dashboard overview cards.
  - Added table pagination controls to administrative reports.
- **[Fixed]** Double Check Permissions — *Committed by @PeroroFaust*
  - Verified role access and route guard permissions across document and folder operations.
- **[Changed]** Institutional BSU Branding — *Committed by @Nanashi*
  - Integrated official Benguet State University seal and logos.
- **[Added]** Feature Reports & Mobile Display — *Committed by @Nanashi*
  - Added official header requirements and mobile navigation bar.

### Sep 4, 2026
- **[Added]** Automated Document Naming — *Committed by @Nanashi*
  - Implemented dynamic document title generation incorporating ratee name, position, and academic semester.
- **[Added]** CSC 5-Point Rating Rubric Matrix — *Committed by @Nanashi*
  - Embedded CSC Quality, Timeliness, and Efficiency rating standards directly into evaluation workspaces.
- **[Changed]** System Overhaul & Calculation Engine — *Committed by @Nanashi*
  - Overhauled form editor layout and deliverable row average formulas.

### Sep 3, 2026
- **[Changed]** Target Setting Success Indicators — *Committed by @Nanashi*
  - Unlocked success indicators for active editing during target drafting phase.
- **[Changed]** University Visual Palette — *Committed by @Nanashi*
  - Aligned primary accents and UI elements with Benguet State University forest green branding.

### Sep 2, 2026
- **[Refactored]** Routing Preset Identifiers & TinyMCE Configuration — *Committed by @is-Kurt*
  - Refactored approval routing preset IDs to use short string identifiers for clean URL and state serialization.
  - Updated TinyMCE rich text plugins, toolbar controls, and configuration parameters.
  - Polished `MasterSeeder` styling and initial system seed records.
- **[Fixed]** Document View Alignment & Border Rendering — *Committed by @is-Kurt*
  - Resolved minor table border rendering inconsistencies in document view templates.

### Aug 31, 2026
- **[Fixed]** Calculation Formatting & TWG Role Permissions — *Committed by @is-Kurt*
  - Fixed auto-calculate decimal precision and rounding format across deliverable rating cells.
  - Updated Technical Working Group (TWG) account roles and evaluation review permissions.

### Aug 22, 2026
- **[Fixed]** Auto-Save Data Loss Prevention — *Committed by @is-Kurt*
  - Fixed critical bug where rapid document folder auto-saves could result in empty tabs or lost draft text.
  - Improved dropdown deadline validation logic to strictly check target phase cutoff dates.
  - Enhanced mobile layout for Target and Evaluation Phase toolbars to eliminate squishing.
  - Ensured supervisor folder highlighting in the sidebar remains active when navigating subordinate documents.
  - Relocated Teams Save button directly into the scrollable filter container for improved usability.

### Aug 20, 2026
- **[Added]** Dual-Tab Editor & Rubrics Integration — *Committed by @is-Kurt*
  - Implemented dual-tab TinyMCE editor for documents and templates (Deliverables Target View vs. Rubrics Matrix View).
  - Added database migration adding `rubrics_content` column to documents and templates tables.
  - Refactored folder lifecycle engine to allow resubmission of expired `TARGET_UNAPPROVED` folders when deadlines are extended by supervisors.
  - Standardized folder status labels across all views (cleaned underscore formatting).
  - Added dynamic default tab selection in Ratings dashboard based on the active evaluation phase.
  - Redesigned subordinate reviewing employee cards with clean avatar initials.
- **[Added]** Supervisor Target Approvals & Automated Email Reminders — *Committed by @is-Kurt*
  - Implemented formal supervisor approval workflow for target commitments.
  - Integrated automated background email reminders notifying supervisors of pending approvals.

### Aug 19, 2026
- **[Fixed]** Average Score Calculation Logic — *Committed by @is-Kurt*
  - Fixed arithmetic averaging formula across Q, T, and E dimensions to handle zero and partial weights accurately.

### Aug 2 - 4, 2026
- **[Fixed]** Login Attempts Rate-Limiter Bug — *Committed by @is-Kurt*
  - Added unique Device ID tracking to login attempt records, resolving an issue where legitimate users were locked out after rate-limit resets.
  - Disabled outgoing email dispatch for mock and test accounts to prevent SMTP errors in development.
  - Added bottom padding to Teams tab and filter sidebar on responsive viewports.
- **[Added]** User Profile & Email Verification Management — *Committed by @is-Kurt*
  - Upgraded user profile settings with avatar upload size validation.
  - Added secure email address change workflow with verification requirements.
  - Fixed flex layout and scrollbar overflow boundaries in ratings and document workspaces.

### Jul 30 - 31, 2026
- **[Changed]** Target vs. Evidence Toggles & Guide Protection — *Committed by @is-Kurt*
  - Separated target definition inputs from evidence attachment controls based on active folder phase.
  - Locked user guide templates against unintended employee edits.
  - Improved mobile document editor scaling and touch scrolling.
  - Added layout z-index hierarchy fixes and 2FA email delivery infrastructure.
  - Hidden action buttons on accepted team invitations to prevent duplicate submissions.

### Jul 29, 2026
- **[Fixed]** Signup Validation & Evaluation Extension Logic — *Committed by @is-Kurt*
  - Resolved layout shifts during real-time signup form validation errors.
  - Corrected folder locking behavior when supervisors extend evaluation deadlines.
  - Upgraded TinyMCE table rendering on mobile viewports.

### Jul 20 - 23, 2026
- **[Changed]** Mobile Account Management & Layout Scaling — *Committed by @is-Kurt*
  - Simplified mobile account list rows (v1 and v2) for improved mobile density.
  - Relocated "Forgot Password" link on mobile screens to the bottom of the login container to prevent keyboard obstruction.
  - Fixed flexbox squashing and content cutoff on short laptop and phone displays.

### Jul 12 - 13, 2026
- **[Added]** Position Descendants & Team Cascading Architecture — *Committed by @is-Kurt*
  - Modeled organizational position descendants to establish hierarchical supervisor-subordinate relationships.
  - Added database seeders for standard institutional templates and routing definitions.
  - Upgraded background email queue worker for reliable transactional email delivery.
  - Cleaned repository tracking and reinforced `.gitignore` rules against database backups.

### Jul 1 - 5, 2026
- **[Added]** Evaluator Progress Indicator & Ratings Filters — *Committed by @is-Kurt*
  - Added live progress bar in ratings view showing the supervisor's completion rate of subordinate evaluations.
  - Added multi-criteria filter tabs across ratings rosters (status, department, role).
  - Resolved CSRF token desynchronization during "Remember Me" persistent sessions.
  - Fixed TinyMCE table rendering constraints on small mobile devices.

### Jun 22 - 29, 2026
- **[Added]** System-Wide Authentication & Navigation Revamp — *Committed by @is-Kurt*
  - Implemented secure Forgot Password token generation and email reset flow.
  - Overhauled user profile interface and account security settings.
  - Introduced mobile bottom sheet modal navigation.
  - Centralized real-time notification dispatch engine.

### Jun 10 - 17, 2026
- **[Added]** Ratings Evaluation Workspace & Document Templates — *Committed by @is-Kurt*
  - Established dedicated `/ratings` evaluator dashboard.
  - Built document template engine allowing administrators to create and manage reusable evaluation blueprints.
  - Integrated public vendor assets for Axios and TinyMCE to ensure offline development reliability.
  - Added centralized account management views for HRDO administrators.

### May 3, 2026
- **[Changed]** Role Hierarchy: Evaluator to Supervisor — *Committed by @is-Kurt*
  - Migrated legacy "Evaluator" designation to "Supervisor" across the entire database schema, routes, and controllers to reflect official CSC terminology.

### Apr 10 - 21, 2026
- **[Added]** User Database Schema & Role-Based Access Control — *Committed by @is-Kurt*
  - Created initial user roles schema (`Administrator`, `Supervisor`, `Employee`) with permission guards.
  - Implemented transactional email delivery service and user profile database migrations.
  - Initial UI updates and layout scaffolding for navigation and forms.

### Mar 29, 2026
- **[Added]** Initial SPMS Repository & Framework Architecture — *Committed by @is-Kurt*
  - Initialized Benguet State University Strategic Performance Management System (SPMS) project repository.
  - Configured CodeIgniter 4 framework, environment configuration placeholders, and routing scaffolding.
  - Implemented base authentication middleware and session redirect guards for protected routes.
