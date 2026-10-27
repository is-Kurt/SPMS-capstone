        <?php if ((!empty($allBasisGuides) || !empty($basisDoc)) && !$isGuide): ?>
        <!-- SUPERIOR BASIS STATIC FORM WORKSPACE -->
        <div id="spms-basis-workspace" class="hidden w-full h-full overflow-y-auto p-2 sm:p-6 lg:p-8 flex justify-center items-start custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
            <article id="basis-printable-sheet" class="spms-sheet-container block space-y-5">
                <!-- Top Reference Bar inside Paper -->
                <div class="flex items-center justify-between pb-2.5 border-b-2 border-sky-500 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 border border-sky-300">
                            Superior Basis Reference (Read-Only)
                        </span>
                        <span class="text-xs font-bold text-zinc-800" id="basis-header-doc-title">
                            <?= esc($basisDoc['title'] ?? '') ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php 
                            $basisTitleUpper = strtoupper($basisDoc['title'] ?? '');
                            $basisTypeUpper  = strtoupper($basisDoc['doc_type'] ?? '');
                            $isBasisOpcr = !empty($isParentDocOpcr) || str_contains($basisTitleUpper, 'OPCR') || ($basisTypeUpper === 'OPCR');
                            $isBasisDpcr = !empty($isParentDocDpcr) || str_contains($basisTitleUpper, 'DPCR') || str_contains($basisTitleUpper, 'DEPARTMENT') || str_contains($basisTitleUpper, 'DIVISION') || ($basisTypeUpper === 'DPCR');
                        ?>
                        <span id="basis-header-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold <?= $isParentTargetApproved ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' ?>">
                            <?= $isParentTargetApproved ? ($isBasisOpcr ? 'Institutional Basis' : 'Approved Targets') : 'Pending Approval' ?>
                        </span>
                        <a id="basis-header-open-link" href="<?= site_url('document/' . ($basisDoc['id'] ?? '')) ?>" target="_blank" 
                           class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold text-zinc-600 hover:text-sky-600 border border-zinc-300 hover:border-sky-300 transition-colors print:hidden"
                           title="Open in new window or tab">
                            <span>Open in New Tab ↗</span>
                        </a>
                    </div>
                </div>

                <!-- FORM TITLE -->
                <div style="text-align: center; margin-bottom: 14px;">
                    <h1 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase; color: #000000;" id="basis-sheet-title">
                        <?= esc($basisDoc['title'] ?? 'Office Performance Commitment and Review (OPCR)') ?>
                    </h1>
                </div>

                <!-- PREAMBLE (Filled Version of Template) -->
                <div style="margin-bottom: 18px; font-size: 11px; line-height: 1.6; text-align: justify; color: #000000;">
                    I, <input type="text" id="basis-val-ratee-name" readonly value="" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 170px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;">, 
                    <input type="text" id="basis-val-ratee-pos" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 200px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;"> of the 
                    <input type="text" id="basis-val-ratee-dept" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 170px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;">, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period 
                    <input type="text" id="basis-val-ratee-period" readonly value="" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 320px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;">.
                </div>

                <!-- APPROVER, RATEE, AND RATING SCALE MATRIX (Filled Version of Template) -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 18px; font-size: 11px;">
                    <tr>
                        <!-- Left: Approved By (Extreme Left-most) -->
                        <td style="vertical-align: top; border: none; padding: 0 20px 0 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div style="font-weight: bold; color: #000000;">APPROVED BY:</div>
                            </div>
                            <div id="basis-approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="approver-item" style="position: relative;">
                                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                        <tr>
                                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Name:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-name" readonly value="" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 60%; background: transparent; font-family: inherit;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Position:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-pos" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 80%; background: transparent; font-family: inherit;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Date:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-date" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit;">
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
                                        <input type="text" id="basis-val-ratee-sign" readonly value="" style="color: #000000; font-weight: bold; border: none; border-bottom: 1px solid #94a3b8; text-align: left; width: 240px; outline: none; font-size: 11px; padding: 2px 0; background: transparent; font-family: inherit;">
                                    </div>
                                    <div style="font-size: 11px; color: #000000; margin-top: 3px;" id="basis-val-ratee-sign-pos">Name of Employee</div>
                                    <div style="margin-top: 4px; font-size: 11px; color: #000000;">
                                        Date: <input type="text" id="basis-val-ratee-sign-date" readonly value="" style="border: none; border-bottom: 1px solid #94a3b8; width: 130px; outline: none; font-size: 11px; color: #000000; background: transparent; font-family: inherit;">
                                    </div>
                                </div>

                                <!-- Rating Scale -->
                                <div style="font-size: 10px; color: #000000;">
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
                    <table class="spms-table" style="width: 100%; border-collapse: collapse; border: 2px solid #000; font-size: 11px;">
                        <colgroup id="basis-table-colgroup">
                            <?php if ($isBasisOpcr || $isBasisDpcr): ?>
                            <col style="width: 15%;">
                            <col style="width: 17%;">
                            <col style="width: 11%;">
                            <col style="width: 13%;">
                            <col style="width: 18%;">
                            <col style="width: 3.5%;">
                            <col style="width: 3.5%;">
                            <col style="width: 3.5%;">
                            <col style="width: 4.5%;">
                            <col style="width: 11%;">
                            <?php else: ?>
                            <col style="width: 25%;">
                            <col style="width: 25%;">
                            <col style="width: 24%;">
                            <col style="width: 4%;">
                            <col style="width: 4%;">
                            <col style="width: 4%;">
                            <col style="width: 5%;">
                            <col style="width: 9%;">
                            <?php endif; ?>
                        </colgroup>
                        <thead id="basis-table-thead">
                            <?php if ($isBasisOpcr): ?>
                            <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT / PROGRAM / ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                            </tr>
                            <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                            <?php elseif ($isBasisDpcr): ?>
                            <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS / OFFICES ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                            </tr>
                            <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                            <?php else: ?>
                            <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal;">(Targets + Measures)</span></th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="padding: 4px; border: 1px solid #000;">RATING</th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">REMARKS</th>
                            </tr>
                            <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
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
                <div id="basis-html-fallback" class="hidden text-sm leading-relaxed p-4 bg-white text-zinc-800"></div>
            </article>
        </div>
        <?php endif; ?>
