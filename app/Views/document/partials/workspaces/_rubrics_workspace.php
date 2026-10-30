        <!-- SPMS DIGITAL RUBRICS MATRIX WORKSPACE -->
        <div id="spms-rubrics-workspace" class="hidden w-full h-full overflow-y-auto p-2 sm:p-6 lg:p-8 flex flex-col items-center custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
            <div class="w-full max-w-[1280px] flex flex-col gap-6 print:w-full print:block">
                
                <!-- Interactive Digital Rubrics Matrix Section -->
                <div class="space-y-5">
                    <!-- Rubric Matrix Header & Controls Card -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-surface border border-surface-border shadow-sm space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-surface-border pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <h2 class="text-sm sm:text-base font-black uppercase tracking-wider text-text">
                                        Standards or Rating Matrix per Success Indicator
                                    </h2>
                                </div>
                                <p class="text-xs text-text-muted mt-1">
                                    Define measurable scoring criteria (5 to 1) for Quality (Q), Timeliness (T), and Efficiency (E) per output.
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2.5 shrink-0">
                                <a href="<?= site_url('document/' . $doc['id'] . '/export-rubric') ?>" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 transition-all"
                                   title="Download official Excel rubric sheet with your current deliverables pre-filled in Column A">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span>Download Template (.xlsx)</span>
                                </a>
                                <?php if ($canEditTargets): ?>
                                <div class="relative group inline-flex items-center">
                                    <button type="button" 
                                            id="btn-rubrics-save"
                                            onclick="saveDocument(true)" 
                                            title="Save (Ctrl + S)"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow transition-all active:scale-95 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                        </svg>
                                        <span id="btn-rubrics-save-text">Save</span>
                                    </button>
                                    
                                    <!-- Hover Tooltip showing (Ctrl + S) -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-900 text-white shadow-xl pointer-events-none whitespace-nowrap z-50">
                                        <span>Save changes</span>
                                        <span class="text-zinc-400 font-mono text-[9px] bg-zinc-800 px-1 py-0.5 rounded border border-zinc-700">(Ctrl + S)</span>
                                        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-zinc-900 rotate-45"></div>
                                    </div>
                                </div>
                                <span id="rubrics-save-status" class="text-[10px] uppercase tracking-widest font-bold transition-all"></span>
                                <?php else: ?>
                                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-surface-border/40 text-text-muted">
                                    Read-Only
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Deliverables Quick Navigation & Progress -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-0.5">
                            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 text-xs" id="rubric-quick-jump-pills">
                                <!-- Populated dynamically by JS -->
                            </div>

                            <div class="flex items-center gap-2 shrink-0 text-xs self-end sm:self-auto flex-wrap">
                                <span id="rubric-overall-progress" class="font-bold text-text-muted text-[11px]"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Full Sheet View Container (Permanent Single View) -->
                    <div id="rubrics-sheet-container" class="overflow-x-auto rounded-2xl border border-surface-border bg-surface shadow-sm p-4">
                        <table id="digital-rubrics-table" class="w-full text-left border-collapse" style="font-size: 11px; min-width: 900px; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 32%; max-width: 38%;"> <!-- Function column limited to max ~32-38% (well below half) -->
                                <col style="width: 11%;"> <!-- Rating column -->
                                <col style="width: 19%;"> <!-- Q column -->
                                <col style="width: 19%;"> <!-- T column -->
                                <col style="width: 19%;"> <!-- E column -->
                            </colgroup>
                            <thead>
                                <tr class="bg-surface-border/40 text-text border-b border-surface-border">
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 32%; max-width: 38%;">
                                        OFFICE PPA / MAJOR FINAL OUTPUT / EXPECTED OUTPUTS
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs text-center" style="width: 11%;">
                                        Rating
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 19%;">
                                        Q (Quality)
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 19%;">
                                        T (Timeliness)
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 19%;">
                                        E (Efficiency)
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="digital-rubrics-tbody">
                                <!-- Populated dynamically by JS with Category Groups -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

