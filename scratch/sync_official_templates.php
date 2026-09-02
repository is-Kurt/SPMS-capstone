<?php
// Script to synchronize all templates and documents with the official expanded BSU SPMS format
$db = new SQLite3('writable/database/spms_db.sqlite3');

function getOfficialSpmsHtml($docTitle, $weights = ['core' => 0.70, 'strategic' => 0.20, 'support' => 0.10], $coreLabel = '1. Core Functions — Instruction & Teaching Load (70%)') {
    $corePct = ($weights['core'] * 100) . '%';
    $stratPct = ($weights['strategic'] * 100) . '%';
    $suppPct = ($weights['support'] * 100) . '%';

    return <<<HTML
<article id="printable-form" class="spms-sheet-container flex flex-col gap-5">
    <!-- INSTITUTIONAL FORM HEADER -->
    <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px;">
        <h1 style="font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 6px 0;" id="spms-doc-title">
            {$docTitle}
        </h1>
        <p style="font-size: 11px; color: #334155; margin: 6px 0 0 0; line-height: 1.6;">
            I, <input type="text" id="ratee-name" value="" placeholder="Full Name Here" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
            <input type="text" id="ratee-position" value="" placeholder="Position & Designation" style="color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;"> 
            of the <input type="text" id="ratee-dept" value="" placeholder="Office / College Name" style="color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
            commit to deliver and agree to be rated on the attainment of faculty targets for 
            <input type="text" id="ratee-period" value="" placeholder="Period (e.g. 1st Semester, AY 2026–2027)" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 220px; outline: none; padding: 2px 4px;">.
        </p>
    </div>

    <!-- APPROVER, RATEE, AND RATING SCALE MATRIX -->
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
        <tr>
            <td style="width: 35%; border: 1px solid #000; padding: 10px; vertical-align: top;">
                <div style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 8px;">
                    Approved by:
                </div>
                <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                    <tr>
                        <td style="width: 60px; color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Name:</td>
                        <td style="border: none; padding: 3px 0;">
                            <input type="text" id="approver-name" value="" placeholder="Dean / Head of Office" style="width: 100%; font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Position:</td>
                        <td style="border: none; padding: 3px 0;">
                            <input type="text" id="approver-pos" value="" placeholder="Designation" style="width: 100%; color: #1e293b; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Date:</td>
                        <td style="border: none; padding: 3px 0;">
                            <input type="text" id="approver-date" value="" placeholder="Date Approved" style="width: 100%; color: #1e293b; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 35%; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                    <input type="text" id="ratee-sign-name" value="" placeholder="Name of Faculty / Ratee" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                    <span style="color: #334155; font-size: 10px; font-weight: 600; margin-top: 4px;">Faculty Member / Ratee</span>
                    <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                        Date: <input type="text" id="ratee-sign-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 100px; outline: none; font-size: 11px;">
                    </div>
                </div>
            </td>

            <td style="width: 30%; border: 1px solid #000; padding: 8px 12px; background: #f8fafc; vertical-align: top; font-size: 10px;">
                <div style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 4px;">
                    Rating Scale (Annex E):
                </div>
                <div style="display: flex; flex-direction: column; gap: 2px; color: #334155;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold;"><span>5 — Outstanding</span> <span>(4.500 – 5.000)</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>4 — Very Satisfactory</span> <span>(3.500 – 4.499)</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>3 — Satisfactory</span> <span>(2.500 – 3.499)</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>2 — Unsatisfactory</span> <span>(1.500 – 2.499)</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>1 / 0 — Poor / Unmet</span> <span>(Below 1.499)</span></div>
                </div>
            </td>
        </tr>
    </table>

    <!-- CORE SPMS EVALUATION MATRIX -->
    <div style="overflow-x: auto; width: 100%;">
        <table class="spms-table">
            <colgroup>
                <col style="width: 24%;">
                <col style="width: 24%;">
                <col style="width: 24%;">
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 5%;">
                <col style="width: 12%;">
                <col style="width: 3%;">
            </colgroup>

            <!-- Two-Row Header: Q, T, E, Ave side-by-side -->
            <thead>
                <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                    <th rowspan="2" style="padding: 8px;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                    <th rowspan="2" style="padding: 8px;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(Targets + Measures)</span></th>
                    <th rowspan="2" style="padding: 8px;">ACTUAL ACCOMPLISHMENTS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(Grades submitted, Papers published)</span></th>
                    <th colspan="4" style="padding: 4px;">RATING</th>
                    <th rowspan="2" style="padding: 8px;">REMARKS</th>
                    <th rowspan="2" style="padding: 4px;" class="print-hide">ACT</th>
                </tr>
                <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                    <th style="padding: 4px;">Q</th>
                    <th style="padding: 4px;">T</th>
                    <th style="padding: 4px;">E</th>
                    <th style="padding: 4px;">Ave.</th>
                </tr>
            </thead>

            <!-- 1. CORE FUNCTIONS -->
            <tbody id="tbody-core">
                <tr style="background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;">
                    <td colspan="6" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                        {$coreLabel}
                    </td>
                    <td colspan="3" style="padding: 6px 12px; text-align: right;">
                        <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                            Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                        </span>
                    </td>
                </tr>
                <tr class="table-row-core" style="border-bottom: 1px solid #000000;">
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..."></textarea>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..."></textarea>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..."></textarea>
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-q">
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-t">
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-e">
                    </td>
                    <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                            <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                        </div>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..."></textarea>
                    </td>
                    <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                        <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
            <!-- Add Row Footer for Core -->
            <tbody class="print-hide" id="tfoot-add-core">
                <tr>
                    <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                        <button type="button" onclick="addTableRow('core')" class="btn-add-dashed">
                            + Add Deliverable Row to Core Functions
                        </button>
                    </td>
                </tr>
            </tbody>

            <!-- 2. STRATEGIC FUNCTIONS -->
            <tbody id="tbody-strategic">
                <tr style="background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;">
                    <td colspan="6" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                        2. Strategic Functions — Research, Citations & Extension Services ({$stratPct})
                    </td>
                    <td colspan="3" style="padding: 6px 12px; text-align: right;">
                        <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                            Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                        </span>
                    </td>
                </tr>
                <tr class="table-row-strategic" style="border-bottom: 1px solid #000000;">
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..."></textarea>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..."></textarea>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..."></textarea>
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-q">
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-t">
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-e">
                    </td>
                    <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                            <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                        </div>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..."></textarea>
                    </td>
                    <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                        <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
            <!-- Add Row Footer for Strategic -->
            <tbody class="print-hide" id="tfoot-add-strategic">
                <tr>
                    <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                        <button type="button" onclick="addTableRow('strategic')" class="btn-add-dashed">
                            + Add Deliverable Row to Strategic Functions
                        </button>
                    </td>
                </tr>
            </tbody>

            <!-- 3. SUPPORT FUNCTIONS -->
            <tbody id="tbody-support">
                <tr style="background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;">
                    <td colspan="6" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                        3. Support Functions — Committee Work, Thesis Advising & Governance ({$suppPct})
                    </td>
                    <td colspan="3" style="padding: 6px 12px; text-align: right;">
                        <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                            Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                        </span>
                    </td>
                </tr>
                <tr class="table-row-support" style="border-bottom: 1px solid #000000;">
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..."></textarea>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..."></textarea>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..."></textarea>
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-q">
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-t">
                    </td>
                    <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                        <input type="number" min="0" max="5" step="1" value="" placeholder="—" title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)" class="spms-score-input field-e">
                    </td>
                    <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                            <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                        </div>
                    </td>
                    <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                        <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..."></textarea>
                    </td>
                    <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                        <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
            <!-- Add Row Footer for Support -->
            <tbody class="print-hide" id="tfoot-add-support">
                <tr>
                    <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                        <button type="button" onclick="addTableRow('support')" class="btn-add-dashed">
                            + Add Deliverable Row to Support Functions
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- GRAND SUMMARY & NAVY RATING BAR (BULLETPROOF TABLE LAYOUT) -->
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
        <tr>
            <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top; background: #fafafa;">
                <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Formula Weights:</div>
                <div style="margin-top: 6px; font-size: 11px; line-height: 1.6; color: #334155;">
                    <div>• Core Functions: <strong>{$corePct}</strong></div>
                    <div>• Strategic Functions: <strong>{$stratPct}</strong></div>
                    <div>• Support Functions: <strong>{$suppPct}</strong></div>
                </div>
            </td>
            <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Weighted Calculation:</div>
                <div style="font-family: monospace; font-size: 12px; font-weight: bold; margin-top: 6px; color: #0f172a;" id="formula-display">
                    (0.000 × {$weights['core']}) + (0.000 × {$weights['strategic']}) + (0.000 × {$weights['support']})
                </div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Sum of weighted score components</div>
            </td>
            <td style="width: 30%; padding: 12px; border: 1px solid #000; text-align: center; vertical-align: middle; background: #0f172a; color: #ffffff;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8;">Final Grand Score</div>
                <div style="font-size: 26px; font-weight: 900; color: #38bdf8; margin: 4px 0;" id="grand-score">—</div>
                <div style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background: #1e293b; color: #94a3b8;" id="grand-adjectival">
                    PENDING
                </div>
            </td>
        </tr>
    </table>

    <!-- PMT REMARKS / RECOMMENDATIONS -->
    <div style="border: 1px solid #000; padding: 8px 12px;">
        <span style="font-weight: bold; font-size: 10px; text-transform: uppercase; color: #334155;">Performance Management Team (PMT) Remarks & Recommendations:</span>
        <textarea id="pmt-remarks" class="spms-textarea" rows="2" placeholder="PMT notes, developmental interventions, or commendations..." style="margin-top: 4px; border: 1px dashed #cbd5e1;"></textarea>
    </div>

    <!-- OFFICIAL SIGNATORY ENDORSEMENT MATRIX -->
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
        <tr style="border-bottom: 1px solid #000;">
            <td colspan="3" style="padding: 8px 12px; background: #f8fafc; font-weight: bold; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                Official Signatures & Approvals:
            </td>
        </tr>
        <tr style="text-align: center;">
            <td style="width: 33.33%; border: 1px solid #000; padding: 14px 10px; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">Rated / Committed by:</div>
                <input type="text" id="sig-ratee-name" value="" placeholder="Faculty Member Name" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">Faculty Member / Ratee</div>
                <div style="margin-top: 8px; font-size: 10px; color: #64748b;">
                    Date: <input type="text" id="sig-ratee-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 90px; outline: none; font-size: 10px;">
                </div>
            </td>
            <td style="width: 33.33%; border: 1px solid #000; padding: 14px 10px; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">Assessed / Endorsed by:</div>
                <input type="text" id="sig-dean-name" value="" placeholder="Dean / Chair Name" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">College Dean / Chairperson</div>
                <div style="margin-top: 8px; font-size: 10px; color: #64748b;">
                    Date: <input type="text" id="sig-dean-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 90px; outline: none; font-size: 10px;">
                </div>
            </td>
            <td style="width: 33.33%; border: 1px solid #000; padding: 14px 10px; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">Final Rating Approved by:</div>
                <input type="text" id="sig-vp-name" value="" placeholder="VP for Academic Affairs Name" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">Vice President for Academic Affairs</div>
                <div style="margin-top: 8px; font-size: 10px; color: #64748b;">
                    Date: <input type="text" id="sig-vp-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 90px; outline: none; font-size: 10px;">
                </div>
            </td>
        </tr>
    </table>
