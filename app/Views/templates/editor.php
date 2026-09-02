<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<!-- EMBEDDED CSS TO ENSURE BULLETPROOF RENDERING INDEPENDENT OF TAILWIND COMPILATION -->
<style>
    .spms-sheet-container {
        width: 100%;
        max-width: 1280px;
        background: #ffffff;
        color: #000000;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        border: 1px solid #cbd5e1;
        padding: 36px 40px;
        box-sizing: border-box;
        font-family: inherit;
        display: block !important;
        height: auto !important;
        min-height: fit-content !important;
    }

    .spms-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-size: 11px;
        height: auto !important;
        table-layout: auto !important;
        display: table !important;
    }

    .spms-table tbody {
        display: table-row-group !important;
        height: auto !important;
    }

    .spms-table th, .spms-table td {
        border: 1px solid #000000;
    }

    .spms-textarea {
        width: 100%;
        min-height: 48px;
        background: transparent;
        border: 1px solid transparent;
        padding: 4px;
        font-size: 11px;
        line-height: 1.35;
        color: #0f172a;
        resize: vertical;
        box-sizing: border-box;
        border-radius: 4px;
        font-family: inherit;
    }
    .spms-textarea:hover {
        border-color: #cbd5e1;
    }
    .spms-textarea:focus {
        border-color: #0284c7;
        background: #f8fafc;
        outline: none;
    }

    .spms-score-input {
        width: 100%;
        text-align: center;
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
        background: #ffffff;
        border: 1px solid #94a3b8;
        border-radius: 4px;
        padding: 4px 2px;
        box-sizing: border-box;
    }
    .spms-score-input:focus {
        border-color: #0284c7;
        outline: none;
        background: #f0f9ff;
    }

    .btn-add-section {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #0284c7;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-add-section:hover {
        background: #0369a1;
    }

    .btn-add-dashed {
        display: block;
        width: 100%;
        background: #f8fafc;
        border: 1.5px dashed #0284c7;
        color: #0284c7;
        font-weight: 700;
        font-size: 12px;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
        text-align: center;
    }
    .btn-add-dashed:hover {
        background: #f0f9ff;
        border-color: #0369a1;
        color: #0369a1;
    }

    .btn-del-row {
        color: #dc2626;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-del-row:hover {
        background: #fee2e2;
    }

    @media print {
        @page {
            size: letter landscape;
            margin: 8mm 6mm;
        }
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 10px !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }
        header, nav, aside, .print-hide {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
            overflow: visible !important;
            display: block !important;
            width: 100% !important;
            height: auto !important;
        }
        .spms-sheet-container {
            box-shadow: none !important;
            border: none !important;
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }
        .spms-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
            page-break-inside: auto;
        }
        .spms-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .spms-table thead {
            display: table-header-group !important;
        }
        .spms-table th, .spms-table td {
            border: 1px solid #000000 !important;
            color: #000000 !important;
        }
        .spms-textarea, .spms-score-input, input {
            border: none !important;
            background: transparent !important;
            color: #000000 !important;
            resize: none !important;
            box-shadow: none !important;
            padding: 2px !important;
        }
        .spms-textarea::placeholder, .spms-score-input::placeholder, input::placeholder {
            color: transparent !important;
        }
        .btn-add-dashed, .btn-del-row, #tfoot-add-core, #tfoot-add-strategic, #tfoot-add-support {
            display: none !important;
        }
        .spms-textarea {
            overflow: hidden !important;
            height: auto !important;
        }
    }
</style>

<!-- TOP CONTROL BAR -->
<header class="flex-none flex items-center justify-between py-3 px-4 sm:px-6 bg-[#032115] border-b border-[#0c4a33] gap-4 w-full z-30 shrink-0 print-hide">
    <div class="flex items-center gap-3 min-w-0 flex-1">
        <!-- Back Button -->
        <a href="<?= site_url('templates') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#052e1d] hover:bg-[#08422b] text-white text-xs font-bold rounded-lg border border-[#0c4a33] transition-colors shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-extrabold uppercase text-[11px] tracking-wider">Back</span>
        </a>

        <!-- Breadcrumb & Editable Title -->
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <span class="text-slate-400 text-xs font-semibold hidden md:inline shrink-0">Faculty Evaluation /</span>
            <input type="text" name="title" id="template-title" placeholder="Template Title..."
                value="<?= $template ? esc($template['title']) : 'College Faculty IPCR Form (Teaching, Research, Extension)' ?>"
                class="bg-transparent border-none font-black text-sm md:text-base text-white focus:ring-0 px-1 py-0.5 min-w-[200px] flex-1 truncate placeholder:text-slate-500">
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2.5 shrink-0">
        <!-- Export PDF -->
        <button type="button" onclick="exportToPdf()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#052e1d] hover:bg-[#08422b] text-slate-200 hover:text-white text-xs font-bold rounded-lg border border-[#0c4a33] transition-all cursor-pointer shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FFB800]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Export PDF</span>
        </button>

        <!-- Save Template -->
        <button type="button" id="btn-save-template" onclick="saveTemplateForm()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#059669] hover:bg-[#047857] text-white text-xs font-black rounded-lg shadow-md shadow-emerald-950/40 transition-all active:scale-95 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <span id="save-btn-text">Save Template</span>
        </button>
    </div>
