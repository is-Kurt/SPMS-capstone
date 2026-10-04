<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<style>
    /* Dedicated Dark & Light Mode Theme Tokens for Activity Center */
    .notif-page-bg {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .dark .notif-page-bg {
        background-color: #020d07;
        color: #f8fafc;
    }

    .notif-title-main {
        color: #0f172a;
    }
    .dark .notif-title-main {
        color: #ffffff;
    }

    .notif-subtext {
        color: #64748b;
    }
    .dark .notif-subtext {
        color: #94a3b8;
    }

    /* Tag Pill */
    .notif-tag-pill {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }
    .dark .notif-tag-pill {
        background-color: #062c1e;
        border: 1px solid #0f593b;
        color: #34d399;
    }

    /* Unread Badge (Header) */
    .notif-badge-unread {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }
    .dark .notif-badge-unread {
        background-color: #3c1214;
        border: 1px solid #6b1e25;
        color: #f87171;
    }

    /* Top Action Buttons */
    .notif-btn-top-green {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #047857;
    }
    .notif-btn-top-green:hover {
        background-color: #ecfdf5;
        border-color: #a7f3d0;
    }
    .dark .notif-btn-top-green {
        background-color: #062c1e;
        border: 1px solid #105e3e;
        color: #34d399;
    }
    .dark .notif-btn-top-green:hover {
        background-color: #0a3d2a;
    }

    .notif-btn-top-red {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #b91c1c;
    }
    .notif-btn-top-red:hover {
        background-color: #fef2f2;
        border-color: #fecaca;
    }
    .dark .notif-btn-top-red {
        background-color: #280c10;
        border: 1px solid #571922;
        color: #f87171;
    }
    .dark .notif-btn-top-red:hover {
        background-color: #381117;
    }

    /* Filter Tab Segmented Capsule */
    .notif-tab-box {
        background-color: #e2e8f0;
        border: 1px solid #cbd5e1;
    }
    .dark .notif-tab-box {
        background-color: #041910;
        border: 1px solid #0d3b27;
    }

    .notif-tab-active {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #047857;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .dark .notif-tab-active {
        background-color: #0a422a;
        border: 1px solid #146642;
        color: #ffffff;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);
    }

    .notif-tab-inactive {
        background-color: transparent;
        border: 1px solid transparent;
        color: #64748b;
    }
    .notif-tab-inactive:hover {
        color: #0f172a;
    }
    .dark .notif-tab-inactive {
        color: #94a3b8;
    }
    .dark .notif-tab-inactive:hover {
        color: #ffffff;
    }

    /* Search Box */
    .notif-search-box {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
    }
    .notif-search-box::placeholder {
        color: #94a3b8;
    }
    .notif-search-box:focus {
        border-color: #059669;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
    }
    .dark .notif-search-box {
        background-color: #041910;
        border: 1px solid #0d3b27;
        color: #ffffff;
    }
    .dark .notif-search-box::placeholder {
        color: #64748b;
    }
    .dark .notif-search-box:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }

    /* Notification Cards: Emerald (Normal/Approval) */
    .notif-card-emerald {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
    }
    .notif-card-emerald:hover {
        border-color: #a7f3d0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .dark .notif-card-emerald {
        background-color: #031c12;
        border: 1px solid #0e4b31;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
    }
    .dark .notif-card-emerald:hover {
        border-color: #156e48;
    }

    .notif-icon-emerald {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #059669;
    }
    .dark .notif-icon-emerald {
        background-color: #062d1d;
        border: 1px solid #0f5939;
        color: #34d399;
    }

    .notif-title-emerald {
        color: #0f172a;
    }
    .dark .notif-title-emerald {
        color: #ffffff;
    }

    .notif-phase-emerald {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }
    .dark .notif-phase-emerald {
        background-color: #05291b;
        border: 1px solid #0d4e32;
        color: #34d399;
    }

    .notif-btn-emerald {
        background-color: #059669;
        border: 1px solid #047857;
        color: #ffffff;
    }
    .notif-btn-emerald:hover {
        background-color: #047857;
    }
    .dark .notif-btn-emerald {
        background-color: #073824;
        border: 1px solid #115a3a;
        color: #34d399;
    }
    .dark .notif-btn-emerald:hover {
        background-color: #0a472e;
    }

    /* Notification Cards: Amber (Warning/Revoked/Returned) */
    .notif-card-amber {
        background-color: #fffbeb;
        border: 1px solid #fde68a;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
    }
    .notif-card-amber:hover {
        border-color: #fcd34d;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .dark .notif-card-amber {
        background-color: #181104;
        border: 1px solid #483009;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
    }
    .dark .notif-card-amber:hover {
        border-color: #6a470d;
    }

    .notif-icon-amber {
        background-color: #fef3c7;
        border: 1px solid #fde68a;
        color: #d97706;
    }
    .dark .notif-icon-amber {
        background-color: #281a05;
        border: 1px solid #57390a;
        color: #fbbf24;
    }

    .notif-title-amber {
        color: #b45309;
    }
    .dark .notif-title-amber {
        color: #f59e0b;
    }

    .notif-phase-amber {
        background-color: #fef3c7;
        border: 1px solid #fde68a;
        color: #b45309;
    }
    .dark .notif-phase-amber {
        background-color: #2d1b06;
        border: 1px solid #59390f;
        color: #fbbf24;
    }

    .notif-btn-amber {
        background-color: #d97706;
        border: 1px solid #b45309;
        color: #ffffff;
    }
    .notif-btn-amber:hover {
        background-color: #b45309;
    }
    .dark .notif-btn-amber {
        background-color: #2e1c05;
        border: 1px solid #5a370a;
        color: #fbbf24;
    }
    .dark .notif-btn-amber:hover {
        background-color: #3d2507;
    }

    /* Message typography */
    .notif-text-bold {
        color: #0f172a;
    }
    .dark .notif-text-bold {
        color: #ffffff;
    }

    .notif-text-body {
        color: #475569;
    }
    .dark .notif-text-body {
        color: #cbd5e1;
    }

    .notif-text-meta {
        color: #64748b;
    }
    .dark .notif-text-meta {
        color: #94a3b8;
    }

    /* Dismiss Cross */
    .notif-dismiss-btn {
        color: #94a3b8;
    }
    .notif-dismiss-btn:hover {
        color: #0f172a;
    }
    .dark .notif-dismiss-btn {
        color: #64748b;
    }
    .dark .notif-dismiss-btn:hover {
        color: #ffffff;
    }