</article>
HTML;
}

function buildTabsPayload($docTitle, $weights, $coreLabel) {
    $formData = [
        'title' => $docTitle,
        'ratee' => ['name' => '', 'position' => '', 'dept' => '', 'period' => ''],
        'approver' => ['name' => '', 'position' => '', 'date' => ''],
        'rateeSign' => ['name' => '', 'date' => ''],
        'categories' => [
            'core' => [
                ['mfo' => '', 'indicators' => '', 'accomplishments' => '', 'q' => '', 't' => '', 'e' => '', 'remarks' => '']
            ],
            'strategic' => [
                ['mfo' => '', 'indicators' => '', 'accomplishments' => '', 'q' => '', 't' => '', 'e' => '', 'remarks' => '']
            ],
            'support' => [
                ['mfo' => '', 'indicators' => '', 'accomplishments' => '', 'q' => '', 't' => '', 'e' => '', 'remarks' => '']
            ]
        ],
        'pmtRemarks' => '',
        'signatories' => ['ratee' => '', 'dean' => '', 'vp' => '']
    ];

    $html = getOfficialSpmsHtml($docTitle, $weights, $coreLabel);

    return json_encode([
        [
            'id' => 'tab-target',
            'title' => 'Target Form',
            'formData' => $formData,
            'content' => $html
        ]
    ]);
}