</header>

<!-- MAIN FORM WORKSPACE -->
<main class="flex-1 overflow-y-auto bg-[#031c12] p-3 sm:p-6 lg:p-8 flex justify-center items-start custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
    
    <!-- Centered Printable Document Sheet -->
    <article id="printable-form" class="spms-sheet-container block space-y-5">
        
        <!-- INSTITUTIONAL FORM HEADER -->
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px;">
            <h1 style="font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 6px 0;">
                Individual Performance Commitment and Review (IPCR) — Faculty / Professors
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
                <!-- Approver Block -->
                <td style="width: 35%; border: 1px solid #000; padding: 10px; vertical-align: top;">
                    <div style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 8px;">
                        Approved by (Dean / Chair):
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

                <!-- Ratee Sign-off Block -->
                <td style="width: 35%; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                        <input type="text" id="ratee-sign-name" value="" placeholder="Name of Employee" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                        <span style="color: #334155; font-size: 10px; font-weight: 600; margin-top: 4px;">Faculty Member / Professor</span>
                        <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                            Date: <input type="text" id="ratee-sign-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 100px; outline: none; font-size: 11px;">
                        </div>
                    </div>
                </td>

                <!-- CHED / CSC / BSU Rating Scale -->
                <td style="width: 30%; border: 1px solid #000; padding: 8px 12px; background: #f8fafc; vertical-align: top; font-size: 10px;">
                    <div style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 4px;">
                        BSU / CSC Faculty Rating Scale:
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

        <!-- MAIN TABLE OF DELIVERABLES & RATINGS -->
        <div style="width: 100%; overflow: visible; min-height: fit-content; display: block;">
            <table class="spms-table">
                <!-- Column Widths (9 total columns matching BSU Annex B IPCR) -->
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
                            1. Core Functions — Instruction & Teaching Load (70%)
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
                <tbody class="print-hide">
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
                            2. Strategic Functions — Research, Citations & Extension Services (20%)
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
                <tbody class="print-hide">
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
                            3. Support Functions — Committee Work, Thesis Advising & Governance (10%)
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
                <tbody class="print-hide">
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
                <!-- Left: Formula Explanation -->
                <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top; background: #fafafa;">
                    <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Formula Weights:</div>
                    <div style="font-size: 11px; color: #334155; margin-top: 6px; line-height: 1.5;">
                        Core Function (70%) + Strategic Function (20%) + Support Functions (10%).
                    </div>
                    <div style="font-size: 10px; color: #94a3b8; font-style: italic; margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 6px;">
                        Validated against standard Civil Service Commission SPMS Guidelines.
                    </div>
                </td>

                <!-- Right: Calculation Breakdown & Final Navy Bar -->
                <td style="width: 65%; padding: 0; vertical-align: top; border: 1px solid #000;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <!-- Core Row -->
                        <tr style="border-bottom: 1px solid #000;">
                            <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; width: 45%; color: #0f172a;">
                                CORE FUNCTION
                            </td>
                            <td style="padding: 8px 12px; font-weight: 900; color: #047857; font-size: 13px; width: 20%;" id="sum-core-score">
                                0.000
                            </td>
                            <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right; width: 35%;">
                                (Average: <span id="sum-core-avg">0.000</span> × 0.70)
                            </td>
                        </tr>

                        <!-- Strategic Row -->
                        <tr style="border-bottom: 1px solid #000;">
                            <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                STRATEGIC FUNCTION
                            </td>
                            <td style="padding: 8px 12px; font-weight: 900; color: #0284c7; font-size: 13px;" id="sum-strategic-score">
                                0.000
                            </td>
                            <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right;">
                                (Average: <span id="sum-strategic-avg">0.000</span> × 0.20)
                            </td>
                        </tr>

                        <!-- Support Row -->
                        <tr style="border-bottom: 1px solid #000;">
                            <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                SUPPORT FUNCTIONS
                            </td>
                            <td style="padding: 8px 12px; font-weight: 900; color: #d97706; font-size: 13px;" id="sum-support-score">
                                0.000
                            </td>
                            <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right;">
                                (Average: <span id="sum-support-avg">0.000</span> × 0.10)
                            </td>
                        </tr>

                        <!-- Dark Navy Grand Total Banner (Matching Reference Mockup) -->
                        <tr style="background: #0a192f; color: #ffffff;">
                            <td style="padding: 12px 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px; color: #e2e8f0;">
                                FINAL AVERAGE RATING
                            </td>
                            <td style="padding: 12px 14px; font-weight: 900; font-size: 24px; color: #38bdf8; font-family: monospace;" id="grand-score">
                                0.000
                            </td>
                            <td style="padding: 12px 14px; text-align: right;">
                                <span id="adjectival-badge" style="display: inline-block; background: #475569; color: #ffffff; font-weight: 900; font-size: 11px; text-transform: uppercase; padding: 4px 12px; border-radius: 4px; letter-spacing: 0.05em; margin-right: 8px;">
                                    PENDING EVALUATION
                                </span>
                                <span id="grand-formula" style="font-size: 9px; color: #94a3b8; font-family: monospace;">
                                    (Core 0.000 + Strategic 0.000 + Support 0.000)
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- PMT & DEAN REMARKS -->
        <div style="border: 1px solid #000; padding: 10px; box-sizing: border-box;">
            <div style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 4px;">
                College Performance Management Team (PMT) & Dean Remarks / Recommendations:
            </div>
            <textarea id="pmt-remarks" rows="2" placeholder="Enter PMT & Dean remarks and recommendations here..." class="spms-textarea" style="width: 100%; border: 1px solid transparent; font-style: italic;"></textarea>
        </div>

        <!-- 3 BOTTOM SIGNATORIES -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px; text-align: center;">
            <tr>
                <!-- Discussed with (Ratee) -->
                <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">
                        Discussed with (Faculty Ratee):
                    </div>
                    <input type="text" id="sig-ratee-name" value="" placeholder="Name of Faculty Ratee" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                    <div style="font-size: 10px; color: #475569; margin-top: 3px;">Faculty Member / Professor</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                        Date: <input type="text" id="sig-ratee-date" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 85px; outline: none; font-size: 10px;">
                    </div>
                </td>

                <!-- Assessed by (Dean) -->
                <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">
                        Assessed by (College Dean):
                    </div>
                    <input type="text" id="sig-dean-name" value="" placeholder="Name of Dean / Supervisor" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                    <div style="font-size: 10px; color: #475569; margin-top: 3px;">College Dean / Unit Head</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                        Date: <input type="text" id="sig-dean-date" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 85px; outline: none; font-size: 10px;">
                    </div>
                </td>

                <!-- Final Approval (VP) -->
                <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">
                        Final Approval (VP for Academic Affairs):
                    </div>
                    <input type="text" id="sig-vp-name" value="" placeholder="Name of Approving Authority" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                    <div style="font-size: 10px; color: #475569; margin-top: 3px;">Vice President for Academic Affairs</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                        Date: <input type="text" id="sig-vp-date" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 85px; outline: none; font-size: 10px;">
                    </div>
                </td>
            </tr>
        </table>

    </article>