</style>

<main class="min-h-[calc(100vh-4.5rem)] notif-page-bg py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-200">
    <div class="max-w-5xl mx-auto flex flex-col gap-6 pb-20">
        
        <!-- Header & Global Actions (Clean, directly on background) -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <!-- Top Badge & Subtitle -->
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider notif-tag-pill">
                        ACTIVITY CENTER
                    </span>
                    <span class="text-xs notif-subtext font-medium">• Real-time alerts</span>
                </div>

                <!-- Title & Unread Pill -->
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-extrabold notif-title-main tracking-tight">Notifications</h1>
                    <span id="page-unread-pill" class="<?= ($unreadCount > 0) ? '' : 'hidden' ?> px-2.5 py-0.5 rounded-full notif-badge-unread text-xs font-bold">
                        <span id="page-unread-count-text"><?= $unreadCount ?></span> new
                    </span>
                </div>

                <!-- Description -->
                <p class="text-xs sm:text-sm notif-subtext mt-1 max-w-2xl">
                    Track submissions, target approvals, evaluations, TWG verifications, and system updates.
                </p>
            </div>

            <!-- Global Action Buttons -->
            <div class="flex items-center gap-2.5 shrink-0 sm:pt-1">
                <button type="button" id="btn-page-mark-all"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold notif-btn-top-green transition-all cursor-pointer shadow-xs active:scale-95"
                        title="Mark all as read">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Mark all read</span>
                </button>

                <button type="button" id="btn-page-clear-all"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold notif-btn-top-red transition-all cursor-pointer shadow-xs active:scale-95"
                        title="Clear all notifications">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Clear all</span>
                </button>
            </div>
        </div>

        <!-- Filter Tab Capsule Bar & Search Input -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-1">
            
            <!-- Left Filter Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl notif-tab-box gap-1 shrink-0 self-start">
                <button type="button" id="tab-unread"
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer notif-tab-active">
                    Unread <span id="tab-count-unread"><?= $unreadCount ?></span>
                </button>
                <button type="button" id="tab-all"
                        class="px-4 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer notif-tab-inactive">
                    All <span id="tab-count-all"><?= count($notifications) ?></span>
                </button>
            </div>

            <!-- Right Instant Search Input -->
            <div class="relative w-full sm:w-80 md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none notif-subtext">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="notif-search-input" 
                       placeholder="Filter by keyword, folder, or submitter..." 
                       class="w-full pl-9 pr-8 py-2 rounded-xl text-xs notif-search-box focus:outline-none transition-all shadow-xs">
                <button type="button" id="notif-search-clear" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center notif-dismiss-btn cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Notification Cards Feed -->
        <div id="notifications-feed" class="flex flex-col gap-3 min-h-[300px]">
            <!-- Injected via JavaScript for live interactivity -->
        </div>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let notifications = <?= json_encode($notifications, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?> || [];
        let currentFilter = 'unread'; // 'unread' or 'all'
        let searchQuery = '';

        const feedEl = document.getElementById('notifications-feed');
        const tabUnread = document.getElementById('tab-unread');
        const tabAll = document.getElementById('tab-all');
        const tabCountUnread = document.getElementById('tab-count-unread');
        const tabCountAll = document.getElementById('tab-count-all');
        const pageUnreadPill = document.getElementById('page-unread-pill');
        const pageUnreadCountText = document.getElementById('page-unread-count-text');
        const searchInput = document.getElementById('notif-search-input');
        const searchClearBtn = document.getElementById('notif-search-clear');
        const markAllBtn = document.getElementById('btn-page-mark-all');
        const clearAllBtn = document.getElementById('btn-page-clear-all');

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token-hash"]')?.content || '';
        }

        function getCsrfName() {
            return document.querySelector('meta[name="csrf-token-name"]')?.content || 'csrf_test_name';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function getNotificationPhase(item) {
            const typeStr = (item.type || '').toLowerCase();
            const titleStr = (item.title || '').toLowerCase();
            const msgStr = (item.message || '').toLowerCase();

            if (typeStr.includes('target') || titleStr.includes('target') || msgStr.includes('target')) {
                return 'TARGET SETTING PHASE';
            }
            if (typeStr.includes('eval') || titleStr.includes('eval') || msgStr.includes('eval')) {
                return 'EVALUATION PHASE';
            }
            if (typeStr.includes('twg') || titleStr.includes('twg') || msgStr.includes('twg')) {
                return 'TWG REVIEW PHASE';
            }
            if (typeStr.includes('audit') || titleStr.includes('audit') || msgStr.includes('audit')) {
                return 'AUDIT LOGGED';
            }
            return 'SPMS WORKFLOW';
        }

        function getButtonLabel(item, isWarning) {
            if (isWarning) {
                const msgLower = (item.message || '').toLowerCase();
                const titleLower = (item.title || '').toLowerCase();
                if (msgLower.includes('draft') || titleLower.includes('revok') || msgLower.includes('revok')) {
                    return 'View Draft';
                }
                return 'Review Revision';
            }
            return 'Open Rating';
        }

        function syncHeaderBadge(unreadCount) {
            const headerBadge = document.getElementById('notification-badge');
            if (headerBadge) {
                if (unreadCount > 0) {
                    headerBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    headerBadge.classList.remove('hidden');
                } else {
                    headerBadge.classList.add('hidden');
                }
            }
            const drawerBadge = document.getElementById('drawer-notif-badge');
            if (drawerBadge) {
                if (unreadCount > 0) {
                    drawerBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    drawerBadge.classList.remove('hidden');
                } else {
                    drawerBadge.classList.add('hidden');
                }
            }
        }

        function updateCounts() {
            const unreadCount = notifications.filter(n => !n.is_read).length;
            const totalCount = notifications.length;

            if (tabCountUnread) tabCountUnread.textContent = unreadCount;
            if (tabCountAll) tabCountAll.textContent = totalCount;

            if (pageUnreadPill && pageUnreadCountText) {
                if (unreadCount > 0) {
                    pageUnreadCountText.textContent = unreadCount;
                    pageUnreadPill.classList.remove('hidden');
                } else {
                    pageUnreadPill.classList.add('hidden');
                }
            }

            syncHeaderBadge(unreadCount);
        }

        function renderFeed() {
            updateCounts();

            let filtered = notifications.slice();

            if (currentFilter === 'unread') {
                filtered = filtered.filter(n => !n.is_read);
            }

            if (searchQuery.trim()) {
                const q = searchQuery.toLowerCase().trim();
                filtered = filtered.filter(n => {
                    const title = (n.title || '').toLowerCase();
                    const message = (n.message || '').toLowerCase();
                    const sender = (n.sender_name || '').toLowerCase();
                    const phase = getNotificationPhase(n).toLowerCase();
                    return title.includes(q) || message.includes(q) || sender.includes(q) || phase.includes(q);
                });
            }

            // Update Tab active styling
            if (currentFilter === 'unread') {
                tabUnread.className = 'px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer notif-tab-active';
                tabAll.className = 'px-4 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer notif-tab-inactive';
            } else {
                tabAll.className = 'px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer notif-tab-active';
                tabUnread.className = 'px-4 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer notif-tab-inactive';
            }

            // Empty state check
            if (filtered.length === 0) {
                if (searchQuery.trim()) {
                    feedEl.innerHTML = `
                        <div class="notif-card-emerald rounded-2xl p-12 text-center flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full notif-icon-emerald flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold notif-title-main">No matches found</h3>
                            <p class="text-xs notif-subtext max-w-sm">No notifications found matching "${escapeHtml(searchQuery)}".</p>
                            <button type="button" id="btn-empty-clear-search" class="mt-2 px-4 py-2 rounded-xl text-xs font-bold notif-btn-emerald transition-all cursor-pointer shadow-xs">
                                Clear Search
                            </button>
                        </div>
                    `;
                    document.getElementById('btn-empty-clear-search')?.addEventListener('click', () => {
                        searchInput.value = '';
                        searchQuery = '';
                        searchClearBtn.classList.add('hidden');
                        renderFeed();
                    });
                } else if (currentFilter === 'unread' && notifications.length > 0) {
                    feedEl.innerHTML = `
                        <div class="notif-card-emerald rounded-2xl p-12 text-center flex flex-col items-center justify-center gap-3">
                            <div class="w-14 h-14 rounded-full notif-icon-emerald flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold notif-title-main">All caught up!</h3>
                            <p class="text-xs notif-subtext max-w-sm">You have zero unread notifications.</p>
                            <button type="button" id="btn-empty-view-all" class="mt-2 px-4 py-2 rounded-xl text-xs font-bold notif-btn-emerald transition-all cursor-pointer shadow-sm">
                                View Notification History (${notifications.length})
                            </button>
                        </div>
                    `;
                    document.getElementById('btn-empty-view-all')?.addEventListener('click', () => {
                        currentFilter = 'all';
                        renderFeed();
                    });
                } else {
                    feedEl.innerHTML = `
                        <div class="notif-card-emerald rounded-2xl p-12 text-center flex flex-col items-center justify-center gap-3">
                            <div class="w-14 h-14 rounded-full notif-icon-emerald flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold notif-title-main">Your inbox is empty</h3>
                            <p class="text-xs notif-subtext max-w-sm">No notifications found at this time.</p>
                        </div>
                    `;
                }
                return;
            }

            // Render matching the exact card design in the image with full dark & light mode support
            feedEl.innerHTML = filtered.map(item => {
                const typeStr = (item.type || '').toLowerCase();
                const titleStr = (item.title || '').toLowerCase();

                const isWarning = typeStr.includes('revok') || 
                                  typeStr.includes('return') || 
                                  typeStr.includes('disapprov') || 
                                  typeStr.includes('unsubmit') ||
                                  titleStr.includes('revok') ||
                                  titleStr.includes('return');

                let cardClasses = '';
                let iconClasses = '';
                let iconSvg = '';
                let titleClasses = '';
                let phaseBadgeClasses = '';
                let actionBtnStyle = '';

                if (isWarning) {
                    cardClasses = 'notif-card-amber';
                    iconClasses = 'notif-icon-amber';
                    iconSvg = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008z" /></svg>`;
                    titleClasses = 'notif-title-amber';
                    phaseBadgeClasses = 'notif-phase-amber';
                    actionBtnStyle = 'notif-btn-amber';
                } else {
                    cardClasses = 'notif-card-emerald';
                    iconClasses = 'notif-icon-emerald';
                    iconSvg = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>`;
                    titleClasses = 'notif-title-emerald';
                    phaseBadgeClasses = 'notif-phase-emerald';
                    actionBtnStyle = 'notif-btn-emerald';
                }

                // Format Message: Bold quoted document/folder names and sender name safely
                let messageHtml = escapeHtml(item.message);
                
                // Bold any quoted string: e.g. &quot;Untitled Evaluation (3)&quot;
                messageHtml = messageHtml.replace(/(&quot;.*?&quot;)/g, '<span class="font-bold notif-text-bold">$1</span>');

                // Bold sender name if present at the start of the message
                if (item.sender_name) {
                    const senderEscaped = escapeHtml(item.sender_name);
                    if (messageHtml.startsWith(senderEscaped)) {
                        messageHtml = `<span class="font-bold notif-text-bold">${senderEscaped}</span>` + messageHtml.substring(senderEscaped.length);
                    }
                }

                const phase = getNotificationPhase(item);
                const buttonLabel = getButtonLabel(item, isWarning);

                return `
                    <div class="notification-card rounded-2xl p-4 sm:px-5 sm:py-4.5 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-4 ${cardClasses}"
                         data-id="${item.id}"
                         data-link="${item.link || ''}">
                        
                        <!-- Left Icon & Middle Body -->
                        <div class="flex items-start sm:items-center gap-3.5 sm:gap-4 flex-1 min-w-0">
                            <!-- Status Circle -->
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center shrink-0 mt-0.5 sm:mt-0 shadow-xs ${iconClasses}">
                                ${iconSvg}
                            </div>

                            <!-- Text Content -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold tracking-tight ${titleClasses}">${escapeHtml(item.title)}</h4>
                                <p class="text-xs notif-text-body mt-1 leading-relaxed">${messageHtml}</p>
                                
                                <div class="mt-2.5 flex items-center gap-2 text-[11px] notif-text-meta font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        ${item.time_ago}
                                    </span>
                                    <span>•</span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider ${phaseBadgeClasses}">
                                        ${phase}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <button type="button" 
                                    class="btn-open-action px-5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs whitespace-nowrap active:scale-95 ${actionBtnStyle}"
                                    data-id="${item.id}"
                                    data-link="${item.link || ''}">
                                ${buttonLabel}
                            </button>

                            <button type="button" 
                                    class="btn-delete-card p-1 notif-dismiss-btn transition-colors cursor-pointer text-base font-bold shrink-0"
                                    title="Dismiss notification"
                                    data-id="${item.id}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            // Click listener on action button & card
            feedEl.querySelectorAll('.btn-open-action').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = btn.getAttribute('data-id');
                    const link = btn.getAttribute('data-link');

                    const targetItem = notifications.find(n => n.id == id);
                    if (targetItem && !targetItem.is_read) {
                        targetItem.is_read = true;
                        sendMarkRead(id);
                    }

                    if (link) {
                        window.location.href = link;
                    } else {
                        renderFeed();
                    }
                });
            });

            // Card click behavior (open rating if link exists)
            feedEl.querySelectorAll('.notification-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.btn-delete-card') || e.target.closest('.btn-open-action')) {
                        return;
                    }
                    const id = card.getAttribute('data-id');
                    const link = card.getAttribute('data-link');

                    const targetItem = notifications.find(n => n.id == id);
                    if (targetItem && !targetItem.is_read) {
                        targetItem.is_read = true;
                        sendMarkRead(id);
                    }

                    if (link) {
                        window.location.href = link;
                    } else {
                        renderFeed();
                    }
                });
            });

            // Delete / Dismiss button
            feedEl.querySelectorAll('.btn-delete-card').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = btn.getAttribute('data-id');
                    sendDelete(id);
                    notifications = notifications.filter(n => n.id != id);
                    renderFeed();
                });
            });
        }

        // AJAX Helper: mark single as read
        async function sendMarkRead(id) {
            try {
                const formData = new FormData();
                formData.append(getCsrfName(), getCsrfToken());
                await fetch(`<?= site_url('notifications/read/') ?>${id}`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (err) {
                console.warn(err);
            }
        }

        // AJAX Helper: delete single
        async function sendDelete(id) {
            try {
                const formData = new FormData();
                formData.append(getCsrfName(), getCsrfToken());
                await fetch(`<?= site_url('notifications/delete/') ?>${id}`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (err) {
                console.warn(err);
            }
        }

        // Filter tab triggers
        tabUnread?.addEventListener('click', () => {
            currentFilter = 'unread';
            renderFeed();
        });

        tabAll?.addEventListener('click', () => {
            currentFilter = 'all';
            renderFeed();
        });

        // Search inputs
        searchInput?.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            if (searchQuery.length > 0) {
                searchClearBtn.classList.remove('hidden');
            } else {
                searchClearBtn.classList.add('hidden');
            }
            renderFeed();
        });

        searchClearBtn?.addEventListener('click', () => {
            searchInput.value = '';
            searchQuery = '';
            searchClearBtn.classList.add('hidden');
            renderFeed();
        });

        // Global Mark All Read
        markAllBtn?.addEventListener('click', async () => {
            if (!notifications.some(n => !n.is_read)) return;
            try {
                const formData = new FormData();
                formData.append(getCsrfName(), getCsrfToken());
                await fetch('<?= site_url("notifications/read-all") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                notifications.forEach(n => n.is_read = true);
                renderFeed();
            } catch (err) {
                console.warn(err);
            }
        });

        // Global Clear All
        clearAllBtn?.addEventListener('click', async () => {
            if (notifications.length === 0) return;
            
            const confirmed = window.confirm('Are you sure you want to clear all notifications? This action cannot be undone.');
            if (!confirmed) return;

            try {
                const formData = new FormData();
                formData.append(getCsrfName(), getCsrfToken());
                await fetch('<?= site_url("notifications/clear-all") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                notifications = [];
                renderFeed();
            } catch (err) {
                console.warn(err);
            }
        });

        // Initial render
        renderFeed();
    });
</script>

<?= $this->endSection() ?>
