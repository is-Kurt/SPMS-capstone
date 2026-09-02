<?php
// Script to seed official BSU SPMS templates in spms_db.sqlite3

$db = new SQLite3('writable/database/spms_db.sqlite3');

function generateSpmsHtml($title, $weights = ['core' => 0.70, 'strategic' => 0.20, 'support' => 0.10], $coreLabel = '1. CORE FUNCTIONS — INSTRUCTION & TEACHING LOAD (70%)') {
    $coreWeightPct = ($weights['core'] * 100) . '%';
    $stratWeightPct = ($weights['strategic'] * 100) . '%';
    $suppWeightPct = ($weights['support'] * 100) . '%';

    return <<<HTML
<article id="printable-form" class="spms-sheet-container flex flex-col gap-5">
    <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px;">
        <h1 style="font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 6px 0;">
            {$title}
        </h1>
        <p style="font-size: 11px; color: #334155; margin: 6px 0 0 0; line-height: 1.6;">
            I, <input type="text" id="ratee-name" value="" placeholder="Full Name Here" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
            <input type="text" id="ratee-position" value="" placeholder="Position & Designation" style="color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;"> 
            of the <input type="text" id="ratee-dept" value="" placeholder="Office / College Name" style="color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
            commit to deliver and agree to be rated on the attainment of targets for 
            <input type="text" id="ratee-period" value="" placeholder="Period (e.g. 1st Semester, AY 2026–2027)" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 220px; outline: none; padding: 2px 4px;">.
        </p>
    </div>

    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
        <tr>
            <td style="width: 35%; border: 1px solid #000; padding: 10px; vertical-align: top;">
                <div style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 8px;">
                    Approved by (Dean / Head of Office):
                </div>
                <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                    <tr>
                        <td style="width: 60px; color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Name:</td>
                        <td style="border: none; padding: 3px 0;">
                            <input type="text" id="approver-name" value="" placeholder="Name of Approving Authority" style="width: 100%; font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Position:</td>
                        <td style="border: none; padding: 3px 0;">
                            <input type="text" id="approver-pos" value="" placeholder="Official Designation" style="width: 100%; color: #1e293b; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
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
                    <input type="text" id="ratee-sign-name" value="" placeholder="Name of Employee" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                    <span style="color: #334155; font-size: 10px; font-weight: 600; margin-top: 4px;">Employee / Ratee</span>
                    <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                        Date: <input type="text" id="ratee-sign-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 100px; outline: none; font-size: 11px;">
                    </div>
                </div>
            </td>

            <td style="width: 30%; border: 1px solid #000; padding: 8px 12px; background: #f8fafc; vertical-align: top; font-size: 10px;">
                <div style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 4px;">
                    BSU / CSC Rating Scale:
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
            <thead>
                <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                    <th rowspan="2" style="padding: 8px;">MAJOR FINAL OUTPUT</th>
                    <th rowspan="2" style="padding: 8px;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(Targets + Measures)</span></th>
                    <th rowspan="2" style="padding: 8px;">ACTUAL ACCOMPLISHMENTS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(Deliverables Submitted)</span></th>
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
            <tbody>
                <!-- Core Functions -->
                <tr class="spms-category-header">
                    <td colspan="6" style="padding: 6px 8px; font-weight: 800; font-size: 11px;">
                        {$coreLabel}
                    </td>
                    <td colspan="3" style="padding: 6px 8px; text-align: right;">
                        <span class="subtotal-badge" id="subtotal-badge-core">Core Subtotal: —</span>
                    </td>
                </tr>
            </tbody>
            <tbody id="tbody-core"></tbody>
            <tfoot>
                <tr class="print-hide">
                    <td colspan="9" style="padding: 4px; border: 1px solid #cbd5e1; background: #fafafa; text-align: center;">
                        <button type="button" onclick="addTableRow('core')" class="btn-add-row">+ Add Deliverable Row to Core Functions</button>
                    </td>
                </tr>
            </tfoot>

            <tbody>
                <!-- Strategic Functions -->
                <tr class="spms-category-header">
                    <td colspan="6" style="padding: 6px 8px; font-weight: 800; font-size: 11px;">
                        2. STRATEGIC FUNCTIONS ({$stratWeightPct})
                    </td>
                    <td colspan="3" style="padding: 6px 8px; text-align: right;">
                        <span class="subtotal-badge" id="subtotal-badge-strategic">Strategic Subtotal: —</span>
                    </td>
                </tr>
            </tbody>
            <tbody id="tbody-strategic"></tbody>
            <tfoot>
                <tr class="print-hide">
                    <td colspan="9" style="padding: 4px; border: 1px solid #cbd5e1; background: #fafafa; text-align: center;">
                        <button type="button" onclick="addTableRow('strategic')" class="btn-add-row">+ Add Deliverable Row to Strategic Functions</button>
                    </td>
                </tr>
            </tfoot>

            <tbody>
                <!-- Support Functions -->
                <tr class="spms-category-header">
                    <td colspan="6" style="padding: 6px 8px; font-weight: 800; font-size: 11px;">
                        3. SUPPORT FUNCTIONS ({$suppWeightPct})
                    </td>
                    <td colspan="3" style="padding: 6px 8px; text-align: right;">
                        <span class="subtotal-badge" id="subtotal-badge-support">Support Subtotal: —</span>
                    </td>
                </tr>
            </tbody>
            <tbody id="tbody-support"></tbody>
            <tfoot>
                <tr class="print-hide">
                    <td colspan="9" style="padding: 4px; border: 1px solid #cbd5e1; background: #fafafa; text-align: center;">
                        <button type="button" onclick="addTableRow('support')" class="btn-add-row">+ Add Deliverable Row to Support Functions</button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- GRAND SUMMARY TABLE -->
    <div style="margin-top: 16px; border: 2px solid #000; background: #ffffff;">
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr style="background: #f8fafc; border-bottom: 1px solid #000;">
                <th style="padding: 8px 12px; text-align: left; font-weight: 800; text-transform: uppercase; width: 45%;">Performance Category</th>
                <th style="padding: 8px 12px; text-align: center; font-weight: 800; text-transform: uppercase; width: 20%;">Category Average</th>
                <th style="padding: 8px 12px; text-align: center; font-weight: 800; text-transform: uppercase; width: 15%;">Assigned Weight</th>
                <th style="padding: 8px 12px; text-align: right; font-weight: 800; text-transform: uppercase; width: 20%;">Weighted Subtotal</th>
            </tr>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 6px 12px; font-weight: 600;">1. Core Functions</td>
                <td style="padding: 6px 12px; text-align: center; font-weight: bold;" id="summary-avg-core">—</td>
                <td style="padding: 6px 12px; text-align: center; color: #64748b;">{$coreWeightPct}</td>
                <td style="padding: 6px 12px; text-align: right; font-weight: bold; color: #0284c7;" id="summary-sub-core">—</td>
            </tr>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 6px 12px; font-weight: 600;">2. Strategic Functions</td>
                <td style="padding: 6px 12px; text-align: center; font-weight: bold;" id="summary-avg-strategic">—</td>
                <td style="padding: 6px 12px; text-align: center; color: #64748b;">{$stratWeightPct}</td>
                <td style="padding: 6px 12px; text-align: right; font-weight: bold; color: #0284c7;" id="summary-sub-strategic">—</td>
            </tr>
            <tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="padding: 6px 12px; font-weight: 600;">3. Support Functions</td>
                <td style="padding: 6px 12px; text-align: center; font-weight: bold;" id="summary-avg-support">—</td>
                <td style="padding: 6px 12px; text-align: center; color: #64748b;">{$suppWeightPct}</td>
                <td style="padding: 6px 12px; text-align: right; font-weight: bold; color: #0284c7;" id="summary-sub-support">—</td>
            </tr>
            <tr style="background: #0a192f; color: #ffffff;">
                <td colspan="3" style="padding: 10px 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; font-size: 12px;">
                    Final Average Rating (Core + Strategic + Support):
                </td>
                <td style="padding: 10px 14px; text-align: right;">
                    <div style="font-size: 18px; font-weight: 900; color: #38bdf8;" id="grand-final-score">—</div>
                    <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;" id="grand-adjectival-rating">PENDING EVALUATION</div>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px; margin-top: 16px;">
        <tr style="border-bottom: 1px solid #000;">
            <td colspan="3" style="padding: 8px 12px; background: #f8fafc; font-weight: bold;">
                Discussed with (Ratee):
            </td>
        </tr>
        <tr style="text-align: center;">
            <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">Ratee:</div>
                <input type="text" id="sig-ratee-name" value="" placeholder="Employee Signature Over Printed Name" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">Faculty Member / Ratee</div>
            </td>
            <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">Immediate Supervisor:</div>
                <input type="text" id="sig-dean-name" value="" placeholder="Dean / Department Chair" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">College Dean / Chairperson</div>
            </td>
            <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">Final Approval:</div>
                <input type="text" id="sig-vp-name" value="" placeholder="Name of Approving Authority" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">Vice President for Academic Affairs</div>
            </td>
        </tr>
    </table>
</article>
HTML;
}

