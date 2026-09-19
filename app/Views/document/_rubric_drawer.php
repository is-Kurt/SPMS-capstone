<!-- Mobile Drawer Backdrop Overlay -->
<div id="rubric-backdrop" onclick="toggleRubricDrawer(false)" 
     class="fixed inset-0 bg-black/60 z-40 transition-opacity lg:hidden print-hide" 
     style="display: none;"></div>

<style>
    @media screen and (max-width: 1023px) {
        #rubric-drawer {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: auto !important;
            width: 100% !important;
            max-width: 420px !important;
            min-width: 0 !important;
            z-index: 50 !important;
            box-shadow: -8px 0 25px rgba(0, 0, 0, 0.6) !important;
        }
    }
    .bsu-rubric-card {
        background-color: #16241d;
        border: 1px solid rgba(16, 185, 129, 0.2);
        transition: all 0.2s ease;
    }
    .bsu-rubric-card:hover {
        border-color: rgba(16, 185, 129, 0.4);
    }
</style>

<!-- Eye-Friendly CSC & BSU Scoring Rubric Reference Panel -->
<aside id="rubric-drawer" 
       class="h-full flex flex-col shrink-0 border-l border-emerald-900/30 transition-all duration-200 ease-out print-hide z-50 select-none"
       style="width: 440px; min-width: 360px; max-width: 34vw; background-color: #121c17; color: #cbd5e1; display: none;">
    
    <!-- Header -->
    <div class="px-5 py-4 border-b border-emerald-900/30 flex items-center justify-between shrink-0 bg-[#0e1713]">
        <div>
            <h3 class="text-sm font-bold text-slate-100 tracking-tight flex items-center gap-2">
                <span>Scoring Rubric Reference</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">BSU</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
                Sample Guide &amp; CSC MC No. 6, s. 2012
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

    <!-- Mode Toggle: BSU Sample Guide vs CSC Scales -->
    <div class="px-5 py-2.5 border-b border-emerald-900/30 bg-[#0f1914] flex items-center justify-between gap-2">
        <div class="inline-flex p-0.5 rounded-lg bg-emerald-950/80 border border-emerald-800/40 text-xs font-semibold w-full">
            <button type="button" id="tab-btn-bsu-guide" onclick="switchRubricDrawerView('bsu-guide')" 
                    class="flex-1 py-1.5 px-3 rounded-md text-center transition-all bg-emerald-700/80 text-white shadow-sm font-bold">
                BSU Sample Guide (7 Examples)
            </button>
            <button type="button" id="tab-btn-csc-scales" onclick="switchRubricDrawerView('csc-scales')" 
                    class="flex-1 py-1.5 px-3 rounded-md text-center transition-all text-slate-400 hover:text-slate-200 font-medium">
                CSC Core Scales
            </button>
        </div>
    </div>

    <!-- Filter Pills for CSC Scales (only shown when csc-scales active) -->
    <div id="csc-filter-bar" class="hidden px-5 py-2 border-b border-emerald-900/20 flex items-center gap-1.5 shrink-0 bg-[#0f1914]">
        <button type="button" onclick="scrollToRubricSection('all')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md bg-emerald-900/60 text-emerald-200 border border-emerald-700/50 transition-colors cursor-pointer" data-target="all">All</button>
        <button type="button" onclick="scrollToRubricSection('section-e')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md text-slate-400 hover:text-slate-200 hover:bg-white/5 border border-transparent transition-colors cursor-pointer" data-target="section-e">Efficiency</button>
        <button type="button" onclick="scrollToRubricSection('section-q')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md text-slate-400 hover:text-slate-200 hover:bg-white/5 border border-transparent transition-colors cursor-pointer" data-target="section-q">Quality</button>
        <button type="button" onclick="scrollToRubricSection('section-t')" class="rubric-filter-btn px-2.5 py-1 text-xs font-medium rounded-md text-slate-400 hover:text-slate-200 hover:bg-white/5 border border-transparent transition-colors cursor-pointer" data-target="section-t">Timeliness</button>
    </div>

    <!-- Scrollable Content -->
    <div id="rubric-content-scroll" class="flex-1 overflow-y-auto px-5 py-5 space-y-6 custom-scrollbar">

        <!-- ============================================================= -->
        <!-- VIEW 1: BSU SAMPLE GUIDE (7 Real-World Examples from Sheet)   -->
        <!-- ============================================================= -->
        <div id="view-bsu-guide" class="space-y-5">
            <div class="p-3 rounded-lg bg-amber-950/30 border border-amber-500/30 text-xs text-amber-200/90 leading-relaxed">
                <span class="font-bold text-amber-300">💡 Institutional Guideline:</span> 
                Always attach your rubrics when submitting. The targets in your rubrics should match the targets in your DPCR/IPCR/IPERF.
            </div>

            <!-- Example 1: Database Recording -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 1 • Monitoring Database</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            100% of submitted/collected documents recorded within 5 working days
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5 font-sans">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 1–2 working days from receipt &bull; <strong class="text-slate-200">E:</strong> 100% recorded</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 3–4 working days from receipt</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 5 working days from receipt</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 6–7 working days &bull; <strong class="text-slate-200">E:</strong> Less than 100% recorded</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-rose-400 shrink-0 w-4">1:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> >8 working days &bull; <strong class="text-slate-200">Q:</strong> N/A</span>
                    </div>
                </div>
            </div>

            <!-- Example 2: Issuances Routing -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 2 • Issuances &amp; Routing</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            Issued/Routed 100% of approved issuances to personnel within 3 working days
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> within 1 working day &bull; <strong class="text-slate-200">E:</strong> 100% routed</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> within 2 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> within 3 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> within 4 working days &bull; <strong class="text-slate-200">E:</strong> less than 100%</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-rose-400 shrink-0 w-4">1:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> >5 working days &bull; <strong class="text-slate-200">Q:</strong> N/A</span>
                    </div>
                </div>
            </div>

            <!-- Example 3: Requested Documents Submission -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 3 • Requested Documents</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            100% of requested documents submitted in 5 working days (max 2 revisions)
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> No revision &bull; <strong class="text-slate-200">T:</strong> 1–2 working days &bull; <strong class="text-slate-200">E:</strong> 100% prepared &amp; released</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 1 revision &bull; <strong class="text-slate-200">T:</strong> 3–4 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 2 revisions &bull; <strong class="text-slate-200">T:</strong> 5 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 3 revisions &bull; <strong class="text-slate-200">T:</strong> 6–7 working days &bull; <strong class="text-slate-200">E:</strong> &lt;100%</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-rose-400 shrink-0 w-4">1:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 4 revisions &bull; <strong class="text-slate-200">T:</strong> >8 working days</span>
                    </div>
                </div>
            </div>

            <!-- Example 4: Certificate Requests -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 4 • Certificates &amp; Transcripts</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            100% requests for certificates issued in 3 days (max 2 revisions)
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> No revision &bull; <strong class="text-slate-200">T:</strong> 1 working day &bull; <strong class="text-slate-200">E:</strong> 100% prepared</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 1 revision &bull; <strong class="text-slate-200">T:</strong> 2 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 2 revisions &bull; <strong class="text-slate-200">T:</strong> 3 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 3 revisions &bull; <strong class="text-slate-200">T:</strong> 4 working days &bull; <strong class="text-slate-200">E:</strong> &lt;100%</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-rose-400 shrink-0 w-4">1:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 4+ revisions &bull; <strong class="text-slate-200">T:</strong> >5 working days</span>
                    </div>
                </div>
            </div>

            <!-- Example 5: Minutes of Meetings -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 5 • Minutes of Meetings</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            100% Minutes of Meetings submitted in 3 days (with at most 3 revisions)
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 1 revision &bull; <strong class="text-slate-200">T:</strong> 1 working day &bull; <strong class="text-slate-200">E:</strong> 100% submitted</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 2 revisions &bull; <strong class="text-slate-200">T:</strong> 2 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 3 revisions &bull; <strong class="text-slate-200">T:</strong> 3 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 4 revisions &bull; <strong class="text-slate-200">T:</strong> 4 working days &bull; <strong class="text-slate-200">E:</strong> &lt;100%</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-rose-400 shrink-0 w-4">1:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> >4 revisions &bull; <strong class="text-slate-200">T:</strong> >4 working days</span>
                    </div>
                </div>
            </div>

            <!-- Example 6: Email & Messenger Queries -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 6 • Inquiries &amp; Communications</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            100% queries on email &amp; messenger responded to within 3 working days
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> Within the day &bull; <strong class="text-slate-200">E:</strong> 100% responded</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 1 working day</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 2 working days</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 3 working days &bull; <strong class="text-slate-200">E:</strong> less than 100%</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-rose-400 shrink-0 w-4">1:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">T:</strong> 4 or more working days &bull; <strong class="text-slate-200">Q:</strong> N/A</span>
                    </div>
                </div>
            </div>

            <!-- Example 7: Reports & Post-training Liquidation -->
            <div class="bsu-rubric-card rounded-xl p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2 border-b border-emerald-900/40 pb-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Example 7 • Reports &amp; Liquidation</span>
                        <h4 class="text-xs font-bold text-slate-100 mt-0.5">
                            Accomplishment Report &amp; liquidation in 10 working days (with 3 revisions)
                        </h4>
                    </div>
                </div>
                <div class="text-[11px] space-y-1.5">
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-emerald-300 shrink-0 w-4">5:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 1 revision &bull; <strong class="text-slate-200">T:</strong> 2–4 working days after training</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-sky-300 shrink-0 w-4">4:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 2 revisions &bull; <strong class="text-slate-200">T:</strong> 5–7 working days after training</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-amber-300 shrink-0 w-4">3:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 3 revisions &bull; <strong class="text-slate-200">T:</strong> 8–10 working days after training</span>
                    </div>
                    <div class="flex items-start gap-2 bg-black/20 p-2 rounded border border-emerald-900/30">
                        <span class="font-bold text-orange-300 shrink-0 w-4">2:</span>
                        <span class="text-slate-300"><strong class="text-slate-200">Q:</strong> 4 revisions &bull; <strong class="text-slate-200">T:</strong> 11–13 working days after training</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- VIEW 2: CSC CORE SCALES (Efficiency, Quality, Timeliness)     -->
        <!-- ============================================================= -->
        <div id="view-csc-scales" class="hidden space-y-8">
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
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-300">5 — Outstanding</span>
                            <span class="text-xs font-medium text-emerald-200/90">&ge; 130% of target</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Exceeds planned commitments by 30% or more.
                        </p>
                    </div>

                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-sky-300">4 — Very Satisfactory</span>
                            <span class="text-xs font-medium text-sky-200/90">115% – 129% of target</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Exceeds planned commitments by 15% to 29%.
                        </p>
                    </div>

                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-300">3 — Satisfactory</span>
                            <span class="text-xs font-medium text-amber-200/90">100% – 114% (Met)</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Meets target commitment. Standard acceptable performance.
                        </p>
                    </div>

                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-orange-300">2 — Unsatisfactory</span>
                            <span class="text-xs font-medium text-orange-200/90">51% – 99% of target</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Falls short of target commitment by half to nearly full.
                        </p>
                    </div>

                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-400">1 — Poor</span>
                            <span class="text-xs font-medium text-rose-300/90">&le; 50% of target</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Fails to meet commitments by 50% or more.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 2. QUALITY (Q) -->
            <section id="section-q" class="space-y-4">
                <div class="flex items-center justify-between border-b border-emerald-900/30 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded flex items-center justify-center bg-emerald-900/50 text-emerald-300 font-bold text-xs border border-emerald-700/40">Q</span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">
                            Quality (Excellence &amp; Accuracy)
                        </h4>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-emerald-300">5 — Outstanding</span>
                        <p class="text-xs text-slate-300 leading-relaxed">No errors or revisions; exceeds standards of technical excellence.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-sky-300">4 — Very Satisfactory</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Minor defects with at most 1 revision; high degree of accuracy.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-amber-300">3 — Satisfactory</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Meets basic quality requirements; with 2 minor revisions.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-orange-300">2 — Unsatisfactory</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Notable errors needing 3 revisions; substantial corrections required.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-rose-400">1 — Poor</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Frequent errors, 4+ revisions or total rejection.</p>
                    </div>
                </div>
            </section>

            <!-- 3. TIMELINESS (T) -->
            <section id="section-t" class="space-y-4">
                <div class="flex items-center justify-between border-b border-emerald-900/30 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded flex items-center justify-center bg-emerald-900/50 text-emerald-300 font-bold text-xs border border-emerald-700/40">T</span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">
                            Timeliness (Speed &amp; Punctuality)
                        </h4>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-emerald-300">5 — Outstanding</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Completed significantly ahead of deadline (within 1–2 days).</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-sky-300">4 — Very Satisfactory</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Completed ahead of scheduled timeline.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-amber-300">3 — Satisfactory</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Completed exactly on the prescribed target deadline.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-orange-300">2 — Unsatisfactory</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Delayed past deadline by several working days.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-[#16241d] border border-emerald-900/30 space-y-1">
                        <span class="text-xs font-bold text-rose-400">1 — Poor</span>
                        <p class="text-xs text-slate-300 leading-relaxed">Severely overdue or not submitted.</p>
                    </div>
                </div>
            </section>
        </div>

    </div>
</aside>

<script>
    function toggleRubricDrawer(forceState) {
        const drawer = document.getElementById('rubric-drawer');
        const backdrop = document.getElementById('rubric-backdrop');
        const btn = document.getElementById('btn-toggle-rubric');
        if (!drawer) return;

        const isHidden = (drawer.style.display === 'none' || drawer.style.display === '');
        const shouldOpen = forceState !== undefined ? forceState : isHidden;

        if (shouldOpen) {
            drawer.style.display = 'flex';
            if (window.innerWidth < 1024 && backdrop) {
                backdrop.style.display = 'block';
            }
            btn?.classList.add('ring-1', 'ring-emerald-400', 'bg-emerald-600/30');
        } else {
            drawer.style.display = 'none';
            if (backdrop) {
                backdrop.style.display = 'none';
            }
            btn?.classList.remove('ring-1', 'ring-emerald-400', 'bg-emerald-600/30');
        }
    }

    function switchRubricDrawerView(view) {
        const viewBsu = document.getElementById('view-bsu-guide');
        const viewCsc = document.getElementById('view-csc-scales');
        const btnBsu = document.getElementById('tab-btn-bsu-guide');
        const btnCsc = document.getElementById('tab-btn-csc-scales');
        const filterBar = document.getElementById('csc-filter-bar');

        if (view === 'bsu-guide') {
            viewBsu?.classList.remove('hidden');
            viewCsc?.classList.add('hidden');
            filterBar?.classList.add('hidden');

            btnBsu?.classList.add('bg-emerald-700/80', 'text-white', 'shadow-sm', 'font-bold');
            btnBsu?.classList.remove('text-slate-400');

            btnCsc?.classList.remove('bg-emerald-700/80', 'text-white', 'shadow-sm', 'font-bold');
            btnCsc?.classList.add('text-slate-400');
        } else {
            viewBsu?.classList.add('hidden');
            viewCsc?.classList.remove('hidden');
            filterBar?.classList.remove('hidden');

            btnCsc?.classList.add('bg-emerald-700/80', 'text-white', 'shadow-sm', 'font-bold');
            btnCsc?.classList.remove('text-slate-400');

            btnBsu?.classList.remove('bg-emerald-700/80', 'text-white', 'shadow-sm', 'font-bold');
            btnBsu?.classList.add('text-slate-400');
        }
    }

    // Keep mobile backdrop in sync on window resize
    window.addEventListener('resize', () => {
        const backdrop = document.getElementById('rubric-backdrop');
        const drawer = document.getElementById('rubric-drawer');
        if (backdrop && drawer) {
            if (window.innerWidth >= 1024 || drawer.style.display !== 'flex') {
                backdrop.style.display = 'none';
            } else if (drawer.style.display === 'flex') {
                backdrop.style.display = 'block';
            }
        }
    });

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
