<!--
    BSU SPMS Faculty, Supervisor & Evaluator Step-by-Step Guide
    Plain-English, role-aware, jargon-free guide accessible across the entire system
-->
<div id="userGuideModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeUserGuideModal()">
    <div class="spms-modal-dialog">
        <!-- Header -->
        <div class="spms-modal-header">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        BSU SPMS &bull; Quick Step-by-Step Guide
                    </span>
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium hidden sm:inline">&bull; 2026 Edition</span>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-zinc-900 dark:text-white m-0 tracking-tight">
                    How to Complete Your Evaluation Paper
                </h3>
            </div>
            <button type="button" onclick="closeUserGuideModal()" class="spms-modal-btn-close" title="Close Guide (Esc)" aria-label="Close Guide">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Quick Role Jump Pill Bar -->
        <div class="spms-guide-role-bar">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 shrink-0">
                Quick Jump:
            </span>
            <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-0.5">
                <button type="button" onclick="showGuideStep(1)" class="spms-guide-role-btn" title="Jump to Teacher & Staff Target Setting">
                    🎓 Faculty &amp; Staff (IPCR/IPERF)
                </button>
                <button type="button" onclick="showGuideStep(2)" class="spms-guide-role-btn" title="Jump to Supervisor Approvals & Cascading">
                    👔 Deans &amp; Chairs (DPCR/Supervisors)
                </button>
                <button type="button" onclick="showGuideStep(3)" class="spms-guide-role-btn" title="Jump to MOV & Cloud Evidence">
                    📁 Evidence &amp; Google Drive
                </button>
                <button type="button" onclick="showGuideStep(6)" class="spms-guide-role-btn spms-guide-role-btn-faq" title="Jump to Top Gotchas & FAQs">
                    ❓ Gotchas &amp; FAQs
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="spms-modal-body custom-scrollbar">
            
            <!-- 6 Simple Tabs -->
            <div class="spms-guide-tabs">
                <button type="button" id="guide-tab-1" class="spms-guide-tab active" onclick="showGuideStep(1)">
                    <span class="spms-guide-tab-badge">1</span>
                    <span class="spms-guide-tab-title">Set Targets</span>
                </button>
                <button type="button" id="guide-tab-2" class="spms-guide-tab" onclick="showGuideStep(2)">
                    <span class="spms-guide-tab-badge">2</span>
                    <span class="spms-guide-tab-title">Approvals</span>
                </button>
                <button type="button" id="guide-tab-3" class="spms-guide-tab" onclick="showGuideStep(3)">
                    <span class="spms-guide-tab-badge">3</span>
                    <span class="spms-guide-tab-title">Attach Proof</span>
                </button>
                <button type="button" id="guide-tab-4" class="spms-guide-tab" onclick="showGuideStep(4)">
                    <span class="spms-guide-tab-badge">4</span>
                    <span class="spms-guide-tab-title">Rate &amp; Score</span>
                </button>
                <button type="button" id="guide-tab-5" class="spms-guide-tab" onclick="showGuideStep(5)">
                    <span class="spms-guide-tab-badge">5</span>
                    <span class="spms-guide-tab-title">Export Excel</span>
                </button>
                <button type="button" id="guide-tab-6" class="spms-guide-tab" onclick="showGuideStep(6)">
                    <span class="spms-guide-tab-badge">6</span>
                    <span class="spms-guide-tab-title">Quick FAQs</span>
                </button>
            </div>

            <!-- SLIDE 1: SET TARGETS -->
            <div id="guide-slide-1" class="spms-guide-slide active">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Start of Semester &bull; Step 1</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Write Down What You Commit to Do</h4>
                    <p class="spms-guide-hero-desc">
                        At the beginning of the semester or cycle, list your major duties, set clear output numbers, and make sure your category percentage weights add up to 100%.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <span>What to List (IPCR vs IPERF)</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Faculty (IPCR):</strong> Organize your commitments into Core Functions (teaching/instruction), Strategic Priorities (research/extension), and Support Duties.</li>
                            <li><strong>Contract &amp; JO Staff (IPERF):</strong> Starts clean with blank rows &mdash; type in your specific contract duties, office PPAs, and expected outputs.</li>
                            <li><strong>Category Weights (100%):</strong> Enter your category percentage weights (e.g., Core 70%, Support 30%). They must total exactly <strong>100%</strong>.</li>
                            <li><strong>Put a Number on It:</strong> Set measurable goals (e.g., "Teach 4 subjects, 120 students", "Process 50 requests within 24 hours").</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span>Submitting &amp; Auto-Save</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Auto-Save is Active:</strong> The system automatically saves your draft inputs as you type, so you never lose work if your browser closes.</li>
                            <li><strong>Click Submit Targets:</strong> When your matrix and weights are complete, click the green <em>Submit Targets</em> button at the top right.</li>
                            <li><strong>Locked While Under Review:</strong> Once submitted, your paper is locked against edits while your Department Chair or Dean reviews it.</li>
                            <li><strong>Need Changes?</strong> If adjustments are needed, your supervisor clicks <em>Return for Revision</em> with remarks so you can revise and resubmit.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Pro Tip:</strong> Check your department's approved DPCR before writing your IPCR. Your personal commitments must support what your department committed to achieve this term!
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: GET APPROVAL & TEAM WORKFLOW -->
            <div id="guide-slide-2" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Approval &bull; Step 2</span>
                    </div>
                    <h4 class="spms-guide-hero-title">How Approvals &amp; Cascading Work</h4>
                    <p class="spms-guide-hero-desc">
                        Goals flow from top leadership down to individual faculty. Superiors review, calibrate, and lock in targets before the semester's work begins.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span>For Teachers &amp; Staff (Ratees)</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Review Queue:</strong> After clicking submit, your document lands directly in your supervisor's review queue.</li>
                            <li><strong>Instant Notifications:</strong> You will receive an in-app notification the moment your supervisor approves or returns your paper.</li>
                            <li><strong>Targets Locked:</strong> Once approved, your semester goals are locked in. You are now ready to begin executing and gathering proof!</li>
                            <li><strong>Check Remarks:</strong> If returned, open your matrix and look at the supervisor remarks and PTWG notes to see what to revise.</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <span>For Deans &amp; Department Chairs (Supervisors)</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Cascading Hierarchy:</strong> Deans must have collegiate DPCR approved first before cascading to Chairs; Chairs must have their DPCR approved before distributing to faculty.</li>
                            <li><strong>Distribute to Team:</strong> Use the right-side Team / Distribution drawer to issue semester papers to all department members.</li>
                            <li><strong>One-Click Approval:</strong> If the member's targets and weights look realistic, click <em>Approve Targets</em>.</li>
                            <li><strong>Return for Revision:</strong> If goals are missing measures or overloaded, click <em>Return for Revision</em> and leave specific notes.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">PTWG Calibration:</strong> The Performance Technical Working Group (PTWG) can stamp official calibration guidance using the quick <code class="px-1 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 text-amber-700 dark:text-amber-300 font-mono text-[11px]">+ [PTWG]</code> button in review remarks.
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: ATTACH PROOF & CLOUD EVIDENCE -->
            <div id="guide-slide-3" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>During the Semester &bull; Step 3</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Attach Proof of Your Accomplishments</h4>
                    <p class="spms-guide-hero-desc">
                        As you finish tasks throughout the semester, attach verifiable Means of Verification (MOV) proof &mdash; either by uploading files or pasting Google Drive cloud links.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </div>
                            <span>How to Upload &amp; Link Proof</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Click the Paperclip:</strong> On any deliverable row in your table, click the small paperclip icon in the MOV column.</li>
                            <li><strong>Local File Uploads:</strong> Upload PDFs, images (PNG, JPG), Word docs (`.docx`), or spreadsheets (`.xlsx`).</li>
                            <li><strong>Google Drive &amp; Cloud Links:</strong> Paste sharing links from <strong>Google Drive, OneDrive, or Dropbox</strong>. The system detects the provider badge and provides an in-browser preview!</li>
                            <li><strong>Upload as You Go:</strong> Don't wait until the final clearance week &mdash; attach your proof as soon as you finish each activity!</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span>Examples of Good MOVs</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>For Teaching:</strong> Approved course syllabi, signed class grading sheets, student evaluation summaries, and sample instructional modules.</li>
                            <li><strong>For Research &amp; Extension:</strong> Published papers, acceptance letters, training certificates, signed attendance sheets, or photos of community service.</li>
                            <li><strong>For Office Work:</strong> Signed routing slips, processed purchase requests, travel orders, finished reports, and completed time logs.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Large Files?</strong> If your course portfolio or video demonstration is too big to upload directly, upload it to your BSU Google Drive and paste the shareable link in the MOV modal. It opens instantly for your evaluator!
                    </div>
                </div>
            </div>

            <!-- SLIDE 4: RATE YOURSELF & SCORING -->
            <div id="guide-slide-4" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>End of Semester &bull; Step 4</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Record Accomplishments &amp; Score Ratings</h4>
                    <p class="spms-guide-hero-desc">
                        At the end of the term, type in what you actually achieved and grade each task on Quality (Q), Efficiency (E), and Timeliness (T) using the CSC 1 to 5 scale.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </div>
                            <span>What the Scores Mean (1 to 5)</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>5 &bull; Outstanding:</strong> Exceeded agreed targets by at least 30% with zero defects and exceptional quality.</li>
                            <li><strong>4 &bull; Very Satisfactory:</strong> Met 100% of agreed commitments on or ahead of time with high quality.</li>
                            <li><strong>3 &bull; Satisfactory:</strong> Met the standard requirements and finished baseline targets.</li>
                            <li><strong>2 or 1 &bull; Needs Work:</strong> Significant portions of committed targets were not completed.</li>
                            <li><strong>Dimension Gating (N/A):</strong> If Efficiency or Timeliness doesn't apply to a task, select <strong>N/A</strong> &mdash; the system automatically excludes it from the average!</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            </div>
                            <span>Scoring Rules &amp; Safeguards</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Math is 100% Automatic:</strong> Row averages, weighted categories, and institutional totals compute instantly &mdash; zero manual calculator work.</li>
                            <li><strong>Score Adjustment Dots:</strong> When an evaluator or PTWG calibrates a score, a subtle corner indicator dot appears (🟢 HoO or 🟡 PTWG). Hovering shows the prior score, and clicking opens the 3-Tier Rating Attribution Audit Modal.</li>
                            <li><strong>Dynamic Approval Lock:</strong> If a supervisor modifies any score away from baseline, direct "Approve Rating" is disabled until returning for revision or resetting adjustments.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Bonus &amp; Promotion Threshold:</strong> Under Civil Service rules, a minimum final rating of <strong>Satisfactory (3.00)</strong> is required to remain eligible for performance-based bonuses, step increments, and promotions.
                    </div>
                </div>
            </div>

            <!-- SLIDE 5: EXPORT EXCEL & SIGN -->
            <div id="guide-slide-5" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>CSC Compliance &bull; Step 5</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Official CSC Excel Export &amp; Signatures</h4>
                    <p class="spms-guide-hero-desc">
                        Once all scores are finalized, download the official Civil Service Commission spreadsheet, print cleanly without browser margin cuts, and route for required signatures.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span>Official CSC Excel Export (.xlsx)</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Click "Export Excel":</strong> Click the green <em>Export Excel</em> button in the document viewer header to download your complete form.</li>
                            <li><strong>100% CSC Fidelity:</strong> Outputs the official government format with pre-formatted institutional headers, category percentages, formulas, and signature blocks.</li>
                            <li><strong>Print Directly from Excel:</strong> Open the `.xlsx` file in Excel or Google Sheets and print (<kbd class="px-1 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px] border border-zinc-200 dark:border-zinc-700">Ctrl + P</kbd>). No cropped margins or awkward browser page cuts!</li>
                            <li><strong>Tamper-Proof Protection:</strong> Exported spreadsheets are password-protected against accidental cell overwrites (default unlock password: <code class="font-mono text-emerald-700 dark:text-emerald-400 font-bold">spms</code>).</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </div>
                            <span>Synchronized Signatures &amp; HRDO Filing</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Synchronized Signatories:</strong> Signatory names and positions in the header ("APPROVED BY") automatically match the footer ("Final Rating by").</li>
                            <li><strong>Add Co-Signers:</strong> Supervisors can click <code class="text-xs font-bold text-emerald-700 dark:text-emerald-400">+ Add Signatory</code> to add extra reviewers or committee heads.</li>
                            <li><strong>Required Signatures:</strong> Print the paper copy and secure signatures: Ratee, Department Chair, College Dean, and University President.</li>
                            <li><strong>Submit to HRDO:</strong> Hand in the signed hard copy to the HRDO. Your digital record is safely archived in the system for 5 years per CSC rules.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">All Set!</strong> You have officially completed the semester performance evaluation cycle!
                    </div>
                </div>
            </div>

            <!-- SLIDE 6: GOTCHAS & QUICK FAQS -->
            <div id="guide-slide-6" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Troubleshooting &bull; Step 6</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Common Questions &amp; Quick Fixes</h4>
                    <p class="spms-guide-hero-desc">
                        Encountered a disabled button or locked form? Here are the instant answers to the most common questions across the system.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span>Buttons &amp; Document Locking</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Why is "Approve Rating" disabled?</strong> If you adjusted any scores, the system protects due process by disabling direct approval. Click <em>Return for Revision</em> to send notes to the ratee, or reset the adjustments back to submitted scores to re-enable direct approval.</li>
                            <li><strong>Why is my form locked / read-only?</strong> Once submitted to a supervisor or officially approved, the table is locked against tampering. Ask your supervisor to click <em>Return for Revision</em> if corrections are needed.</li>
                            <li><strong>Why does it say weights must equal 100%?</strong> The sum of your percentage weights (Core + Strategic + Support) must equal 100%. Adjust the weight inputs in the category headers.</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span>Files, Printing &amp; Passwords</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Where is the browser Print / PDF button?</strong> We upgraded printing: browser printing cut off table margins, so we replaced it with <strong>Export Excel</strong>. Download the `.xlsx` file and print directly (<kbd class="px-1 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px] border border-zinc-200 dark:border-zinc-700">Ctrl + P</kbd>) for perfect pages!</li>
                            <li><strong>What is the password for the Excel file?</strong> If you need to unprotect the sheet in Excel, the unlock password is <code class="font-mono text-emerald-700 dark:text-emerald-400 font-bold">spms</code>.</li>
                            <li><strong>My proof file is too large!</strong> Don't struggle with file size limits &mdash; upload it to your BSU Google Drive, copy the link, and paste it into the MOV Evidence drawer.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Still need assistance?</strong> Contact the <strong>BSU Human Resource Development Office (HRDO)</strong> or reach out to your college/office SPMS Coordinator.
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Navigation Controls -->
        <div class="spms-modal-footer">
            <div id="guide-step-indicator" class="text-xs sm:text-sm font-bold text-zinc-600 dark:text-zinc-300">
                Step 1 of 6: Set Targets
            </div>
            <div class="flex items-center gap-2">
                <button id="guide-btn-prev" type="button" onclick="prevGuideStep()" class="spms-guide-nav-btn-secondary" style="display: none;">
                    &larr; Previous
                </button>
                <button id="guide-btn-next" type="button" onclick="nextGuideStep()" class="spms-guide-nav-btn-primary">
                    Next Step &rarr;
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================================
       BSU SPMS Standard Modal Design System (60-30-10 Palette)
       ============================================================ */
    .spms-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background-color: rgba(9, 9, 11, 0.75);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .spms-modal-backdrop.hidden {
        display: none !important;
    }
    .spms-modal-dialog {
        background-color: #ffffff;
        border: 1px solid #e4e4e7;
        border-radius: 16px;
        box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 900px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    .dark .spms-modal-dialog {
        background-color: #18181b !important;
        border: 1px solid #27272a !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.85) !important;
    }
    .spms-modal-header {
        background-color: #f4f4f5;
        border-bottom: 1px solid #e4e4e7;
        padding: 16px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .dark .spms-modal-header {
        background-color: #09090b !important;
        border-bottom: 1px solid #27272a !important;
    }
    .spms-modal-btn-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: transparent;
        border: 1px solid transparent;
        color: #71717a;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .spms-modal-btn-close:hover {
        background-color: #e4e4e7;
        color: #09090b;
    }
    .dark .spms-modal-btn-close:hover {
        background-color: #27272a !important;
        color: #ffffff !important;
    }

    /* Quick Role Jump Bar */
    .spms-guide-role-bar {
        background-color: #fafafa;
        border-bottom: 1px solid #f4f4f5;
        padding: 8px 22px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .dark .spms-guide-role-bar {
        background-color: #121214 !important;
        border-bottom: 1px solid #27272a !important;
    }
    .spms-guide-role-btn {
        background-color: #ffffff;
        border: 1px solid #e4e4e7;
        color: #3f3f46;
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 9999px;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .spms-guide-role-btn:hover {
        background-color: #f4f4f5;
        border-color: #d4d4d8;
        color: #09090b;
    }
    .dark .spms-guide-role-btn {
        background-color: #1f1f23;
        border-color: #2e2e33;
        color: #d4d4d8;
    }
    .dark .spms-guide-role-btn:hover {
        background-color: #27272a;
        color: #ffffff;
    }
    .spms-guide-role-btn-faq {
        background-color: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }
    .spms-guide-role-btn-faq:hover {
        background-color: #fee2e2;
        border-color: #fca5a5;
        color: #7f1d1d;
    }
    .dark .spms-guide-role-btn-faq {
        background-color: #2a1215 !important;
        border-color: #4c1d24 !important;
        color: #fca5a5 !important;
    }
    .dark .spms-guide-role-btn-faq:hover {
        background-color: #3a181e !important;
        color: #ffffff !important;
    }

    .spms-modal-body {
        background-color: #ffffff;
        padding: 18px 22px;
        overflow-y: auto;
        flex: 1 1 auto;
    }
    .dark .spms-modal-body {
        background-color: #18181b !important;
    }
    .spms-modal-footer {
        background-color: #f4f4f5;
        border-top: 1px solid #e4e4e7;
        padding: 14px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .dark .spms-modal-footer {
        background-color: #09090b !important;
        border-top: 1px solid #27272a !important;
    }

    /* Segmented Navigation Tabs */
    .spms-guide-tabs {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 6px;
        margin-bottom: 18px;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 4px;
    }
    .spms-guide-tabs::-webkit-scrollbar {
        display: none;
    }
    .spms-guide-tab {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        background-color: #f4f4f5;
        border: 1px solid #e4e4e7;
        border-radius: 12px;
        padding: 8px 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.15s ease;
        text-align: left;
        white-space: nowrap;
    }
    @media (max-width: 768px) {
        .spms-guide-tab {
            flex: 0 0 auto !important;
            min-width: 130px !important;
            padding: 8px 12px;
        }
    }
    .spms-guide-tab:hover {
        background-color: #ebebee;
        border-color: #d4d4d8;
    }
    .dark .spms-guide-tab {
        background-color: #27272a;
        border: 1px solid #3f3f46;
    }
    .dark .spms-guide-tab:hover {
        background-color: #3f3f46;
        border-color: #52525b;
    }
    .spms-guide-tab.active {
        background-color: #ffffff;
        border-color: #f59e0b;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.18);
    }
    .dark .spms-guide-tab.active {
        background-color: #1f1f23 !important;
        border-color: #f59e0b !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5) !important;
    }
    .spms-guide-tab-badge {
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        background-color: #e4e4e7;
        color: #3f3f46;
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .spms-guide-tab.active .spms-guide-tab-badge {
        background-color: #f59e0b;
        color: #000000;
    }
    .dark .spms-guide-tab-badge {
        background-color: #18181b;
        color: #d4d4d8;
    }
    .dark .spms-guide-tab.active .spms-guide-tab-badge {
        background-color: #f59e0b;
        color: #000000;
    }
    .spms-guide-tab-title {
        font-size: 12px;
        font-weight: 700;
        color: #52525b;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: -0.01em;
    }
    .spms-guide-tab.active .spms-guide-tab-title {
        color: #09090b;
        font-weight: 800;
    }
    .dark .spms-guide-tab-title {
        color: #d4d4d8;
    }
    .dark .spms-guide-tab.active .spms-guide-tab-title {
        color: #ffffff;
        font-weight: 800;
    }

    /* Slides & Animation */
    .spms-guide-slide {
        display: none;
    }
    .spms-guide-slide.active {
        display: block;
        animation: spmsGuideFade 0.15s ease-out;
    }
    @keyframes spmsGuideFade {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Hero Banner */
    .spms-guide-hero-banner {
        background-color: #f4f4f5;
        border: 1px solid #e4e4e7;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 14px;
    }
    .dark .spms-guide-hero-banner {
        background-color: #1f1f23;
        border: 1px solid #2e2e33;
    }
    .spms-guide-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #52525b;
        margin-bottom: 4px;
    }
    .dark .spms-guide-hero-tag {
        color: #d4d4d8;
    }
    .spms-guide-hero-title {
        font-size: 17px;
        font-weight: 900;
        color: #09090b;
        margin: 0 0 4px 0;
        letter-spacing: -0.015em;
    }
    .dark .spms-guide-hero-title {
        color: #ffffff;
    }
    .spms-guide-hero-desc {
        font-size: 13.5px;
        line-height: 1.5;
        color: #52525b;
        margin: 0;
    }
    .dark .spms-guide-hero-desc {
        color: #d4d4d8;
    }

    /* Action Cards */
    .spms-guide-action-card {
        background-color: #ffffff;
        border: 1px solid #e4e4e7;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .dark .spms-guide-action-card {
        background-color: #18181b;
        border: 1px solid #27272a;
    }
    .spms-guide-bullet-list {
        margin: 0;
        padding-left: 18px;
        font-size: 12.5px;
        line-height: 1.6;
        color: #3f3f46;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .dark .spms-guide-bullet-list {
        color: #d4d4d8;
    }
    .spms-guide-bullet-list strong {
        color: #09090b;
        font-weight: 700;
    }
    .dark .spms-guide-bullet-list strong {
        color: #fafafa;
        font-weight: 700;
    }

    /* Callout Box */
    .spms-guide-callout-box {
        background-color: #f4fdf7;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 10px 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .dark .spms-guide-callout-box {
        background-color: #0e291e;
        border: 1px solid #144630;
    }

    /* Buttons */
    .spms-guide-nav-btn-primary {
        background-color: #f59e0b;
        color: #000000;
        font-size: 12.5px;
        font-weight: 900;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 8px 18px;
        border-radius: 9px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .spms-guide-nav-btn-primary:hover {
        background-color: #d97706;
    }
    .spms-guide-nav-btn-secondary {
        background-color: #e4e4e7;
        color: #27272a;
        font-size: 12.5px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 9px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .spms-guide-nav-btn-secondary:hover {
        background-color: #d4d4d8;
        color: #09090b;
    }
    .dark .spms-guide-nav-btn-secondary {
        background-color: #27272a;
        color: #d4d4d8;
    }
    .dark .spms-guide-nav-btn-secondary:hover {
        background-color: #3f3f46;
        color: #ffffff;
    }
</style>

<script>
    let currentGuideStep = 1;
    const totalGuideSteps = 6;
    const guideStepTitles = [
        '', 
        'Set Targets', 
        'Approvals', 
        'Attach Proof', 
        'Rate & Score',
        'Export Excel',
        'Quick FAQs'
    ];

    function showGuideStep(step) {
        if (step < 1) step = 1;
        if (step > totalGuideSteps) step = totalGuideSteps;
        currentGuideStep = step;

        for (let i = 1; i <= totalGuideSteps; i++) {
            const tab = document.getElementById('guide-tab-' + i);
            const slide = document.getElementById('guide-slide-' + i);
            if (tab) {
                if (i === step) {
                    tab.classList.add('active');
                    tab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    tab.classList.remove('active');
                }
            }
            if (slide) {
                if (i === step) slide.classList.add('active');
                else slide.classList.remove('active');
            }
        }
        const prevBtn = document.getElementById('guide-btn-prev');
        const nextBtn = document.getElementById('guide-btn-next');
        const indicator = document.getElementById('guide-step-indicator');

        if (prevBtn) {
            prevBtn.style.display = (step === 1) ? 'none' : 'inline-flex';
        }
        if (nextBtn) {
            if (step === totalGuideSteps) {
                nextBtn.innerText = 'Got It, Close Guide';
                nextBtn.onclick = closeUserGuideModal;
            } else {
                nextBtn.innerText = 'Next Step →';
                nextBtn.onclick = nextGuideStep;
            }
        }
        if (indicator) {
            indicator.innerText = 'Step ' + step + ' of ' + totalGuideSteps + ': ' + guideStepTitles[step];
        }
    }

    function nextGuideStep() {
        if (currentGuideStep < totalGuideSteps) {
            showGuideStep(currentGuideStep + 1);
        }
    }

    function prevGuideStep() {
        if (currentGuideStep > 1) {
            showGuideStep(currentGuideStep - 1);
        }
    }

    function openUserGuideModal() {
        showGuideStep(1);
        document.getElementById('userGuideModal')?.classList.remove('hidden');
    }

    function closeUserGuideModal() {
        document.getElementById('userGuideModal')?.classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('userGuideModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'ArrowRight') nextGuideStep();
            else if (e.key === 'ArrowLeft') prevGuideStep();
            else if (e.key === 'Escape') closeUserGuideModal();
        }
    });
</script>
