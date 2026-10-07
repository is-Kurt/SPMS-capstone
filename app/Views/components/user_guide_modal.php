<!--
    BSU SPMS Faculty & Evaluator Simple Step-by-Step Guide
    Plain-English, jargon-free guide accessible across the system
-->
<div id="userGuideModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeUserGuideModal()">
    <div class="spms-modal-dialog">
        <!-- Header -->
        <div class="spms-modal-header">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block mb-1">
                    BSU SPMS &bull; Quick Step-by-Step Guide
                </span>
                <h3 class="text-lg sm:text-xl font-black text-zinc-900 dark:text-white m-0 tracking-tight">
                    How to Complete Your Evaluation Paper
                </h3>
            </div>
            <button type="button" onclick="closeUserGuideModal()" class="spms-modal-btn-close" title="Close Guide" aria-label="Close Guide">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="spms-modal-body custom-scrollbar">
            
            <!-- 5 Simple Tabs -->
            <div class="spms-guide-tabs">
                <button type="button" id="guide-tab-1" class="spms-guide-tab active" onclick="showGuideStep(1)">
                    <span class="spms-guide-tab-badge">1</span>
                    <span class="spms-guide-tab-title">Set Targets</span>
                </button>
                <button type="button" id="guide-tab-2" class="spms-guide-tab" onclick="showGuideStep(2)">
                    <span class="spms-guide-tab-badge">2</span>
                    <span class="spms-guide-tab-title">Get Approval</span>
                </button>
                <button type="button" id="guide-tab-3" class="spms-guide-tab" onclick="showGuideStep(3)">
                    <span class="spms-guide-tab-badge">3</span>
                    <span class="spms-guide-tab-title">Attach Proof</span>
                </button>
                <button type="button" id="guide-tab-4" class="spms-guide-tab" onclick="showGuideStep(4)">
                    <span class="spms-guide-tab-badge">4</span>
                    <span class="spms-guide-tab-title">Rate Yourself</span>
                </button>
                <button type="button" id="guide-tab-5" class="spms-guide-tab" onclick="showGuideStep(5)">
                    <span class="spms-guide-tab-badge">5</span>
                    <span class="spms-guide-tab-title">Print &amp; Sign</span>
                </button>
            </div>

            <!-- SLIDE 1: SET TARGETS -->
            <div id="guide-slide-1" class="spms-guide-slide active">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Start of Semester &bull; Step 1</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Write Down What You Plan to Do</h4>
                    <p class="spms-guide-hero-desc">
                        At the start of the semester, list the main tasks you will work on and set a clear number for each goal.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <span>What to List</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Your Main Duties:</strong> List your main responsibilities (like teaching classes, research, office paperwork, or student services).</li>
                            <li><strong>Put a Number on It:</strong> Set clear, realistic targets (for example: teach 4 subjects, prepare 2 modules, or process 50 requests).</li>
                            <li><strong>How It's Judged:</strong> Briefly explain how quality and on-time delivery will be checked.</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span>Send It for Review</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Submit Button:</strong> When you finish writing your list, click the <em>Submit Targets</em> button at the top.</li>
                            <li><strong>Supervisor Checks It:</strong> Your Department Chair or Dean reviews your goals to make sure they are fair.</li>
                            <li><strong>Need Changes?</strong> If your supervisor wants something adjusted, they will send it back with a short note so you can update it.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Quick Tip:</strong> Make sure your personal targets support what your department is trying to achieve this semester.
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
                    <h4 class="spms-guide-hero-title">How Approvals Work</h4>
                    <p class="spms-guide-hero-desc">
                        Once your supervisor approves your list, your targets are locked in so you can focus on doing the work.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span>For Teachers &amp; Staff</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Sit Back &amp; Wait:</strong> After clicking submit, your paper moves to your supervisor's review queue.</li>
                            <li><strong>Get Notified:</strong> You will see a notification as soon as your supervisor approves your list.</li>
                            <li><strong>Targets Locked:</strong> Once approved, your goals are set for the term and you are ready to start.</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <span>For Deans &amp; Department Chairs</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Check Each Paper:</strong> Look over your members' targets to make sure they are realistic and not overloaded.</li>
                            <li><strong>One-Click Approval:</strong> If the goals look great, click <em>Approve Targets</em>.</li>
                            <li><strong>Send to Your Team:</strong> Use the right-side panel to hand out new semester folders to everyone in your department.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Good to Know:</strong> Deans and Chairs must have their own college targets approved before they can distribute folders down to their teachers.
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: ATTACH PROOF -->
            <div id="guide-slide-3" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>During the Semester &bull; Step 3</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Attach Proof of Your Accomplishments</h4>
                    <p class="spms-guide-hero-desc">
                        As you finish tasks throughout the semester, upload files directly to your paper to prove your work was done.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </div>
                            <span>How to Upload</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Click the Paperclip:</strong> On any row in your table, click the small paperclip icon.</li>
                            <li><strong>Pick Your File:</strong> Upload a PDF, image, Word doc, or spreadsheet.</li>
                            <li><strong>Upload as You Go:</strong> Don't wait until the last week of school &mdash; upload files right after you finish each task!</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span>Examples of Good Proof</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>For Teaching:</strong> Signed course syllabi, class grading sheets, or student evaluation summaries.</li>
                            <li><strong>For Research &amp; Extension:</strong> Published articles, acceptance letters, certificates, or photos of community service.</li>
                            <li><strong>For Office Work:</strong> Signed routing slips, processed receipts, finished reports, or time logs.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Why It Matters:</strong> Your supervisor cannot grade your accomplishments without seeing the proof.
                    </div>
                </div>
            </div>

            <!-- SLIDE 4: RATE YOURSELF -->
            <div id="guide-slide-4" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>End of Semester &bull; Step 4</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Score Your Results with Your Supervisor</h4>
                    <p class="spms-guide-hero-desc">
                        At the end of the term, type in what you achieved and give yourself a fair score using the 1 to 5 scale.
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
                            <li><strong>5 &bull; Outstanding:</strong> You did way more than promised (at least 30% higher than your target) with zero errors.</li>
                            <li><strong>4 &bull; Very Satisfactory:</strong> You completed 100% of what you promised, on time and with great quality.</li>
                            <li><strong>3 &bull; Satisfactory:</strong> You met the basic requirements and finished most tasks.</li>
                            <li><strong>2 or 1 &bull; Needs Work:</strong> You were unable to finish a big portion of your goals.</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            </div>
                            <span>Agreeing on the Final Grade</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Math is Automatic:</strong> The system computes all your averages automatically.</li>
                            <li><strong>Supervisor Checks Proof:</strong> Your supervisor opens your uploaded files to verify your claims.</li>
                            <li><strong>Chat &amp; Agree:</strong> You and your supervisor discuss your performance and agree on the final rating before signing.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">Bonus Qualification:</strong> You need at least a <em>Satisfactory</em> rating (3.0 or higher) to be eligible for university performance bonuses and promotions.
                    </div>
                </div>
            </div>

            <!-- SLIDE 5: PRINT & SIGN -->
            <div id="guide-slide-5" class="spms-guide-slide">
                <div class="spms-guide-hero-banner">
                    <div class="spms-guide-hero-tag">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Final Step &bull; Step 5</span>
                    </div>
                    <h4 class="spms-guide-hero-title">Download, Print &amp; Sign Your Form</h4>
                    <p class="spms-guide-hero-desc">
                        Once everyone agrees on the final scores, download the official form, sign it, and turn it in to HR.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span>Getting the File</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>Download Excel:</strong> Click <em>Export Excel</em> to save the official government spreadsheet with all your scores and math ready.</li>
                            <li><strong>Print Preview:</strong> Click <em>Print / PDF</em> to open a clean page ready to print directly onto standard paper.</li>
                            <li><strong>Pre-Formatted:</strong> You don't need to format anything &mdash; the system lines up all tables and boxes for you.</li>
                        </ul>
                    </div>

                    <div class="spms-guide-action-card">
                        <div class="flex items-center gap-2.5 mb-2.5 text-zinc-900 dark:text-white font-extrabold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </div>
                            <span>Signatures &amp; Turning It In</span>
                        </div>
                        <ul class="spms-guide-bullet-list">
                            <li><strong>3 Signatures:</strong> Print the paper and sign it. Then have your Department Chair and the University President sign it.</li>
                            <li><strong>Submit to HR:</strong> Hand in the signed hard copy to the HR Office.</li>
                            <li><strong>Safely Stored:</strong> Your record is kept safe in university archives for 5 years as required by law.</li>
                        </ul>
                    </div>
                </div>

                <div class="spms-guide-callout-box">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs sm:text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200">
                        <strong class="text-zinc-900 dark:text-white">All Done!</strong> You have officially completed your semester performance evaluation!
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Navigation Controls -->
        <div class="spms-modal-footer">
            <div id="guide-step-indicator" class="text-sm font-bold text-zinc-600 dark:text-zinc-300">
                Step 1 of 5: Set Targets
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
        max-width: 880px;
        max-height: 90vh;
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
        padding: 18px 24px;
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
    .spms-modal-body {
        background-color: #ffffff;
        padding: 22px 24px;
        overflow-y: auto;
        flex: 1 1 auto;
    }
    .dark .spms-modal-body {
        background-color: #18181b !important;
    }
    .spms-modal-footer {
        background-color: #f4f4f5;
        border-top: 1px solid #e4e4e7;
        padding: 16px 24px;
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
        gap: 8px;
        margin-bottom: 20px;
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
        padding: 10px 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: all 0.15s ease;
        text-align: left;
        white-space: nowrap;
    }
    @media (max-width: 680px) {
        .spms-guide-tab {
            flex: 0 0 auto !important;
            min-width: 135px !important;
            padding: 10px 12px;
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
        width: 24px;
        height: 24px;
        border-radius: 9999px;
        background-color: #e4e4e7;
        color: #3f3f46;
        font-size: 12px;
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
        font-size: 13px;
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
        padding: 16px 20px;
        margin-bottom: 16px;
    }
    .dark .spms-guide-hero-banner {
        background-color: #1f1f23;
        border: 1px solid #2e2e33;
    }
    .spms-guide-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #52525b;
        margin-bottom: 5px;
    }
    .dark .spms-guide-hero-tag {
        color: #d4d4d8;
    }
    .spms-guide-hero-title {
        font-size: 18px;
        font-weight: 900;
        color: #09090b;
        margin: 0 0 6px 0;
        letter-spacing: -0.015em;
    }
    .dark .spms-guide-hero-title {
        color: #ffffff;
    }
    .spms-guide-hero-desc {
        font-size: 14px;
        line-height: 1.55;
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
        padding: 16px 18px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .dark .spms-guide-action-card {
        background-color: #18181b;
        border: 1px solid #27272a;
    }
    .spms-guide-bullet-list {
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
        line-height: 1.65;
        color: #3f3f46;
        display: flex;
        flex-direction: column;
        gap: 8px;
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
        padding: 12px 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .dark .spms-guide-callout-box {
        background-color: #0e291e;
        border: 1px solid #144630;
    }

    /* Buttons */
    .spms-guide-nav-btn-primary {
        background-color: #f59e0b;
        color: #000000;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 10px 20px;
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
        font-size: 13px;
        font-weight: 700;
        padding: 10px 18px;
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
    const totalGuideSteps = 5;
    const guideStepTitles = [
        '', 
        'Set Targets', 
        'Get Approval', 
        'Attach Proof', 
        'Rate Yourself',
        'Print & Sign'
    ];

    function showGuideStep(step) {
        currentGuideStep = step;
        for (let i = 1; i <= totalGuideSteps; i++) {
            const tab = document.getElementById('guide-tab-' + i);
            const slide = document.getElementById('guide-slide-' + i);
            if (tab) {
                if (i === step) tab.classList.add('active');
                else tab.classList.remove('active');
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
