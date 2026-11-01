        <?php if ((!empty($allBasisGuides) || !empty($basisDoc)) && !$isGuide): ?>
        <?php 
            $basisTitleUpper = strtoupper($basisDoc['title'] ?? '');
            $basisTypeUpper  = strtoupper($basisDoc['doc_type'] ?? '');
            $isBasisOpcr = !empty($isParentDocOpcr) || str_contains($basisTitleUpper, 'OPCR') || ($basisTypeUpper === 'OPCR');
            $isBasisDpcr = !empty($isParentDocDpcr) || str_contains($basisTitleUpper, 'DPCR') || str_contains($basisTitleUpper, 'DEPARTMENT') || str_contains($basisTitleUpper, 'DIVISION') || ($basisTypeUpper === 'DPCR');
        ?>
        <!-- SUPERIOR BASIS STATIC FORM WORKSPACE -->
        <div id="spms-basis-workspace" class="hidden w-fit min-w-full min-h-full p-2 sm:p-6 lg:p-8 flex flex-col items-center print:p-0 print:bg-white print:overflow-visible">
            <div class="w-full max-w-[1560px] flex flex-col items-center gap-4 print:w-full print:block">
                <article id="basis-printable-sheet" class="spms-sheet-container block space-y-5">
                <!-- Top Reference Bar inside Paper -->
                <div class="flex items-center justify-between pb-2.5 border-b-2 border-sky-500 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 dark:bg-sky-950/80 dark:text-sky-300 border border-sky-300 dark:border-sky-700">
                            Superior Basis Reference (Read-Only)
                        </span>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200" id="basis-header-doc-title">
                            <?= esc($basisDoc['title'] ?? '') ?>
                        </span>
                    </div>
                    <div class="basis-view-switch-container flex items-center gap-2 print:hidden">
                        <div class="inline-flex p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 shadow-2xs" role="group" aria-label="Basis View Switch">
                            <button type="button" 
                                    onclick="window.switchBasisView(window.currentBasisGuideIdx, 'form')" 
                                    class="basis-switch-btn basis-switch-form is-active"
                                    title="View Target Form">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Target Form</span>
                            </button>
                            <button type="button" 
                                    onclick="window.switchBasisView(window.currentBasisGuideIdx, 'rubrics')" 
                                    class="basis-switch-btn basis-switch-rubrics"
                                    title="View Rubrics Matrix">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Rubrics Matrix</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FORM TITLE -->
                <div style="text-align: center; margin-bottom: 14px;">
                    <h1 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;" id="basis-sheet-title">
                        <?= esc($basisDoc['title'] ?? 'Office Performance Commitment and Review (OPCR)') ?>
                    </h1>
                </div>

                <!-- PREAMBLE (Filled Version of Template) -->
                <div style="margin-bottom: 18px; font-size: 11px; line-height: 1.6; text-align: justify;">
                    I, <input type="text" id="basis-val-ratee-name" readonly value="" class="spms-meta-input" style="font-weight: bold; text-align: center; min-width: 170px; padding: 2px 4px; font-size: 11px;">, 
                    <span id="basis-roles-container">
                        <input type="text" id="basis-val-ratee-pos" readonly value="" class="spms-meta-input" style="text-align: center; min-width: 200px; padding: 2px 4px; font-size: 11px;"> of the 
                        <input type="text" id="basis-val-ratee-dept" readonly value="" class="spms-meta-input" style="text-align: center; min-width: 170px; padding: 2px 4px; font-size: 11px;">
                    </span>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period 
                    <input type="text" id="basis-val-ratee-period" readonly value="" class="spms-meta-input" style="font-weight: bold; text-align: center; min-width: 320px; padding: 2px 4px; font-size: 11px;">.
                </div>

                <!-- APPROVER, RATEE, AND RATING SCALE MATRIX (Filled Version of Template) -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 18px; font-size: 11px;">
                    <tr>
                        <!-- Left: Approved By (Extreme Left-most) -->
                        <td style="vertical-align: top; border: none; padding: 0 20px 0 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div style="font-weight: bold;">APPROVED BY:</div>
                            </div>
                            <div id="basis-approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="approver-item" style="position: relative;">
                                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                        <tr>
                                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold;">Name:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-name" readonly value="" class="spms-meta-input" style="font-weight: bold; font-size: 11px; width: 60%; padding: 2px 4px;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold;">Position:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-pos" readonly value="" class="spms-meta-input" style="font-size: 11px; width: 80%; padding: 2px 4px;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold;">Date:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-date" readonly value="" class="spms-meta-input" style="font-size: 11px; width: 130px; padding: 2px 4px;">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>

                        <!-- Right: Name of Employee & Rating Scale (Extreme Right-most) -->
                        <td style="vertical-align: top; border: none; padding: 0; text-align: right; width: 1%; white-space: nowrap;">
                            <div style="display: inline-block; text-align: left; min-width: 240px;">
                                <!-- Name of Employee Block -->
                                <div style="margin-bottom: 14px;">
                                    <div>
                                        <input type="text" id="basis-val-ratee-sign" readonly value="" class="spms-meta-input" style="font-weight: bold; text-align: left; width: 240px; font-size: 11px; padding: 2px 0;">
                                    </div>
                                    <div style="font-size: 11px; margin-top: 3px;" id="basis-val-ratee-sign-pos">Name of Employee</div>
                                    <div style="margin-top: 4px; font-size: 11px;">
                                        Date: <input type="text" id="basis-val-ratee-sign-date" readonly value="" class="spms-meta-input" style="width: 130px; font-size: 11px; padding: 2px 0;">
                                    </div>
                                </div>

                                <!-- Rating Scale -->
                                <div style="font-size: 10px;">
                                    <div style="font-weight: bold; margin-bottom: 3px;">Rating Scale:</div>
                                    <div style="display: flex; flex-direction: column; gap: 2px; line-height: 1.35;">
                                        <div>5 – Outstanding</div>
                                        <div>4 – Very Satisfactory</div>
                                        <div>3 – Satisfactory</div>
                                        <div>2 – Unsatisfactory</div>
                                        <div>1 – Poor</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- MAIN TABLE OF DELIVERABLES & RATINGS (Static Read-Only) -->
                <div class="spms-table-responsive-wrapper">
                    <div class="lg:hidden flex items-center justify-between text-[11px] text-zinc-500 bg-zinc-200 dark:bg-zinc-800/40 px-3 py-1.5 rounded border border-zinc-200 dark:border-zinc-700 mb-2 print-hide">
                        <span class="flex items-center gap-1 font-medium">↔ Swipe matrix horizontally to view all cascaded targets</span>
                        <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider" id="basis-col-count-badge"><?= ($isBasisOpcr || $isBasisDpcr) ? '10 Columns' : '8 Columns' ?></span>
                    </div>
                    <table class="spms-table" style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
                        <colgroup id="basis-table-colgroup">
                            <?php if ($isBasisOpcr || $isBasisDpcr): ?>
                            <col style="width: 17%;">
                            <col style="width: 21%;">
                            <col style="width: 10%;">
                            <col style="width: 11%;">
                            <col style="width: 15%;">
                            <col style="width: 3%;">
                            <col style="width: 3%;">
                            <col style="width: 3%;">
                            <col style="width: 4%;">
                            <col style="width: 13%;">
                            <?php else: ?>
                            <col style="width: 24%;">
                            <col style="width: 25%;">
                            <col style="width: 23%;">
                            <col style="width: 3.5%;">
                            <col style="width: 3.5%;">
                            <col style="width: 3.5%;">
                            <col style="width: 4.5%;">
                            <col style="width: 13%;">
                            <?php endif; ?>
                        </colgroup>
                        <thead id="basis-table-thead">
                            <?php if ($isBasisOpcr): ?>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT / PROGRAM / ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                            </tr>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                            <?php elseif ($isBasisDpcr): ?>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS / OFFICES ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                            </tr>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                            <?php else: ?>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal;">(Targets + Measures)</span></th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="padding: 4px; border: 1px solid #000;">RATING</th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">REMARKS</th>
                            </tr>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; font-size: 10px;">
                                <th style="padding: 4px; border: 1px solid #000;">Q</th>
                                <th style="padding: 4px; border: 1px solid #000;">T</th>
                                <th style="padding: 4px; border: 1px solid #000;">E</th>
                                <th style="padding: 4px; border: 1px solid #000;">Ave.</th>
                            </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody id="basis-tbody-core"></tbody>
                        <tbody id="basis-tbody-strategic"></tbody>
                        <tbody id="basis-tbody-support"></tbody>
                    </table>
                </div>

                <!-- Custom HTML Container for older legacy guidelines if applicable -->
                <div id="basis-html-fallback" class="hidden text-sm leading-relaxed p-4 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200"></div>
            </article>

            <!-- SUPERIOR BASIS RUBRICS MATRIX SHEET (Read-Only) -->
            <article id="basis-rubrics-sheet" class="spms-sheet-container hidden space-y-5" style="display: none;">
                <!-- Top Reference Bar inside Paper -->
                <div class="flex items-center justify-between pb-2.5 border-b-2 border-sky-500 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-300 dark:border-sky-700">
                            Superior Basis Reference (Read-Only)
                        </span>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200" id="basis-rubrics-header-doc-title">
                            <?= esc($basisDoc['title'] ?? '') ?>
                        </span>
                    </div>
                    <div class="basis-view-switch-container flex items-center gap-2 print:hidden">
                        <div class="inline-flex p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 shadow-2xs" role="group" aria-label="Basis View Switch">
                            <button type="button" 
                                    onclick="window.switchBasisView(window.currentBasisGuideIdx, 'form')" 
                                    class="basis-switch-btn basis-switch-form"
                                    title="View Target Form">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Target Form</span>
                            </button>
                            <button type="button" 
                                    onclick="window.switchBasisView(window.currentBasisGuideIdx, 'rubrics')" 
                                    class="basis-switch-btn basis-switch-rubrics is-active"
                                    title="View Rubrics Matrix">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Rubrics Matrix</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TITLE -->
                <div style="text-align: center; margin-bottom: 14px;">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #0284c7; letter-spacing: 0.05em; margin-bottom: 2px;" id="basis-rubrics-superior-name">
                        SUPERIOR RUBRICS MATRIX REFERENCE
                    </div>
                    <h1 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;" id="basis-rubrics-sheet-title">
                        SUCCESS INDICATORS &amp; RATING STANDARDS MATRIX
                    </h1>
                    <div style="font-size: 11px; font-style: italic;" class="text-zinc-500 dark:text-zinc-400">
                        Rating Scale: 5 – Outstanding &bull; 4 – Very Satisfactory &bull; 3 – Satisfactory &bull; 2 – Unsatisfactory &bull; 1 – Poor
                    </div>
                </div>

                <!-- RUBRICS TABLE -->
                <div class="spms-table-responsive-wrapper">
                    <table class="spms-table" style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 32%;">
                            <col style="width: 11%;">
                            <col style="width: 19%;">
                            <col style="width: 19%;">
                            <col style="width: 19%;">
                        </colgroup>
                        <thead>
                            <tr class="basis-thead-tr" style="text-align: center; font-weight: bold; font-size: 10px;">
                                <th style="padding: 8px 6px; border: 1px solid #000; text-align: left; width: 32%;">
                                    OFFICE PPA / MAJOR FINAL OUTPUT / EXPECTED OUTPUTS
                                </th>
                                <th style="padding: 8px 4px; border: 1px solid #000; text-align: center; width: 11%;">
                                    RATING
                                </th>
                                <th style="padding: 8px 6px; border: 1px solid #000; text-align: left; width: 19%;">
                                    Q (QUALITY)
                                </th>
                                <th style="padding: 8px 6px; border: 1px solid #000; text-align: left; width: 19%;">
                                    T (TIMELINESS)
                                </th>
                                <th style="padding: 8px 6px; border: 1px solid #000; text-align: left; width: 19%;">
                                    E (EFFICIENCY)
                                </th>
                            </tr>
                        </thead>
                        <tbody id="basis-rubrics-tbody">
                            <!-- Populated dynamically by JS -->
                        </tbody>
                    </table>
                </div>
            </article>
            </div>
        </div>
        <?php endif; ?>
