<!--
    BSU SPMS Visual Quick-Start Guide (2026 Edition)
    Action-oriented, role-segregated, zero-scroll visual recipes
    Strictly no emojis; compact viewport-fitting layout
-->
<div id="userGuideModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeUserGuideModal()">
    <div class="spms-modal-dialog">
        <!-- Header (Compact) -->
        <div class="spms-modal-header">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        BSU SPMS &bull; Quick Action Guide
                    </span>
                    <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-medium hidden sm:inline">&bull; 2026 Edition</span>
                </div>
                <h3 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white m-0 tracking-tight leading-tight">
                    How to Complete Your Evaluation Paper
                </h3>
            </div>
            <button type="button" onclick="closeUserGuideModal()" class="spms-modal-btn-close" title="Close Guide (Esc)" aria-label="Close Guide">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Role Selector & Quick Search Bar -->
        <div class="spms-guide-toolbar">
            <div class="spms-guide-role-selector">
                <button type="button" id="role-btn-faculty" onclick="switchGuideRole('faculty')" class="spms-guide-role-tab active">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                    <span>Faculty &amp; Staff</span>
                </button>
                <button type="button" id="role-btn-supervisor" onclick="switchGuideRole('supervisor')" class="spms-guide-role-tab">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Head of Office / TWG / PMT</span>
                </button>
                <button type="button" id="role-btn-faqs" onclick="switchGuideRole('faqs')" class="spms-guide-role-tab">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Gotchas &amp; FAQs</span>
                </button>
            </div>

            <!-- Instant Search Input -->
            <div class="spms-guide-search-wrapper">
                <svg class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="guideSearchInput" placeholder="Quick search... (password, rubric, weights, drive)" oninput="handleGuideSearch(this.value)" autocomplete="off">
                <button type="button" id="guideSearchClear" onclick="clearGuideSearch()" class="hidden text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search Results Panel (Overlay for search queries) -->
        <div id="guideSearchResults" class="spms-guide-search-results hidden custom-scrollbar"></div>

        <!-- Main Body (No Scroll) -->
        <div id="guideMainBody" class="spms-modal-body">
            
            <!-- ========================================================
                 ROLE 1: FACULTY & STAFF (RATEES: IPCR / IPERF)
                 ======================================================== -->
            <div id="guide-section-faculty" class="spms-guide-section active">
                <!-- Lifecycle Phase Step Pills (Grid 3 Equal Columns) -->
                <div class="spms-phase-tabs">
                    <button type="button" id="faculty-tab-1" class="spms-phase-tab active" onclick="showFacultyStep(1)">
                        <span class="spms-phase-indicator bg-emerald-500"></span>
                        <div class="text-left overflow-hidden">
                            <div class="spms-phase-tag">Start of Semester</div>
                            <div class="spms-phase-title truncate">1. Set Targets &amp; Rubrics</div>
                        </div>
                    </button>
                    <button type="button" id="faculty-tab-2" class="spms-phase-tab" onclick="showFacultyStep(2)">
                        <span class="spms-phase-indicator bg-amber-500"></span>
                        <div class="text-left overflow-hidden">
                            <div class="spms-phase-tag">During Semester</div>
                            <div class="spms-phase-title truncate">2. Attach Proof (MOVs)</div>
                        </div>
                    </button>
                    <button type="button" id="faculty-tab-3" class="spms-phase-tab" onclick="showFacultyStep(3)">
                        <span class="spms-phase-indicator bg-sky-500"></span>
                        <div class="text-left overflow-hidden">
                            <div class="spms-phase-tag">End of Semester</div>
                            <div class="spms-phase-title truncate">3. Rate &amp; Export Excel</div>
                        </div>
                    </button>
                </div>

                <!-- FACULTY STEP 1: TARGETS & RUBRICS -->
                <div id="faculty-slide-1" class="spms-guide-slide active">
                    <div class="spms-recipe-card">
                        <div class="spms-recipe-header">
                            <span class="spms-step-number bg-emerald-600 text-white">1</span>
                            <div>
                                <h4 class="spms-recipe-title">Type Commitments &amp; Set 100% Weights</h4>
                                <p class="spms-recipe-sub">Enter what you plan to accomplish before the semester begins.</p>
                            </div>
                        </div>
                        <div class="spms-recipe-steps">
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 1</span>
                                <div class="spms-recipe-text">
                                    <strong>Fill table rows:</strong> Enter tasks under <strong>Core Functions</strong>, <strong>Strategic Functions</strong>, and <strong>Support Functions</strong>. <em>(Contract/JO use blank IPERF rows)</em>.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 2</span>
                                <div class="spms-recipe-text">
                                    <strong>Equal 100% weights:</strong> Adjust category headers (e.g., Core 70% + Strategic 20% + Support 10% = <strong>100%</strong>).
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 3</span>
                                <div class="spms-recipe-text">
                                    <strong>Rubrics made simple:</strong> In <span class="spms-ui-badge spms-ui-badge-amber"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Rubrics Matrix</span>, simply upload your Word/PDF rubric file—leaving matrix rows blank is allowed!
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 4</span>
                                <div class="spms-recipe-text">
                                    <strong>Submit Targets:</strong> Click <span class="spms-ui-badge spms-ui-badge-emerald"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>Submit Targets</span> at top right. Work auto-saves; once submitted, table locks for review.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FACULTY STEP 2: ATTACH PROOF (MOVs) -->
                <div id="faculty-slide-2" class="spms-guide-slide">
                    <div class="spms-recipe-card">
                        <div class="spms-recipe-header">
                            <span class="spms-step-number bg-amber-500 text-white">2</span>
                            <div>
                                <h4 class="spms-recipe-title">Attach Proof as You Finish Tasks</h4>
                                <p class="spms-recipe-sub">Don't wait for final clearance week—attach evidence as deliverables finish.</p>
                            </div>
                        </div>
                        <div class="spms-recipe-steps">
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 1</span>
                                <div class="spms-recipe-text">
                                    <strong>Click Paperclip:</strong> On any row, click <span class="spms-ui-badge spms-ui-badge-zinc"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>Paperclip</span> in the MOV column to open the Evidence Drawer.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 2</span>
                                <div class="spms-recipe-text">
                                    <strong>Upload direct files:</strong> Attach standard files (`PDF`, `DOCX`, `PNG`, `JPG`, `XLSX`) like signed syllabi or certificates.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 3</span>
                                <div class="spms-recipe-text">
                                    <strong>Large files / videos:</strong> Upload to <strong>BSU Google Drive</strong>, copy the shareable link, and paste into Cloud Link for instant evaluator preview.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FACULTY STEP 3: RATE & EXPORT EXCEL -->
                <div id="faculty-slide-3" class="spms-guide-slide">
                    <div class="spms-recipe-card">
                        <div class="spms-recipe-header">
                            <span class="spms-step-number bg-sky-600 text-white">3</span>
                            <div>
                                <h4 class="spms-recipe-title">Self-Rate &amp; Download CSC Excel</h4>
                                <p class="spms-recipe-sub">Enter actual achievements, self-grade, and export your official government form.</p>
                            </div>
                        </div>
                        <div class="spms-recipe-steps">
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 1</span>
                                <div class="spms-recipe-text">
                                    <strong>Type accomplishments:</strong> In the <em>Actual Accomplishment</em> column, write what was delivered.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 2</span>
                                <div class="spms-recipe-text">
                                    <strong>Score 1 to 5 on Q, E, T:</strong> Enter numbers 1 to 5 (or select N/A). Row and category averages calculate automatically.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 3</span>
                                <div class="spms-recipe-text">
                                    <strong>Export official Excel:</strong> Click <span class="spms-ui-badge spms-ui-badge-emerald"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Export Excel</span> in the top toolbar to download the `.xlsx` file.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 4</span>
                                <div class="spms-recipe-text">
                                    <strong>Print &amp; Sign:</strong> Open in Excel, print with <kbd class="spms-kbd">Ctrl + P</kbd> (no cutoffs), sign, and submit hard copies to HRDO.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================
                 ROLE 2: DEANS & DEPARTMENT CHAIRS (SUPERVISORS)
                 ======================================================== -->
            <div id="guide-section-supervisor" class="spms-guide-section">
                <!-- Lifecycle Phase Step Pills (Grid 3 Equal Columns) -->
                <div class="spms-phase-tabs">
                    <button type="button" id="supervisor-tab-1" class="spms-phase-tab active" onclick="showSupervisorStep(1)">
                        <span class="spms-phase-indicator bg-emerald-500"></span>
                        <div class="text-left overflow-hidden">
                            <div class="spms-phase-tag">Start of Semester</div>
                            <div class="spms-phase-title truncate">1. Targets &amp; Cascading</div>
                        </div>
                    </button>
                    <button type="button" id="supervisor-tab-2" class="spms-phase-tab" onclick="showSupervisorStep(2)">
                        <span class="spms-phase-indicator bg-amber-500"></span>
                        <div class="text-left overflow-hidden">
                            <div class="spms-phase-tag">During Semester</div>
                            <div class="spms-phase-title truncate">2. Track Team Proof</div>
                        </div>
                    </button>
                    <button type="button" id="supervisor-tab-3" class="spms-phase-tab" onclick="showSupervisorStep(3)">
                        <span class="spms-phase-indicator bg-sky-500"></span>
                        <div class="text-left overflow-hidden">
                            <div class="spms-phase-tag">End of Semester</div>
                            <div class="spms-phase-title truncate">3. Calibrate &amp; TWG/PMT</div>
                        </div>
                    </button>
                </div>

                <!-- SUPERVISOR STEP 1: REVIEW TARGETS & CASCADING -->
                <div id="supervisor-slide-1" class="spms-guide-slide active">
                    <div class="spms-recipe-card">
                        <div class="spms-recipe-header">
                            <span class="spms-step-number bg-emerald-600 text-white">1</span>
                            <div>
                                <h4 class="spms-recipe-title">Approve Targets &amp; Cascading Flow</h4>
                                <p class="spms-recipe-sub">Ensure university targets flow cleanly to individual faculty.</p>
                            </div>
                        </div>
                        <div class="spms-recipe-steps">
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 1</span>
                                <div class="spms-recipe-text">
                                    <strong>Cascading rule:</strong> Dean's DPCR must be approved before cascading to Chairs; Chair's DPCR must be approved before Faculty can begin.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 2</span>
                                <div class="spms-recipe-text">
                                    <strong>Check Review Queue:</strong> Open the faculty document. Verify deliverable measures and confirm Core, Strategic, and Support weights total 100%.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 3</span>
                                <div class="spms-recipe-text">
                                    <strong>Approve or Return:</strong> If valid, click <span class="spms-ui-badge spms-ui-badge-emerald"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Approve Targets</span>. If changes needed, click <span class="spms-ui-badge spms-ui-badge-rose"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Return for Revision</span>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUPERVISOR STEP 2: TRACK TEAM PROOF -->
                <div id="supervisor-slide-2" class="spms-guide-slide">
                    <div class="spms-recipe-card">
                        <div class="spms-recipe-header">
                            <span class="spms-step-number bg-amber-500 text-white">2</span>
                            <div>
                                <h4 class="spms-recipe-title">Monitor Evidence &amp; Coaching</h4>
                                <p class="spms-recipe-sub">Track team progress as deliverables are completed throughout the term.</p>
                            </div>
                        </div>
                        <div class="spms-recipe-steps">
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 1</span>
                                <div class="spms-recipe-text">
                                    <strong>Inspect MOVs:</strong> Click any <span class="spms-ui-badge spms-ui-badge-zinc"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>Paperclip</span> to inspect syllabi, certificates, or attached Google Drive folders.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 2</span>
                                <div class="spms-recipe-text">
                                    <strong>Live Cloud Previews:</strong> Google Drive links open in-browser for quick evaluation without downloading large files.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 3</span>
                                <div class="spms-recipe-text">
                                    <strong>Ongoing Feedback:</strong> Leave coaching notes in the remarks column before final rating week so staff have time to calibrate.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUPERVISOR STEP 3: CALIBRATE & TWG/PMT -->
                <div id="supervisor-slide-3" class="spms-guide-slide">
                    <div class="spms-recipe-card">
                        <div class="spms-recipe-header">
                            <span class="spms-step-number bg-sky-600 text-white">3</span>
                            <div>
                                <h4 class="spms-recipe-title">Confirm Evidence &amp; Calibrate Ratings</h4>
                                <p class="spms-recipe-sub">Validate proof attachments and apply calibrated HoO or TWG/PMT adjustments.</p>
                            </div>
                        </div>
                        <div class="spms-recipe-steps">
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 1</span>
                                <div class="spms-recipe-text">
                                    <strong>Inspect Self-Ratings:</strong> Verify that accomplishments match uploaded proof and CSC Quality, Efficiency, and Timeliness rubrics.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 2</span>
                                <div class="spms-recipe-text">
                                    <strong>Modifying a score:</strong> Type the calibrated score in Q, E, or T. An indicator dot appears (Green HoO or Amber TWG/PMT) to track history.
                                </div>
                            </div>
                            <div class="spms-recipe-row">
                                <span class="spms-badge-pill">Step 3</span>
                                <div class="spms-recipe-text">
                                    <strong>Why "Approve Rating" is disabled:</strong> CSC rules require due process: click <span class="spms-ui-badge spms-ui-badge-rose"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Return for Revision</span> with remarks so ratee is notified, or reset scores.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================
                 ROLE 3: GOTCHAS & QUICK FAQS (INSTANT 2x3 GRID - NO SCROLL)
                 ======================================================== -->
            <div id="guide-section-faqs" class="spms-guide-section">
                <div class="spms-faq-grid" id="faqCardGrid">
                    
                    <div class="spms-faq-card" data-keywords="locked read only tamper submit approved disabled edit table">
                        <div class="spms-faq-icon text-amber-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="spms-faq-content">
                            <h5 class="spms-faq-q">Why is my table locked / read-only?</h5>
                            <p class="spms-faq-a">
                                Once submitted or approved, tables lock to protect official records. Ask your supervisor to click <span class="spms-ui-badge spms-ui-badge-rose">Return for Revision</span>.
                            </p>
                        </div>
                    </div>

                    <div class="spms-faq-card" data-keywords="approve rating disabled greyed gray button click score adjustment calibration hoo twg pmt">
                        <div class="spms-faq-icon text-rose-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div class="spms-faq-content">
                            <h5 class="spms-faq-q">Why is "Approve Rating" disabled?</h5>
                            <p class="spms-faq-a">
                                A score was adjusted. Click <span class="spms-ui-badge spms-ui-badge-rose">Return for Revision</span> with feedback, or reset scores to submitted values.
                            </p>
                        </div>
                    </div>

                    <div class="spms-faq-card" data-keywords="excel password unlock unprotect spms sheet download export">
                        <div class="spms-faq-icon text-emerald-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div class="spms-faq-content">
                            <h5 class="spms-faq-q">What is the Excel sheet password?</h5>
                            <p class="spms-faq-a">
                                Exported sheets are protected against accidental formula overwrites. The unlock password is <code class="spms-code">spms</code>.
                            </p>
                        </div>
                    </div>

                    <div class="spms-faq-card" data-keywords="weights 100 percent core strategic support total error warning">
                        <div class="spms-faq-icon text-blue-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <div class="spms-faq-content">
                            <h5 class="spms-faq-q">Why must weights equal 100%?</h5>
                            <p class="spms-faq-a">
                                CSC rules mandate that category percentage weights total exactly <strong>100%</strong>. Adjust numbers in category headers until green.
                            </p>
                        </div>
                    </div>

                    <div class="spms-faq-card" data-keywords="rubric rubrics matrix attach upload word pdf excel mandatory empty blank">
                        <div class="spms-faq-icon text-amber-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <div class="spms-faq-content">
                            <h5 class="spms-faq-q">Must I type rubrics in the matrix?</h5>
                            <p class="spms-faq-a">
                                <strong>No.</strong> Click <span class="spms-ui-badge spms-ui-badge-amber">Rubrics Matrix</span> and attach your Word/PDF rubric file—leaving matrix rows blank is accepted!
                            </p>
                        </div>
                    </div>

                    <div class="spms-faq-card" data-keywords="proof large file size drive google link video portfolio limit upload mov">
                        <div class="spms-faq-icon text-indigo-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <div class="spms-faq-content">
                            <h5 class="spms-faq-q">What if my proof file is too large?</h5>
                            <p class="spms-faq-a">
                                Upload to <strong>BSU Google Drive</strong>, copy the shareable link, click <span class="spms-ui-badge spms-ui-badge-zinc">Paperclip</span>, and paste the link in the Cloud Link box.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Footer Bar (Compact) -->
        <div class="spms-modal-footer">
            <div class="flex items-center gap-2 text-[11px] text-zinc-500 dark:text-zinc-400">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Need extra help? Contact the <strong>BSU HRDO</strong> or your SPMS Coordinator.</span>
            </div>
            <button type="button" onclick="closeUserGuideModal()" class="spms-guide-footer-btn">
                Got It, Close Guide
            </button>
        </div>
    </div>
