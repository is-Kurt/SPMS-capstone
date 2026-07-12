<!--
    Generic confirm/alert dialog - replaces native confirm()/alert() app-wide.
    Rendered once in layouts/main.php. Driven entirely by public/assets/js/main/modals/confirmModal.js,
    which exposes window.appConfirm()/window.appAlert() and fills in the title/message/icon/button
    color below based on the `variant` option (danger/warning/info) passed by the caller.
-->
<div id="confirm-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">

    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-[380px] p-8 border border-zinc-200 dark:border-zinc-800">

        <div class="text-center mb-6">
            <div id="confirm-modal-icon-wrap" class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4 ring-4">
                <svg id="confirm-modal-icon" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"></svg>
            </div>
            <h2 id="confirm-modal-title" class="text-xl font-black tracking-tight text-text">Are you sure?</h2>
            <p id="confirm-modal-message" class="text-xs text-text-muted mt-2 px-2 leading-relaxed font-medium"></p>
        </div>

        <div class="flex flex-col gap-2 mt-8">
            <button id="btn-confirm-ok"
                class="w-full text-white font-bold py-3.5 rounded-xl shadow-lg transition-all text-sm cursor-pointer active:scale-[0.98]">
                Confirm
            </button>
            <button id="btn-confirm-cancel"
                class="w-full text-xs font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 py-2 transition-colors cursor-pointer">
                Cancel
            </button>
        </div>
    </div>
</div>