function buildTemplateTabs($title, $weights, $coreLabel) {
    $formData = [
        'title' => $title,
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

    $html = generateSpmsHtml($title, $weights, $coreLabel);

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
        'coreLabel' => '1. CORE FUNCTIONS — INSTRUCTION & TEACHING LOAD (70%)',
        'fullTitle' => 'Individual Performance Commitment and Review (IPCR) — Faculty / Staff'
    ],
    2 => [
        'title' => 'DPCR',
        'weights' => ['core' => 0.60, 'strategic' => 0.25, 'support' => 0.15],
        'coreLabel' => '1. CORE DIVISION FUNCTIONS (60%)',
        'fullTitle' => 'Division Performance Commitment and Review (DPCR) — Division / Department'
    ],
    3 => [
        'title' => 'OPCR',
        'weights' => ['core' => 0.60, 'strategic' => 0.25, 'support' => 0.15],
        'coreLabel' => '1. CORE OFFICE MANDATE (60%)',
        'fullTitle' => 'Office Performance Commitment and Review (OPCR) — Executive / College'
    ],
    4 => [
        'title' => 'IPERF',
        'weights' => ['core' => 0.70, 'strategic' => 0.20, 'support' => 0.10],
        'coreLabel' => '1. CORE FUNCTIONS — JOB ORDER & CONTRACT OF SERVICE (70%)',
        'fullTitle' => 'Individual Performance Evaluation Rating Form (IPERF)'
    ]
];

$stmt = $db->prepare("UPDATE templates SET tabs = :tabs, updated_at = :updated_at WHERE id = :id");
$now = date('Y-m-d H:i:s');

foreach ($templates as $id => $info) {
    $tabsJson = buildTemplateTabs($info['fullTitle'], $info['weights'], $info['coreLabel']);
    $stmt->bindValue(':tabs', $tabsJson, SQLITE3_TEXT);
    $stmt->bindValue(':updated_at', $now, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    echo "Updated template #{$id} ({$info['title']}) with official BSU SPMS format.\n";
}

echo "All templates successfully updated in database.\n";