$templates = [
    1 => [
        'title' => 'IPCR',
        'weights' => ['core' => 0.70, 'strategic' => 0.20, 'support' => 0.10],
        'coreLabel' => '1. Core Functions — Instruction & Teaching Load (70%)',
        'fullTitle' => 'Individual Performance Commitment and Review (IPCR) — Faculty / Staff'
    ],
    2 => [
        'title' => 'DPCR',
        'weights' => ['core' => 0.60, 'strategic' => 0.25, 'support' => 0.15],
        'coreLabel' => '1. Core Division Functions (60%)',
        'fullTitle' => 'Division Performance Commitment and Review (DPCR) — Division / Department'
    ],
    3 => [
        'title' => 'OPCR',
        'weights' => ['core' => 0.60, 'strategic' => 0.25, 'support' => 0.15],
        'coreLabel' => '1. Core Office Mandate (60%)',
        'fullTitle' => 'Office Performance Commitment and Review (OPCR) — Executive / College'
    ],
    4 => [
        'title' => 'IPERF',
        'weights' => ['core' => 0.70, 'strategic' => 0.20, 'support' => 0.10],
        'coreLabel' => '1. Core Functions — Job Order & Contract of Service (70%)',
        'fullTitle' => 'Individual Performance Evaluation Rating Form (IPERF)'
    ]
];

$stmt = $db->prepare("UPDATE templates SET tabs = :tabs, updated_at = :updated_at WHERE id = :id");
$now = date('Y-m-d H:i:s');

foreach ($templates as $id => $info) {
    $tabsJson = buildTabsPayload($info['fullTitle'], $info['weights'], $info['coreLabel']);
    $stmt->bindValue(':tabs', $tabsJson, SQLITE3_TEXT);
    $stmt->bindValue(':updated_at', $now, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    echo "Updated template #{$id} ({$info['title']}) with official expanded format.\n";
}

// Also update documents table
$docTabs = buildTabsPayload(
    'Individual Performance Commitment and Review (IPCR) — Faculty / Staff',
    ['core' => 0.70, 'strategic' => 0.20, 'support' => 0.10],
    '1. Core Functions — Instruction & Teaching Load (70%)'
);
$db->exec("UPDATE documents SET tabs = '{$db->escapeString($docTabs)}'");
echo "All documents updated with official expanded format.\n";
