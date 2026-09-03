<!-- Eye-Friendly Minimalist CSC Scoring Rubric Reference Panel -->
<aside id="rubric-drawer" 
       class="h-full flex flex-col shrink-0 border-l border-emerald-900/30 transition-all duration-200 ease-out print-hide z-20 select-none"
       style="width: 420px; min-width: 340px; max-width: 32vw; background-color: #121c17; color: #cbd5e1; display: none;">
    
    <!-- Gentle Header -->
    <div class="px-6 py-4 border-b border-emerald-900/30 flex items-center justify-between shrink-0 bg-[#0e1713]">
        <div>
            <h3 class="text-sm font-semibold text-slate-100 tracking-tight">
                Scoring Rubric Reference
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
                Civil Service Commission MC No. 6, s. 2012
            </p>
        </div>

        <button type="button" onclick="toggleRubricDrawer(false)" 
                class="w-8 h-8 rounded-lg hover:bg-white/10 text-slate-400 hover:text-slate-200 flex items-center justify-center transition-colors cursor-pointer"
                title="Close (Esc)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Soft Filter Pills -->
    <div class="px-6 py-2.5 border-b border-emerald-900/20 flex items-center gap-1.5 shrink-0 bg-[#0f1914]">
        <button type="button" onclick="scrollToRubricSection('all')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md bg-emerald-900/60 text-emerald-200 border border-emerald-700/50 transition-colors cursor-pointer" data-target="all">All</button>
        <button type="button" onclick="scrollToRubricSection('section-e')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md text-slate-400 hover:text-slate-200 hover:bg-white/5 border border-transparent transition-colors cursor-pointer" data-target="section-e">Efficiency</button>
        <button type="button" onclick="scrollToRubricSection('section-q')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md text-slate-400 hover:text-slate-200 hover:bg-white/5 border border-transparent transition-colors cursor-pointer" data-target="section-q">Quality</button>
        <button type="button" onclick="scrollToRubricSection('section-t')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md text-slate-400 hover:text-slate-200 hover:bg-white/5 border border-transparent transition-colors cursor-pointer" data-target="section-t">Timeliness</button>
    </div>

    <!-- Calm, Comfortable Scrollable Content -->
    <div id="rubric-content-scroll" class="flex-1 overflow-y-auto px-6 py-6 space-y-8 custom-scrollbar">

        <!-- 1. EFFICIENCY (E) -->
        <section id="section-e" class="space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-900/30 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded flex items-center justify-center bg-emerald-900/50 text-emerald-300 font-bold text-xs border border-emerald-700/40">E</span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">
                        Efficiency (Quantity &amp; Targets)
                    </h4>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">
                    (Actual ÷ Target) × 100%
                </span>
            </div>

            <div class="space-y-3">
                <!-- 5 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-300">5 — Outstanding</span>
                        <span class="text-xs font-medium text-emerald-200/90">&ge; 130% of target</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Exceeds planned commitments by 30% or more.
                    </p>
                </div>

                <!-- 4 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-sky-300">4 — Very Satisfactory</span>
                        <span class="text-xs font-medium text-sky-200/90">115% – 129% of target</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Exceeds planned commitments by 15% to 29%.
                    </p>
                </div>

                <!-- 3 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-300">3 — Satisfactory</span>
                        <span class="text-xs font-medium text-amber-200/90">100% – 114% (Met)</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Meets target commitment. Standard acceptable performance.
                    </p>
                </div>

                <!-- 2 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-orange-300">2 — Unsatisfactory</span>
                        <span class="text-xs font-medium text-orange-200/90">51% – 99% of target</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Target commitments were not fully achieved.
                    </p>
                </div>

                <!-- 1 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-300">1 — Poor</span>
                        <span class="text-xs font-medium text-rose-200/90">&le; 50% of target</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Critical failure to deliver planned outputs.
                    </p>
                </div>
            </div>
        </section>

        <!-- 2. QUALITY (Q) -->
        <section id="section-q" class="space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-900/30 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded flex items-center justify-center bg-sky-950/60 text-sky-300 font-bold text-xs border border-sky-800/40">Q</span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">
                        Quality (Accuracy &amp; Standards)
                    </h4>
                </div>
                <span class="text-[11px] text-slate-400">
                    Accuracy &amp; Satisfaction
                </span>
            </div>

            <div class="space-y-3">
                <!-- 5 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-300">5 — Outstanding</span>
                        <span class="text-xs font-medium text-emerald-200/90">0 Revisions</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        No errors or revisions required; client satisfaction &ge; 96%.
                    </p>
                </div>

                <!-- 4 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-sky-300">4 — Very Satisfactory</span>
                        <span class="text-xs font-medium text-sky-200/90">1–2 Minor</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        1 to 2 minor revisions only; satisfaction rate 86% to 95%.
                    </p>
                </div>

                <!-- 3 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-300">3 — Satisfactory</span>
                        <span class="text-xs font-medium text-amber-200/90">Standard Met</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Meets agency baseline standards; satisfaction rate 75% to 85%.
                    </p>
                </div>

                <!-- 2 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-orange-300">2 — Unsatisfactory</span>
                        <span class="text-xs font-medium text-orange-200/90">Major Rework</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Substantial errors noted; satisfaction rate 60% to 74%.
                    </p>
                </div>

                <!-- 1 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-300">1 — Poor</span>
                        <span class="text-xs font-medium text-rose-200/90">Substandard</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Output rejected or substandard; satisfaction below 60%.
                    </p>
                </div>
            </div>
        </section>

        <!-- 3. TIMELINESS (T) -->
        <section id="section-t" class="space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-900/30 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded flex items-center justify-center bg-amber-950/60 text-amber-300 font-bold text-xs border border-amber-800/40">T</span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">
                        Timeliness (Deadline Adherence)
                    </h4>
                </div>
                <span class="text-[11px] text-slate-400">
                    Delivery Cutoff
                </span>
            </div>

            <div class="space-y-3">
                <!-- 5 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-300">5 — Outstanding</span>
                        <span class="text-xs font-medium text-emerald-200/90">&ge; 5 Days Ahead</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Completed and delivered 5 or more working days ahead of cutoff.
                    </p>
                </div>

                <!-- 4 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-sky-300">4 — Very Satisfactory</span>
                        <span class="text-xs font-medium text-sky-200/90">1–4 Days Ahead</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Completed and delivered 1 to 4 working days before cutoff.
                    </p>
                </div>

                <!-- 3 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-300">3 — Satisfactory</span>
                        <span class="text-xs font-medium text-amber-200/90">On Deadline</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Completed and delivered on the exact scheduled deadline.
                    </p>
                </div>

                <!-- 2 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-orange-300">2 — Unsatisfactory</span>
                        <span class="text-xs font-medium text-orange-200/90">1–5 Days Late</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Delivered 1 to 5 working days after the scheduled cutoff.
                    </p>
                </div>

                <!-- 1 -->
                <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-300">1 — Poor</span>
                        <span class="text-xs font-medium text-rose-200/90">&gt; 5 Days Late</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Delivered more than 5 working days late, or not submitted.
                    </p>
                </div>
            </div>
        </section>

        <!-- Calm Footer Note -->
        <div class="pt-6 border-t border-emerald-900/20 text-center">
            <span class="text-xs text-slate-400 font-medium">
                BSU SPMS &middot; CSC MC No. 6, s. 2012
            </span>
        </div>

    </div>