</main>

<!-- HIDDEN FORM FOR SAVING TEMPLATE -->
<?= form_open('templates/store', ['id' => 'template-save-form', 'class' => 'hidden']) ?>
    <input type="hidden" name="template_id" value="<?= $template ? esc($template['id']) : '' ?>">
    <input type="hidden" name="title" id="form-post-title" value="">
    <input type="hidden" name="tabs" id="form-post-tabs" value="">
<?= form_close() ?>

<!-- REACTIVE MATH & EVENT SCRIPTS -->
<script>
    // Initial Blueprint Data
    <?php
        $existingFormData = null;
        if ($template && !empty($template['tabs'])) {
            $tabsArr = is_string($template['tabs']) ? json_decode($template['tabs'], true) : $template['tabs'];
            if (!empty($tabsArr) && is_array($tabsArr)) {
                $existingFormData = $tabsArr[0]['formData'] ?? null;
            }
        }
    ?>
    const INITIAL_FORM_DATA = <?= json_encode($existingFormData) ?>;

    // Default Seed Data Matching BSU Annex B Official IPCR (Clean empty rows upon creation)
    const DEFAULT_BLUEPRINT = {
        core: [
            {
                mfo: "",
                indicators: "",
                accomplishments: "",
                q: "", t: "", e: "",
                remarks: ""
            }
        ],
        strategic: [
            {
                mfo: "",
                indicators: "",
                accomplishments: "",
                q: "", t: "", e: "",
                remarks: ""
            }
        ],
        support: [
            {
                mfo: "",
                indicators: "",
                accomplishments: "",
                q: "", t: "", e: "",
                remarks: ""
            }
        ]
    };

    // Category Weights
    const CATEGORY_WEIGHTS = {
        core: 0.70,
        strategic: 0.20,
        support: 0.10
    };

    document.addEventListener("DOMContentLoaded", function () {
        populateInitialRows();
        recalculateForm();
    });

    function populateInitialRows() {
        const sourceData = (INITIAL_FORM_DATA && INITIAL_FORM_DATA.categories) ? INITIAL_FORM_DATA.categories : DEFAULT_BLUEPRINT;

        // Populate Core (expand at least 1 row if empty)
        const coreRows = (sourceData.core && sourceData.core.length > 0) ? sourceData.core : DEFAULT_BLUEPRINT.core;
        coreRows.forEach(row => addTableRow('core', row));

        // Populate Strategic (expand at least 1 row if empty)
        const strategicRows = (sourceData.strategic && sourceData.strategic.length > 0) ? sourceData.strategic : DEFAULT_BLUEPRINT.strategic;
        strategicRows.forEach(row => addTableRow('strategic', row));

        // Populate Support (expand at least 1 row if empty)
        const supportRows = (sourceData.support && sourceData.support.length > 0) ? sourceData.support : DEFAULT_BLUEPRINT.support;
        supportRows.forEach(row => addTableRow('support', row));
    }

    function addTableRow(category, rowData = null) {
        const tbody = document.getElementById(`tbody-${category}`);
        if (!tbody) return;

        const data = rowData || {
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: ""
        };

        const tr = document.createElement('tr');
        tr.className = `table-row-${category}`;
        tr.style.borderBottom = '1px solid #000000';
        
        tr.innerHTML = `
            <!-- Major Final Output -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output...">${escapeHtml(data.mfo)}</textarea>
            </td>

            <!-- Success Indicators -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)...">${escapeHtml(data.indicators)}</textarea>
            </td>

            <!-- Actual Accomplishments -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments...">${escapeHtml(data.accomplishments)}</textarea>
            </td>

            <!-- Rating Q, T, E Inputs (Flat Whole Numbers 0 to 5, Return to null via Esc, DblClick, or Backspace) -->
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="0" max="5" step="1" 
                    value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                    placeholder="—" 
                    title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" 
                    oninput="handleScoreInput(this)" 
                    onkeydown="handleScoreKeydown(event, this)" 
                    ondblclick="clearScore(this)" 
                    class="spms-score-input field-q">
            </td>
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="0" max="5" step="1" 
                    value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                    placeholder="—" 
                    title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" 
                    oninput="handleScoreInput(this)" 
                    onkeydown="handleScoreKeydown(event, this)" 
                    ondblclick="clearScore(this)" 
                    class="spms-score-input field-t">
            </td>
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="0" max="5" step="1" 
                    value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                    placeholder="—" 
                    title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" 
                    oninput="handleScoreInput(this)" 
                    onkeydown="handleScoreKeydown(event, this)" 
                    ondblclick="clearScore(this)" 
                    class="spms-score-input field-e">
            </td>

            <!-- Row Average -->
            <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                    <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                </div>
            </td>

            <!-- Remarks -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks...">${escapeHtml(data.remarks)}</textarea>
            </td>

            <!-- Delete Action -->
            <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        recalculateForm();
    }

    function deleteTableRow(button) {
        const tr = button.closest('tr');
        if (!tr) return;
        tr.remove();
        recalculateForm();
    }

    function clearScore(input) {
        input.value = '';
        recalculateForm();
    }

    function handleScoreKeydown(e, input) {
        // Clear back to null on Escape
        if (e.key === 'Escape') {
            e.preventDefault();
            clearScore(input);
            return;
        }
        // If current value is 0 and user presses ArrowDown, return to null/blank
        if (e.key === 'ArrowDown' && input.value === '0') {
            e.preventDefault();
            clearScore(input);
            return;
        }
    }

    function handleScoreInput(input) {
        const raw = input.value.trim();
        if (raw === '') {
            recalculateForm();
            return;
        }
        const num = parseInt(raw, 10);
        // If invalid or out of range (< 0 or > 5), reset back to null
        if (isNaN(num) || num < 0 || num > 5) {
            input.value = '';
        } else {
            input.value = num; // enforce flat integer
        }
        recalculateForm();
    }

    function parseWholeScore(val) {
        if (val === null || val === undefined) return null;
        const str = String(val).trim();
        if (str === '') return null;
        const num = parseInt(str, 10);
        if (isNaN(num) || num < 0 || num > 5) return null;
        return num;
    }

    function recalculateForm() {
        let categoryResults = {
            core: calculateCategory('core'),
            strategic: calculateCategory('strategic'),
            support: calculateCategory('support')
        };

        let totalRated = categoryResults.core.count + categoryResults.strategic.count + categoryResults.support.count;

        // Update Subtotal Badges
        document.getElementById('badge-core-subtotal').innerText = categoryResults.core.count > 0 ? categoryResults.core.subtotal.toFixed(3) : '0.000';
        document.getElementById('badge-strategic-subtotal').innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.subtotal.toFixed(3) : '0.000';
        document.getElementById('badge-support-subtotal').innerText = categoryResults.support.count > 0 ? categoryResults.support.subtotal.toFixed(3) : '0.000';

        // Update Summary Section Breakdown
        document.getElementById('sum-core-score').innerText = categoryResults.core.count > 0 ? categoryResults.core.subtotal.toFixed(3) : '0.000';
        document.getElementById('sum-core-avg').innerText = categoryResults.core.count > 0 ? categoryResults.core.avg.toFixed(3) : '0.000';

        document.getElementById('sum-strategic-score').innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.subtotal.toFixed(3) : '0.000';
        document.getElementById('sum-strategic-avg').innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.avg.toFixed(3) : '0.000';

        document.getElementById('sum-support-score').innerText = categoryResults.support.count > 0 ? categoryResults.support.subtotal.toFixed(3) : '0.000';
        document.getElementById('sum-support-avg').innerText = categoryResults.support.count > 0 ? categoryResults.support.avg.toFixed(3) : '0.000';

        // Calculate Grand Final Rating
        let grandScore = categoryResults.core.subtotal + categoryResults.strategic.subtotal + categoryResults.support.subtotal;
        document.getElementById('grand-score').innerText = totalRated > 0 ? grandScore.toFixed(3) : '0.000';

        // Calculate Adjectival Rating
        let adjectival = getAdjectivalRating(grandScore, totalRated);
        const badgeEl = document.getElementById('adjectival-badge');
        badgeEl.innerText = adjectival.text;
        badgeEl.style.background = adjectival.color;

        // Formula text
        document.getElementById('grand-formula').innerText = 
            `(Core ${categoryResults.core.subtotal.toFixed(3)} + Strategic ${categoryResults.strategic.subtotal.toFixed(3)} + Support ${categoryResults.support.subtotal.toFixed(3)})`;
    }

    function calculateCategory(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        if (rows.length === 0) {
            return { avg: 0, subtotal: 0, count: 0 };
        }

        let sumOfRowAvgs = 0;
        let count = 0;

        rows.forEach(row => {
            const q = parseWholeScore(row.querySelector('.field-q')?.value);
            const t = parseWholeScore(row.querySelector('.field-t')?.value);
            const e = parseWholeScore(row.querySelector('.field-e')?.value);

            let rowSum = 0;
            let rowInputs = 0;

            // Flat whole numbers (0 to 5) or null (blank/not applicable)
            if (q !== null) { rowSum += q; rowInputs++; }
            if (t !== null) { rowSum += t; rowInputs++; }
            if (e !== null) { rowSum += e; rowInputs++; }

            let rowAvg = rowInputs > 0 ? (rowSum / rowInputs) : 0;
            row.querySelector('.field-row-avg').innerText = rowInputs > 0 ? rowAvg.toFixed(2) : '—';

            if (rowInputs > 0) {
                sumOfRowAvgs += rowAvg;
                count++;
            }
        });

        let categoryAvg = count > 0 ? (sumOfRowAvgs / count) : 0;
        let weight = CATEGORY_WEIGHTS[category] || 0;
        let subtotal = categoryAvg * weight;

        return {
            avg: categoryAvg,
            subtotal: subtotal,
            count: count
        };
    }

    function getAdjectivalRating(score, totalRated = 0) {
        if (totalRated === 0) return { text: 'PENDING EVALUATION', color: '#64748b' };
        if (score >= 4.500) return { text: 'OUTSTANDING', color: '#059669' };
        if (score >= 3.500) return { text: 'VERY SATISFACTORY', color: '#2563eb' };
        if (score >= 2.500) return { text: 'SATISFACTORY', color: '#d97706' };
        if (score >= 1.500) return { text: 'UNSATISFACTORY', color: '#ea580c' };
        return { text: 'POOR', color: '#dc2626' };
    }

    function exportToPdf() {
        document.querySelectorAll('#printable-form textarea').forEach(ta => {
            ta.style.height = 'auto';
            ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
        });
        window.print();
    }

    function saveTemplateForm() {
        const saveBtn = document.getElementById('btn-save-template');
        const saveBtnText = document.getElementById('save-btn-text');
        
        saveBtn.disabled = true;
        saveBtnText.innerText = 'Saving...';

        // Gather structured data
        const title = document.getElementById('template-title').value.trim() || 'College Faculty IPCR Form';
        
        const formDataPayload = {
            title: title,
            ratee: {
                name: document.getElementById('ratee-name').value,
                position: document.getElementById('ratee-position').value,
                dept: document.getElementById('ratee-dept').value,
                period: document.getElementById('ratee-period').value
            },
            approver: {
                name: document.getElementById('approver-name').value,
                position: document.getElementById('approver-pos').value,
                date: document.getElementById('approver-date').value
            },
            rateeSign: {
                name: document.getElementById('ratee-sign-name').value,
                date: document.getElementById('ratee-sign-date').value
            },
            categories: {
                core: extractRowsData('core'),
                strategic: extractRowsData('strategic'),
                support: extractRowsData('support')
            },
            pmtRemarks: document.getElementById('pmt-remarks').value,
            signatories: {
                ratee: document.getElementById('sig-ratee-name').value,
                dean: document.getElementById('sig-dean-name').value,
                vp: document.getElementById('sig-vp-name').value
            }
        };

        // Format tabs array for backwards and forwards compatibility
        const tabsPayload = [
            {
                id: 'tab-main-form',
                title: 'Target Form',
                formData: formDataPayload,
                content: document.getElementById('printable-form').innerHTML
            }
        ];

        document.getElementById('form-post-title').value = title;
        document.getElementById('form-post-tabs').value = JSON.stringify(tabsPayload);

        // Submit via fetch for smooth AJAX handling
        const form = document.getElementById('template-save-form');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtnText.innerText = 'Save Template';

            if (data.status === 'success') {
                if (data.template_id) {
                    const idInput = document.querySelector('input[name="template_id"]');
                    if (idInput) idInput.value = data.template_id;
                }
                showToast('Template saved successfully!');
            } else {
                alert(data.message || 'Error saving template.');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtnText.innerText = 'Save Template';
            form.submit();
        });
    }

    function extractRowsData(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        const result = [];

        rows.forEach(row => {
            const q = parseWholeScore(row.querySelector('.field-q')?.value);
            const t = parseWholeScore(row.querySelector('.field-t')?.value);
            const e = parseWholeScore(row.querySelector('.field-e')?.value);

            result.push({
                mfo: row.querySelector('.field-mfo')?.value || '',
                indicators: row.querySelector('.field-indicators')?.value || '',
                accomplishments: row.querySelector('.field-accomplishments')?.value || '',
                q: q !== null ? q : '',
                t: t !== null ? t : '',
                e: e !== null ? e : '',
                remarks: row.querySelector('.field-remarks')?.value || ''
            });
        });

        return result;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 9999; background: #059669; color: #ffffff; font-weight: bold; font-size: 12px; padding: 12px 20px; border-radius: 10px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); display: flex; align-items: center; gap: 8px; transition: all 0.3s; transform: translateY(8px); opacity: 0;';
        toast.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; color: #FFB800;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        setTimeout(() => {
            toast.style.transform = 'translateY(8px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>

<?= $this->endSection() ?>