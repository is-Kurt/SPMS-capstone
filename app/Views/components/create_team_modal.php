<!--
    Create-team dialog, loaded via app_shell.php in the 'teams' context.
    Opened by #btn-create-team-modal; submit handling lives in
    public/assets/js/main/modals/createTeamModal.js, posting to
    Team::createShell() via AJAX then navigating to teams?team_id=... where
    the new team's member list can then be edited.
-->
<div id="modal-create-team" class="fixed inset-0 z-[120] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all m-4">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Create New Team</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Distribution List</p>

        <form id="form-create-team" action="<?= site_url('teams/create-shell') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Team Name</label>
                    <input type="text" name="name" placeholder="e.g., OVPAA Staff" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Description (Optional)</label>
                    <input type="text" name="description" placeholder="Who is in this list?" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button id="btn-close-create-team" type="button" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-create-team" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create & Select
                </button>
            </div>
        </form>
    </div>
</div>
