<div id="share-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-sm">Share Document</h2>
            <button onclick="closeShareModal()" class="text-sub-text hover:text-red-500 text-lg">&times;</button>
        </div>

        <div class="flex gap-2 mb-4">
            <input type="email" id="share-email-input" placeholder="Search by email..."
                class="flex-1 border border-main-border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1">
            <button onclick="searchUser()" class="bg-blue-600 text-white text-sm px-4 py-2 rounded">
                Search
            </button>
        </div>

        <div id="share-search-result" class="hidden items-center justify-between py-2 px-3 bg-gray-50 dark:bg-zinc-800 rounded mb-4">
            <span id="share-user-label" class="text-sm"></span>
            <button onclick="confirmShare()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded">
                Share
            </button>
        </div>

        <p id="share-error" class="hidden text-xs text-red-500 mb-2"></p>
    </div>
</div>