<!--
    BSU SPMS Interactive User Guide & Institutional Reference Modal
    Accessible globally from the header and workflow viewports.
-->
<div id="userGuideModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeUserGuideModal()">
    <div class="spms-modal-dialog" style="max-width: 820px;">
        <!-- Header -->
        <div class="spms-modal-header">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-[#f59e0b] block mb-0.5">BSU SPMS VISUAL GUIDE</span>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white m-0">How to Complete Your Performance Paper</h3>
            </div>
            <button type="button" onclick="closeUserGuideModal()" class="spms-modal-btn-close" title="Close Guide">
                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="spms-modal-body custom-scrollbar">
            
            <!-- 5 Interactive Segmented Tabs -->
            <div class="spms-guide-tabs">
                <div id="guide-tab-1" class="spms-guide-tab active" onclick="showGuideStep(1)">
                    <div class="spms-guide-tab-badge">1</div>
                    <div class="spms-guide-tab-title">Draft Targets</div>
                </div>
                <div id="guide-tab-2" class="spms-guide-tab" onclick="showGuideStep(2)">
                    <div class="spms-guide-tab-badge">2</div>
                    <div class="spms-guide-tab-title">Submit Review</div>
                </div>
                <div id="guide-tab-3" class="spms-guide-tab" onclick="showGuideStep(3)">
                    <div class="spms-guide-tab-badge">3</div>
                    <div class="spms-guide-tab-title">Attach MOVs</div>
                </div>
                <div id="guide-tab-4" class="spms-guide-tab" onclick="showGuideStep(4)">
                    <div class="spms-guide-tab-badge">4</div>
                    <div class="spms-guide-tab-title">Export & Print</div>
                </div>
                <div id="guide-tab-5" class="spms-guide-tab" onclick="showGuideStep(5)">
                    <div class="spms-guide-tab-badge">5</div>
                    <div class="spms-guide-tab-title">Forms & Cycle</div>
                </div>
            </div>

            <!-- SLIDE 1: DRAFT TARGETS -->
            <div id="guide-slide-1" class="spms-guide-slide active">
                <!-- Visual Mockup Canvas -->
                <div class="spms-guide-canvas">
                    <!-- Mini Window Chrome -->
                    <div class="spms-mock-chrome">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: #ef4444; display: inline-block;"></span>
                            <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: #f59e0b; display: inline-block;"></span>
                            <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: #10b981; display: inline-block;"></span>
                            <span class="spms-mock-chrome-title">BSU SPMS • Interactive Performance Grid</span>
                        </div>
                        <span class="spms-mock-autosave">
                            ● Auto-Save Active
                        </span>
                    </div>
                    <!-- Mini Spreadsheet Grid -->
                    <div class="spms-mock-table-box">
                        <div class="spms-mock-thead">
                            <div>Major Final Output (MFO)</div>
                            <div>Success Indicators (Q, E, T)</div>
                            <div>Target Commitment</div>
                        </div>
                        <div class="spms-mock-trow-1">
                            <div style="font-weight: 600;">Higher Education Services</div>
                            <div class="spms-mock-cell-sub">100% of course syllabi submitted on time</div>
                            <div class="spms-mock-target-input">
                                <span>100% achieved</span>
                                <span style="color: #f59e0b; font-weight: 900; animation: blink 1s infinite;">|</span>
                            </div>
                        </div>
                        <div class="spms-mock-trow-2">
                            <div>Research & Innovation</div>
                            <div class="spms-mock-cell-sub">Target research publications completed</div>
                            <div class="spms-mock-cell-sub2">2 papers published</div>
                        </div>
                    </div>
                    <!-- Floating Action Pointer -->
                    <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                        <div style="background-color: #f59e0b; color: #000000; font-size: 10px; font-weight: 900; padding: 6px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Save Changes</span>
                        </div>
                    </div>
                </div>

                <!-- Step Description Card -->
                <div class="spms-guide-instruction-card">
                    <h4 style="font-size: 13px; font-weight: 800; color: #f59e0b; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <span>Step 1: Open & Draft Your Commitments</span>
                    </h4>
                    <ul style="margin: 0; padding-left: 18px; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                        <li><strong>Action:</strong> Click the bold golden <span class="text-amber-600 dark:text-[#f59e0b] font-bold">OPEN & EDIT PAPER</span> button on your dashboard.</li>
                        <li><strong>Editing:</strong> Click directly into any table cell to enter your Major Final Outputs (MFOs), targets, and success indicators.</li>
                        <li><strong>Autosave:</strong> Every target and rating is saved directly to your official university performance record.</li>
                    </ul>
                </div>
            </div>

            <!-- SLIDE 2: SUBMIT FOR REVIEW -->
            <div id="guide-slide-2" class="spms-guide-slide">
                <!-- Visual Mockup Canvas -->
                <div class="spms-guide-canvas">
                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px; align-items: center;">
                        <!-- Node 1: Ratee Folder -->
                        <div class="spms-mock-card">
                            <span style="font-size: 10px; font-weight: 700; color: #f59e0b; display: block; margin-bottom: 4px;">YOUR COMMITMENTS</span>
                            <div class="spms-mock-card-title">Target Setting Complete</div>
                            <span class="spms-mock-pill-green">
                                Click "Submit Targets"
                            </span>
                        </div>
                        <!-- Arrow Connector -->
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <svg width="48" height="16" viewBox="0 0 48 16" fill="none">
                                <line x1="0" y1="8" x2="38" y2="8" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                <polygon points="36,4 46,8 36,12" fill="#f59e0b" />
                            </svg>
                            <span style="font-size: 9px; color: #f59e0b; font-weight: 700; margin-top: 4px;">Instant Routing</span>
                        </div>
                        <!-- Node 2: Supervisor Approval -->
                        <div class="spms-mock-card">
                            <span style="font-size: 10px; font-weight: 700; color: #047857; display: block; margin-bottom: 4px;" class="dark:text-[#34d399]">SUPERVISOR / EVALUATOR</span>
                            <div class="spms-mock-card-title">Review & Validation</div>
                            <span class="spms-mock-pill-green">
                                • TARGET APPROVED
                            </span>
                        </div>
                    </div>
                    <!-- Cascading Rule Banner -->
                    <div class="spms-mock-banner-green">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-[#34d399] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div style="font-size: 11px;">
                            <strong style="color: #047857;" class="dark:text-[#34d399]">Institutional Cascading Rule:</strong> Approved superior OPCR commitments cascade downward to provide the mandatory reference basis for subordinates' DPCR/IPCR papers.
                        </div>
                    </div>
                </div>

                <!-- Step Description Card -->
                <div class="spms-guide-instruction-card">
                    <h4 style="font-size: 13px; font-weight: 800; color: #f59e0b; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <span>Step 2: Submit Targets for Superior Approval</span>
                    </h4>
                    <ul style="margin: 0; padding-left: 18px; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                        <li><strong>Submission:</strong> Click <span class="text-emerald-600 dark:text-[#34d399] font-bold">Submit Targets</span> in your paper toolbar once all initial targets are entered.</li>
                        <li><strong>Notification:</strong> Your designated supervisor (Dean, Chair, Director, or VPAA) receives an instant notification to review and validate your targets.</li>
                        <li><strong>Locking:</strong> Once approved, the target commitments lock in and the cycle advances to the Evaluation phase.</li>
                    </ul>
                </div>
            </div>

            <!-- SLIDE 3: ATTACH MOVS -->
            <div id="guide-slide-3" class="spms-guide-slide">
                <!-- Visual Mockup Canvas -->
                <div class="spms-guide-canvas">
                    <!-- Mini Row with Paperclip -->
                    <div class="spms-mock-row-attach">
                        <div class="spms-mock-row-text">
                            Syllabi & Curriculum Targets (AY 2026–2027)
                        </div>
                        <div class="spms-mock-pill-green" style="display: flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 6px; font-size: 10px;">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Attach MOVs (2 Files)</span>
                        </div>
                    </div>
                    <!-- Mini Attached Files List -->
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="spms-mock-file-card">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg class="w-4 h-4 text-slate-400 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <div>
                                    <div class="spms-mock-file-name">Approved_Curriculum_Syllabi.pdf</div>
                                    <div class="spms-mock-file-meta">1.4 MB • Uploaded Sept 15, 2026</div>
                                </div>
                            </div>
                            <span class="spms-mock-pill-green" style="border-radius: 4px; padding: 2px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                <svg style="width: 10px; height: 10px;" class="text-emerald-700 dark:text-[#00df82] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Verified MOV</span>
                            </span>
                        </div>
                        <div class="spms-mock-file-card">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg class="w-4 h-4 text-slate-400 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <div>
                                    <div class="spms-mock-file-name">Dean_Department_Endorsement.pdf</div>
                                    <div class="spms-mock-file-meta">820 KB • Uploaded Sept 15, 2026</div>
                                </div>
                            </div>
                            <span class="spms-mock-pill-green" style="border-radius: 4px; padding: 2px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                <svg style="width: 10px; height: 10px;" class="text-emerald-700 dark:text-[#00df82] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Verified MOV</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Step Description Card -->
                <div class="spms-guide-instruction-card">
                    <h4 style="font-size: 13px; font-weight: 800; color: #059669; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <span>Step 3: Attach Supporting Evidence & MOVs</span>
                    </h4>
                    <ul style="margin: 0; padding-left: 18px; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                        <li><strong>Attachment Trigger:</strong> In your paper, click the <span class="text-emerald-600 dark:text-[#34d399] font-bold">paperclip icon</span> on any target commitment row.</li>
                        <li><strong>Accepted Files:</strong> Upload official memos, attendance logs, published articles, certificates, or student evaluations.</li>
                        <li><strong>Audit Proof:</strong> Evaluators, TWG, and PMT calibrate your final ratings by reviewing these attached files.</li>
                    </ul>
                </div>
            </div>

            <!-- SLIDE 4: EXPORT & PRINT -->
            <div id="guide-slide-4" class="spms-guide-slide">
                <!-- Visual Mockup Canvas -->
                <div class="spms-guide-canvas">
                    <!-- CSC Document Header Mockup -->
                    <div class="spms-mock-card" style="margin-bottom: 12px;">
                        <span style="font-size: 9px; font-weight: 800; letter-spacing: 0.1em; color: #f59e0b; text-transform: uppercase;">REPUBLIC OF THE PHILIPPINES • CIVIL SERVICE COMMISSION</span>
                        <div class="spms-mock-card-title" style="font-size: 13px; font-weight: 900; margin: 4px 0;">BENGUET STATE UNIVERSITY SPMS FORM</div>
                        <span class="spms-mock-file-meta" style="font-size: 10px;">Official Institutional Rating Summary with Signature Blocks</span>
                    </div>
                    <!-- Two Action Buttons Preview -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div class="spms-mock-action-card">
                            <div class="flex items-center justify-center mb-1 text-emerald-700 dark:text-[#00df82]">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div class="spms-mock-action-title">Export Excel (.xlsx)</div>
                            <div class="spms-mock-action-sub">Formula-ready CSC template</div>
                        </div>
                        <div class="spms-mock-action-card">
                            <div class="flex items-center justify-center mb-1 text-emerald-700 dark:text-[#00df82]">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            </div>
                            <div class="spms-mock-action-title">Print / PDF (.pdf)</div>
                            <div class="spms-mock-action-sub">Formatted for hardcopy routing</div>
                        </div>
                    </div>
                    <!-- Signature Blocks Mockup -->
                    <div class="spms-mock-sign-grid">
                        <div>Ratee Signature</div>
                        <div>Immediate Supervisor</div>
                        <div>Head of Agency Approval</div>
                    </div>
                </div>

                <!-- Step Description Card -->
                <div class="spms-guide-instruction-card">
                    <h4 style="font-size: 13px; font-weight: 800; color: #f59e0b; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <span>Step 4: Official CSC Excel Export & Printing</span>
                    </h4>
                    <ul style="margin: 0; padding-left: 18px; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                        <li><strong>Export Excel:</strong> Automatically compiles and exports your performance commitments into an official CSC-standard spreadsheet.</li>
                        <li><strong>Print / PDF:</strong> Launches the high-resolution print view formatted specifically for institutional routing and physical signing.</li>
                        <li><strong>Submission:</strong> Submit your signed copies to the PMT / HRMO for institutional accreditation and CSC compliance.</li>
                    </ul>
                </div>
            </div>

            <!-- SLIDE 5: FORMS & 4-STAGE CYCLE DIRECTORY -->
            <div id="guide-slide-5" class="spms-guide-slide">
                <div class="spms-guide-matrix-grid">
                    <!-- Form Types Matrix -->
                    <div class="spms-guide-matrix-card">
                        <h5 class="spms-guide-matrix-header text-amber-600 dark:text-[#f59e0b]">
                            <svg class="w-4 h-4 text-amber-600 dark:text-[#f59e0b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span>SPMS Performance Papers</span>
                        </h5>
                        <div class="spms-guide-matrix-list">
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">OPCR (Office Performance)</span>
                                <p class="spms-guide-matrix-desc">Executive, Vice Presidents, Campus Directors</p>
                            </div>
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">DPCR (Division / Department)</span>
                                <p class="spms-guide-matrix-desc">College Deans & Department Chairs</p>
                            </div>
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">IPCR (Individual Performance)</span>
                                <p class="spms-guide-matrix-desc">Teaching Faculty & Academic Staff</p>
                            </div>
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">IPERF (Non-Teaching Staff)</span>
                                <p class="spms-guide-matrix-desc">Administrative, Technical, and Support Personnel</p>
                            </div>
                        </div>
                    </div>

                    <!-- 4-Stage Cycle Matrix -->
                    <div class="spms-guide-matrix-card">
                        <h5 class="spms-guide-matrix-header text-emerald-600 dark:text-[#34d399]">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-[#34d399] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span>The 4-Stage CSC Cycle</span>
                        </h5>
                        <div class="spms-guide-matrix-list">
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">Stage 1: Performance Planning</span>
                                <p class="spms-guide-matrix-desc">Formulate success indicators & target commitments</p>
                            </div>
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">Stage 2: Monitoring & Coaching</span>
                                <p class="spms-guide-matrix-desc">Continuous tracking and MOV attachment throughout semester</p>
                            </div>
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">Stage 3: Review & Evaluation</span>
                                <p class="spms-guide-matrix-desc">Scoring with QET formulas & supervisor calibration</p>
                            </div>
                            <div class="spms-guide-matrix-item">
                                <span class="spms-guide-matrix-title">Stage 4: Rewarding & Development</span>
                                <p class="spms-guide-matrix-desc">PBB, promotion eligibility, and faculty development</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mandatory CSC 5-Year Retention Compliance Note (Visible on all slides) -->
            <div class="spms-guide-retention-banner">
                <svg class="w-4 h-4 text-emerald-700 dark:text-[#34d399] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                <div style="line-height: 1.4;">
                    <strong>CSC 5-Year Record Retention:</strong> Pursuant to CSC & National Archives of the Philippines (NAP) policies, all submitted performance commitments and MOVs are preserved for five (5) years for institutional audit and accreditation.
                </div>
            </div>

        </div>

        <!-- Footer Navigation Controls -->
        <div class="spms-modal-footer">
            <div id="guide-step-indicator" style="font-size: 11px; font-weight: 800; color: #5a8b73;">
                Step 1 of 5: Draft Targets
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button id="guide-btn-prev" type="button" onclick="prevGuideStep()" class="spms-hub-btn-secondary" style="padding: 9px 18px !important; font-size: 11px !important; display: none;">
                    ← Previous
                </button>
                <button id="guide-btn-next" type="button" onclick="nextGuideStep()" class="spms-hub-btn-primary" style="padding: 10px 22px !important; font-size: 11px !important;">
                    Next Step →
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* SPMS Modal Design System */
    .spms-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
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
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        color: #0f172a;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    .dark .spms-modal-dialog {
        background-color: #032115 !important;
        border: 1px solid #0d4a32 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85) !important;
        color: #ffffff !important;
    }
    .spms-modal-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .dark .spms-modal-header {
        background-color: #02170f !important;
        border-bottom: 1px solid #0d4a32 !important;
    }
    .spms-modal-body {
        background-color: #ffffff;
        padding: 24px;
        overflow-y: auto;
        flex: 1 1 auto;
    }
    .dark .spms-modal-body {
        background-color: #032115 !important;
    }
    .spms-modal-footer {
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .dark .spms-modal-footer {
        background-color: #02170f !important;
        border-top: 1px solid #0d4a32 !important;
    }
    .spms-modal-btn-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .spms-modal-btn-close:hover {
        background-color: #e2e8f0;
        color: #0f172a;
    }
    .dark .spms-modal-btn-close {
        background-color: #083b27 !important;
        border: 1px solid #11593b !important;
        color: #94a3b8 !important;
    }
    .dark .spms-modal-btn-close:hover {
        background-color: #0c4d33 !important;
        color: #ffffff !important;
        border-color: #176a46 !important;
    }

    /* Interactive Stepper Carousel Styles */
    .spms-guide-tabs {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        margin-bottom: 20px;
    }
    @media (max-width: 640px) {
        .spms-guide-tabs {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    .spms-guide-tab {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s ease;
        text-align: left;
    }
    .spms-guide-tab:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }
    .spms-guide-tab.active {
        background-color: #ecfdf5;
        border-color: #10b981;
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);
    }
    .dark .spms-guide-tab {
        background-color: #062e1e !important;
        border: 1px solid #0d4a32 !important;
    }
    .dark .spms-guide-tab:hover {
        background-color: #083b27 !important;
        border-color: #156643 !important;
    }
    .dark .spms-guide-tab.active {
        background-color: #083b27 !important;
        border-color: #f59e0b !important;
        box-shadow: 0 0 12px rgba(245, 158, 11, 0.25) !important;
    }
    .spms-guide-tab-badge {
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        background-color: #e2e8f0;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .spms-guide-tab.active .spms-guide-tab-badge {
        background-color: #10b981;
        border-color: #10b981;
        color: #ffffff;
    }
    .dark .spms-guide-tab-badge {
        background-color: #032115 !important;
        border: 1px solid #0d4a32 !important;
        color: #82c8a6 !important;
    }
    .dark .spms-guide-tab.active .spms-guide-tab-badge {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #000000 !important;
    }
    .spms-guide-tab-title {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        line-height: 1.2;
    }
    .spms-guide-tab.active .spms-guide-tab-title {
        color: #047857;
    }
    .dark .spms-guide-tab-title {
        color: #94a3b8 !important;
    }
    .dark .spms-guide-tab.active .spms-guide-tab-title {
        color: #ffffff !important;
    }
    .spms-guide-slide {
        display: none;
    }
    .spms-guide-slide.active {
        display: block;
        animation: spmsFadeSlideIn 0.2s ease-out;
    }
    @keyframes spmsFadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .spms-guide-canvas {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
    }
    .dark .spms-guide-canvas {
        background-color: #02170f !important;
        border: 1px solid #0d4a32 !important;
    }

    /* Mockup Canvas UI Elements - Light & Dark Theme Adaptivity */
    .spms-mock-chrome {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    .dark .spms-mock-chrome {
        border-bottom-color: #0d4a32 !important;
    }
    .spms-mock-chrome-title {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        margin-left: 8px;
    }
    .dark .spms-mock-chrome-title {
        color: #94a3b8 !important;
    }
    .spms-mock-autosave {
        font-size: 10px;
        font-weight: 700;
        color: #047857;
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 2px 8px;
        border-radius: 9999px;
    }
    .dark .spms-mock-autosave {
        color: #34d399 !important;
        background-color: #083b27 !important;
        border-color: #10593b !important;
    }

    /* Table Mockup (Slide 1) */
    .spms-mock-table-box {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
        background-color: #ffffff;
        font-size: 11px;
    }
    .dark .spms-mock-table-box {
        border-color: #0d4a32 !important;
        background-color: #032115 !important;
    }
    .spms-mock-thead {
        display: grid;
        grid-template-columns: 1.5fr 2fr 1.5fr;
        background-color: #f1f5f9;
        border-bottom: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-weight: 800;
        color: #065f46;
        font-size: 10px;
        text-transform: uppercase;
    }
    .dark .spms-mock-thead {
        background-color: #062e1e !important;
        border-bottom-color: #0d4a32 !important;
        color: #82c8a6 !important;
    }
    .spms-mock-trow-1 {
        display: grid;
        grid-template-columns: 1.5fr 2fr 1.5fr;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
        align-items: center;
    }
    .dark .spms-mock-trow-1 {
        border-bottom-color: #0d4a32 !important;
        color: #e2e8f0 !important;
    }
    .spms-mock-trow-2 {
        display: grid;
        grid-template-columns: 1.5fr 2fr 1.5fr;
        padding: 10px 12px;
        color: #64748b;
        align-items: center;
    }
    .dark .spms-mock-trow-2 {
        color: #64748b !important;
    }
    .spms-mock-cell-sub {
        color: #64748b;
        font-size: 10px;
    }
    .dark .spms-mock-cell-sub {
        color: #94a3b8 !important;
    }
    .spms-mock-cell-sub2 {
        color: #047857;
    }
    .dark .spms-mock-cell-sub2 {
        color: #5a8b73 !important;
    }
    .spms-mock-target-input {
        border: 1.5px solid #f59e0b;
        background-color: #fffbeb;
        padding: 4px 8px;
        border-radius: 6px;
        color: #92400e;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dark .spms-mock-target-input {
        background-color: #083b27 !important;
        color: #ffffff !important;
    }

    /* Cards / Nodes Mockups (Slide 2, 3, 4) */
    .spms-mock-card {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 14px;
        text-align: center;
    }
    .dark .spms-mock-card {
        background-color: #032115 !important;
        border-color: #0d4a32 !important;
    }
    .spms-mock-card-title {
        font-size: 12px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .dark .spms-mock-card-title {
        color: #ffffff !important;
    }
    .spms-mock-pill-green {
        font-size: 9px;
        font-weight: 800;
        background-color: #ecfdf5;
        color: #047857;
        padding: 4px 10px;
        border-radius: 9999px;
        border: 1px solid #a7f3d0;
        display: inline-block;
    }
    .dark .spms-mock-pill-green {
        background-color: #083b27 !important;
        color: #34d399 !important;
        border-color: #10593b !important;
    }
    .spms-mock-banner-green {
        margin-top: 14px;
        padding: 10px 14px;
        border-radius: 8px;
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #065f46;
    }
    .dark .spms-mock-banner-green {
        background-color: #062e1e !important;
        border-color: #0d4a32 !important;
        color: #cbd5e1 !important;
    }

    /* Attachments Mockup (Slide 3) */
    .spms-mock-row-attach {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .dark .spms-mock-row-attach {
        background-color: #032115 !important;
        border-color: #0d4a32 !important;
    }
    .spms-mock-row-text {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
    }
    .dark .spms-mock-row-text {
        color: #ffffff !important;
    }
    .spms-mock-file-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dark .spms-mock-file-card {
        background-color: #062e1e !important;
        border-color: #0d4a32 !important;
    }
    .spms-mock-file-name {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
    }
    .dark .spms-mock-file-name {
        color: #ffffff !important;
    }
    .spms-mock-file-meta {
        font-size: 9px;
        color: #64748b;
    }
    .dark .spms-mock-file-meta {
        color: #5a8b73 !important;
    }

    /* Export & Print Mockup (Slide 4) */
    .spms-mock-action-card {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .dark .spms-mock-action-card {
        background-color: #083b27 !important;
        border-color: #11593b !important;
    }
    .spms-mock-action-title {
        font-size: 11px;
        font-weight: 800;
        color: #047857;
    }
    .dark .spms-mock-action-title {
        color: #ffffff !important;
    }
    .spms-mock-action-sub {
        font-size: 9px;
        color: #065f46;
        margin-top: 2px;
    }
    .dark .spms-mock-action-sub {
        color: #82c8a6 !important;
    }
    .spms-mock-sign-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
        text-align: center;
        border-top: 1px dashed #cbd5e1;
        padding-top: 10px;
        font-size: 9px;
        color: #64748b;
    }
    .dark .spms-mock-sign-grid {
        border-top-color: #0d4a32 !important;
        color: #5a8b73 !important;
    }
    .spms-guide-instruction-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 18px;
    }
    .dark .spms-guide-instruction-card {
        background-color: #062e1e !important;
        border: 1px solid #0d4a32 !important;
    }
    .spms-guide-instruction-card ul {
        color: #334155;
    }
    .dark .spms-guide-instruction-card ul {
        color: #cbd5e1 !important;
    }
    .spms-guide-instruction-card strong {
        color: #0f172a;
    }
    .dark .spms-guide-instruction-card strong {
        color: #ffffff !important;
    }
    .spms-guide-retention-banner {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        margin-top: 16px;
        padding: 10px 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
    }
    .dark .spms-guide-retention-banner {
        background-color: #062e1e !important;
        border: 1px solid #0d4a32 !important;
        color: #cbd5e1 !important;
    }
    .spms-guide-retention-banner strong {
        color: #064e3b;
    }
    .dark .spms-guide-retention-banner strong {
        color: #ffffff !important;
    }

    /* Slide 5 Matrix Styling */
    .spms-guide-matrix-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }
    @media (max-width: 640px) {
        .spms-guide-matrix-grid {
            grid-template-columns: 1fr;
        }
    }
    .spms-guide-matrix-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }
    .dark .spms-guide-matrix-card {
        background-color: #062e1e !important;
        border: 1px solid #0d4a32 !important;
    }
    .spms-guide-matrix-header {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .spms-guide-matrix-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .spms-guide-matrix-item {
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .spms-guide-matrix-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .dark .spms-guide-matrix-item {
        border-bottom: 1px solid rgba(16, 89, 59, 0.6) !important;
    }
    .dark .spms-guide-matrix-item:last-child {
        border-bottom: none !important;
    }
    .spms-guide-matrix-title {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
        display: block;
    }
    .dark .spms-guide-matrix-title {
        color: #ffffff !important;
    }
    .spms-guide-matrix-desc {
        font-size: 10px;
        color: #64748b;
        margin: 2px 0 0 0;
        line-height: 1.4;
    }
    .dark .spms-guide-matrix-desc {
        color: #8ea396 !important;
    }
</style>

<script>
    let currentGuideStep = 1;
    const totalGuideSteps = 5;
    const guideStepTitles = [
        '', 
        'Draft Targets', 
        'Submit Targets', 
        'Attach Evidence (MOVs)', 
        'Export & Print',
        'Forms & 4-Stage Cycle'
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
                nextBtn.innerText = 'Got It, Start Working';
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