</div>

<style>
    /* ============================================================
       BSU SPMS Visual Guide Modal - Modern Design System
       Zero-Scroll, Viewport-Fitting Architecture (Strictly No Emojis)
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
        padding: 12px;
    }
    .spms-modal-backdrop.hidden {
        display: none !important;
    }
    .spms-modal-dialog {
        background-color: #ffffff;
        border: 1px solid #e4e4e7;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 860px;
        height: 520px;
        max-height: 94vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    .dark .spms-modal-dialog {
        background-color: #18181b !important;
        border: 1px solid #27272a !important;
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.9) !important;
    }

    /* Header */
    .spms-modal-header {
        background-color: #fafafa;
        border-bottom: 1px solid #e4e4e7;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .dark .spms-modal-header {
        background-color: #121214 !important;
        border-bottom: 1px solid #27272a !important;
    }
    .spms-modal-btn-close {
        width: 28px;
        height: 28px;
        border-radius: 7px;
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

    /* Toolbar: Role Selector + Search */
    .spms-guide-toolbar {
        background-color: #f4f4f5;
        border-bottom: 1px solid #e4e4e7;
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .dark .spms-guide-toolbar {
        background-color: #09090b !important;
        border-bottom: 1px solid #27272a !important;
    }

    .spms-guide-role-selector {
        display: flex;
        background-color: #e4e4e7;
        border-radius: 9px;
        padding: 2.5px;
        gap: 2.5px;
        flex: 1 1 420px;
        max-width: 540px;
    }
    .dark .spms-guide-role-selector {
        background-color: #1f1f23;
    }
    .spms-guide-role-tab {
        border: none;
        background: transparent;
        padding: 0 8px;
        height: 32px;
        border-radius: 7px;
        font-size: 11.5px;
        font-weight: 700;
        color: #52525b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex: 1 1 0;
        min-width: 0;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .dark .spms-guide-role-tab {
        color: #a1a1aa;
    }
    .spms-guide-role-tab:hover {
        color: #09090b;
    }
    .dark .spms-guide-role-tab:hover {
        color: #ffffff;
    }
    .spms-guide-role-tab.active {
        background-color: #ffffff;
        color: #09090b;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .dark .spms-guide-role-tab.active {
        background-color: #27272a;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.5);
    }

    .spms-guide-search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: #ffffff;
        border: 1px solid #d4d4d8;
        border-radius: 8px;
        padding: 0 10px;
        height: 32px;
        flex: 1 1 200px;
        max-width: 300px;
    }
    .dark .spms-guide-search-wrapper {
        background-color: #18181b;
        border-color: #3f3f46;
    }
    .spms-guide-search-wrapper input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 11.5px;
        color: #09090b;
        width: 100%;
    }
    .dark .spms-guide-search-wrapper input {
        color: #ffffff;
    }
    .spms-guide-search-wrapper input::placeholder {
        color: #a1a1aa;
    }

    /* Modal Body - Strict No Scroll */
    .spms-modal-body {
        padding: 12px 20px;
        overflow: hidden;
        flex: 1 1 auto;
        background-color: #ffffff;
        display: flex;
        flex-direction: column;
    }
    .dark .spms-modal-body {
        background-color: #18181b !important;
    }

    /* Sections */
    .spms-guide-section {
        display: none;
        height: 100%;
    }
    .spms-guide-section.active {
        display: flex;
        flex-direction: column;
        animation: spmsGuideFade 0.15s ease-out;
    }
    @keyframes spmsGuideFade {
        from { opacity: 0; transform: translateY(3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Phase Tabs - Compact Equal 3 Columns */
    .spms-phase-tabs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
        flex-shrink: 0;
    }
    .spms-phase-tab {
        width: 100%;
        height: 50px;
        background-color: #f4f4f5;
        border: 1px solid #e4e4e7;
        border-radius: 10px;
        padding: 6px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 9px;
        transition: all 0.15s ease;
        overflow: hidden;
    }
    .dark .spms-phase-tab {
        background-color: #27272a;
        border-color: #3f3f46;
    }
    .spms-phase-tab:hover {
        background-color: #e4e4e7;
    }
    .dark .spms-phase-tab:hover {
        background-color: #3f3f46;
    }
    .spms-phase-tab.active {
        background-color: #ffffff;
        border-color: #10b981;
        box-shadow: 0 1px 6px rgba(16, 185, 129, 0.18);
    }
    .dark .spms-phase-tab.active {
        background-color: #1f1f23;
        border-color: #10b981;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }
    .spms-phase-indicator {
        width: 7px;
        height: 7px;
        border-radius: 9999px;
        flex-shrink: 0;
    }
    .spms-phase-tag {
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #71717a;
        line-height: 1.1;
    }
    .dark .spms-phase-tag {
        color: #a1a1aa;
    }
    .spms-phase-title {
        font-size: 11.5px;
        font-weight: 800;
        color: #18181b;
        letter-spacing: -0.01em;
        line-height: 1.25;
        margin-top: 1px;
    }
    .dark .spms-phase-title {
        color: #ffffff;
    }

    /* Slides */
    .spms-guide-slide {
        display: none;
        flex: 1 1 auto;
    }
    .spms-guide-slide.active {
        display: flex;
        flex-direction: column;
        animation: spmsGuideFade 0.15s ease-out;
    }

    /* Recipe Card */
    .spms-recipe-card {
        background-color: #fafafa;
        border: 1px solid #e4e4e7;
        border-radius: 12px;
        padding: 10px 14px;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }
    .dark .spms-recipe-card {
        background-color: #1f1f23;
        border-color: #2e2e33;
    }
    .spms-recipe-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e4e4e7;
        flex-shrink: 0;
    }
    .dark .spms-recipe-header {
        border-bottom-color: #2e2e33;
    }
    .spms-step-number {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
        flex-shrink: 0;
    }
    .spms-recipe-title {
        font-size: 14px;
        font-weight: 900;
        color: #09090b;
        margin: 0;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }
    .dark .spms-recipe-title {
        color: #ffffff;
    }
    .spms-recipe-sub {
        font-size: 11px;
        color: #71717a;
        margin: 0;
        line-height: 1.2;
    }
    .dark .spms-recipe-sub {
        color: #a1a1aa;
    }

    .spms-recipe-steps {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1 1 auto;
        justify-content: space-evenly;
    }
    .spms-recipe-row {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: #ffffff;
        border: 1px solid #e4e4e7;
        border-radius: 8px;
        padding: 7px 12px;
    }
    .dark .spms-recipe-row {
        background-color: #18181b;
        border-color: #27272a;
    }
    .spms-badge-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        min-width: 52px;
        height: 20px;
        border-radius: 5px;
        background-color: #f4f4f5;
        border: 1px solid #e4e4e7;
        font-size: 9.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #52525b;
        flex-shrink: 0;
    }
    .dark .spms-badge-pill {
        background-color: #27272a;
        border-color: #3f3f46;
        color: #d4d4d8;
    }
    .spms-recipe-text {
        font-size: 12px;
        line-height: 1.35;
        color: #3f3f46;
        flex: 1;
    }
    .dark .spms-recipe-text {
        color: #d4d4d8;
    }
    .spms-recipe-text strong {
        color: #09090b;
    }
    .dark .spms-recipe-text strong {
        color: #ffffff;
    }

    /* Embedded Visual UI Badges */
    .spms-ui-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 1.5px 6px;
        border-radius: 5px;
        font-size: 10.5px;
        font-weight: 800;
        white-space: nowrap;
        vertical-align: middle;
        margin: 0 2px;
    }
    .spms-ui-badge-emerald {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .dark .spms-ui-badge-emerald {
        background-color: #064e3b;
        border-color: #047857;
        color: #6ee7b7;
    }
    .spms-ui-badge-amber {
        background-color: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }
    .dark .spms-ui-badge-amber {
        background-color: #78350f;
        border-color: #92400e;
        color: #fcd34d;
    }
    .spms-ui-badge-zinc {
        background-color: #f4f4f5;
        border: 1px solid #d4d4d8;
        color: #27272a;
    }
    .dark .spms-ui-badge-zinc {
        background-color: #27272a;
        border-color: #3f3f46;
        color: #e4e4e7;
    }
    .spms-ui-badge-rose {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .dark .spms-ui-badge-rose {
        background-color: #7f1d1d;
        border-color: #991b1b;
        color: #fca5a5;
    }
    .spms-kbd {
        padding: 1px 5px;
        border-radius: 3px;
        background-color: #f4f4f5;
        border: 1px solid #d4d4d8;
        font-family: monospace;
        font-size: 10.5px;
        color: #27272a;
    }
    .dark .spms-kbd {
        background-color: #27272a;
        border-color: #3f3f46;
        color: #e4e4e7;
    }
    .spms-code {
        padding: 1px 5px;
        border-radius: 3px;
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        font-family: monospace;
        font-size: 11px;
        font-weight: 800;
        color: #065f46;
    }
    .dark .spms-code {
        background-color: #064e3b;
        border-color: #047857;
        color: #6ee7b7;
    }

    /* FAQs Grid (Strict 2x3 fit without scrolling) */
    .spms-faq-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        flex: 1 1 auto;
    }
    .spms-faq-card {
        background-color: #fafafa;
        border: 1px solid #e4e4e7;
        border-radius: 10px;
        padding: 8px 12px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        height: 84px;
        box-sizing: border-box;
        overflow: hidden;
        transition: all 0.15s ease;
    }
    .dark .spms-faq-card {
        background-color: #1f1f23;
        border-color: #2e2e33;
    }
    .spms-faq-card:hover {
        border-color: #d4d4d8;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .dark .spms-faq-card:hover {
        border-color: #3f3f46;
    }
    .spms-faq-icon {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        background-color: #ffffff;
        border: 1px solid #e4e4e7;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .dark .spms-faq-icon {
        background-color: #18181b;
        border-color: #2e2e33;
    }
    .spms-faq-content {
        flex: 1;
        overflow: hidden;
    }
    .spms-faq-q {
        font-size: 11.5px;
        font-weight: 800;
        color: #09090b;
        margin: 0 0 2px 0;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .spms-faq-q {
        color: #ffffff;
    }
    .spms-faq-a {
        font-size: 11px;
        line-height: 1.35;
        color: #52525b;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .dark .spms-faq-a {
        color: #a1a1aa;
    }

    /* Search Results */
    .spms-guide-search-results {
        max-height: 380px;
        overflow-y: auto;
        padding: 14px 20px;
        background-color: #ffffff;
    }
    .dark .spms-guide-search-results {
        background-color: #18181b;
    }

    /* Footer */
    .spms-modal-footer {
        background-color: #fafafa;
        border-top: 1px solid #e4e4e7;
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .dark .spms-modal-footer {
        background-color: #121214 !important;
        border-top: 1px solid #27272a !important;
    }
    .spms-guide-footer-btn {
        background-color: #10b981;
        color: #ffffff;
        font-size: 11.5px;
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .spms-guide-footer-btn:hover {
        background-color: #059669;
    }
</style>

<script>
    let activeGuideRole = 'faculty';

    function switchGuideRole(role) {
        activeGuideRole = role;
        clearGuideSearch();

        const roles = ['faculty', 'supervisor', 'faqs'];
        roles.forEach(r => {
            const btn = document.getElementById('role-btn-' + r);
            const section = document.getElementById('guide-section-' + r);
            if (btn) {
                if (r === role) btn.classList.add('active');
                else btn.classList.remove('active');
            }
            if (section) {
                if (r === role) section.classList.add('active');
                else section.classList.remove('active');
            }
        });
    }

    function showFacultyStep(step) {
        for (let i = 1; i <= 3; i++) {
            const tab = document.getElementById('faculty-tab-' + i);
            const slide = document.getElementById('faculty-slide-' + i);
            if (tab) {
                if (i === step) tab.classList.add('active');
                else tab.classList.remove('active');
            }
            if (slide) {
                if (i === step) slide.classList.add('active');
                else slide.classList.remove('active');
            }
        }
    }

    function showSupervisorStep(step) {
        for (let i = 1; i <= 3; i++) {
            const tab = document.getElementById('supervisor-tab-' + i);
            const slide = document.getElementById('supervisor-slide-' + i);
            if (tab) {
                if (i === step) tab.classList.add('active');
                else tab.classList.remove('active');
            }
            if (slide) {
                if (i === step) slide.classList.add('active');
                else slide.classList.remove('active');
            }
        }
    }

    // Backward-compatibility wrapper for any external showGuideStep(n) calls
    function showGuideStep(step) {
        if (step === 1) {
            switchGuideRole('faculty');
            showFacultyStep(1);
        } else if (step === 2) {
            switchGuideRole('supervisor');
            showSupervisorStep(1);
        } else if (step === 3) {
            switchGuideRole('faculty');
            showFacultyStep(2);
        } else if (step === 4 || step === 5) {
            switchGuideRole('faculty');
            showFacultyStep(3);
        } else if (step >= 6) {
            switchGuideRole('faqs');
        }
    }

    function handleGuideSearch(query) {
        const q = (query || '').trim().toLowerCase();
        const resultsBox = document.getElementById('guideSearchResults');
        const mainBody = document.getElementById('guideMainBody');
        const clearBtn = document.getElementById('guideSearchClear');

        if (!q) {
            clearGuideSearch();
            return;
        }

        if (clearBtn) clearBtn.classList.remove('hidden');

        // Extract all FAQ cards and match against keywords or text
        const faqCards = document.querySelectorAll('.spms-faq-card');
        const matches = [];

        faqCards.forEach(card => {
            const keywords = (card.getAttribute('data-keywords') || '').toLowerCase();
            const text = card.innerText.toLowerCase();
            if (keywords.includes(q) || text.includes(q)) {
                matches.push(card.cloneNode(true));
            }
        });

        if (resultsBox && mainBody) {
            mainBody.classList.add('hidden');
            resultsBox.classList.remove('hidden');

            if (matches.length === 0) {
                resultsBox.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: #71717a;">
                        <svg class="w-8 h-8 mx-auto mb-2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <div style="font-weight: 800; font-size: 14px; color: #09090b; margin-bottom: 4px;" class="dark:text-white">No direct FAQ match found</div>
                        <div style="font-size: 12px;">Try searching for <em>"password"</em>, <em>"rubric"</em>, <em>"weights"</em>, <em>"locked"</em>, or <em>"drive"</em>.</div>
                    </div>
                `;
            } else {
                resultsBox.innerHTML = `
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #059669; margin-bottom: 10px;">
                        Found ${matches.length} matching answer${matches.length > 1 ? 's' : ''}:
                    </div>
                    <div class="spms-faq-grid"></div>
                `;
                const grid = resultsBox.querySelector('.spms-faq-grid');
                matches.forEach(m => grid.appendChild(m));
            }
        }
    }

    function clearGuideSearch() {
        const input = document.getElementById('guideSearchInput');
        const resultsBox = document.getElementById('guideSearchResults');
        const mainBody = document.getElementById('guideMainBody');
        const clearBtn = document.getElementById('guideSearchClear');

        if (input) input.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        if (resultsBox) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
        }
        if (mainBody) mainBody.classList.remove('hidden');
    }

    function openUserGuideModal() {
        switchGuideRole('faculty');
        showFacultyStep(1);
        document.getElementById('userGuideModal')?.classList.remove('hidden');
    }

    function closeUserGuideModal() {
        clearGuideSearch();
        document.getElementById('userGuideModal')?.classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('userGuideModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Escape') closeUserGuideModal();
        }
    });
</script>