</aside>

<script>
    function toggleRubricDrawer(forceState) {
        const drawer = document.getElementById('rubric-drawer');
        const btn = document.getElementById('btn-toggle-rubric');
        if (!drawer) return;

        const isCurrentlyOpen = (drawer.style.display === 'flex');
        const shouldOpen = (typeof forceState === 'boolean') ? forceState : !isCurrentlyOpen;

        if (shouldOpen) {
            drawer.style.display = 'flex';
            btn?.classList.add('ring-1', 'ring-emerald-400', 'bg-emerald-600/30');
        } else {
            drawer.style.display = 'none';
            btn?.classList.remove('ring-1', 'ring-emerald-400', 'bg-emerald-600/30');
        }
    }

    function scrollToRubricSection(id) {
        const container = document.getElementById('rubric-content-scroll');
        if (!container) return;

        document.querySelectorAll('.rubric-filter-btn').forEach(btn => {
            btn.classList.remove('bg-emerald-900/60', 'text-emerald-200', 'border-emerald-700/50');
            btn.classList.add('text-slate-400', 'border-transparent');
        });

        const activeBtn = document.querySelector(`.rubric-filter-btn[data-target="${id}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-400', 'border-transparent');
            activeBtn.classList.add('bg-emerald-900/60', 'text-emerald-200', 'border-emerald-700/50');
        }

        if (id === 'all') {
            container.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            toggleRubricDrawer(false);
        }
    });
</script>
