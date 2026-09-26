<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<?php
    $existingFormData = null;
    if ($template && !empty($template['tabs'])) {
        $tabsArr = is_string($template['tabs']) ? json_decode($template['tabs'], true) : $template['tabs'];
        if (!empty($tabsArr) && is_array($tabsArr)) {
            $existingFormData = $tabsArr[0]['formData'] ?? null;
        }
    }
    $isDpcr = false;
    $isOpcr = false;
    $isIperf = false;
    if ($template) {
        if ((isset($template['id']) && (int)$template['id'] === 4) || 
            (isset($template['title']) && stripos($template['title'], 'iperf') !== false) ||
            (isset($existingFormData['doc_type']) && strtolower($existingFormData['doc_type']) === 'iperf')) {
            $isIperf = true;
        } elseif ((isset($template['id']) && (int)$template['id'] === 2) || 
            (isset($template['title']) && stripos($template['title'], 'dpcr') !== false) ||
            (isset($existingFormData['doc_type']) && strtolower($existingFormData['doc_type']) === 'dpcr')) {
            $isDpcr = true;
        } elseif ((isset($template['id']) && (int)$template['id'] === 3) || 
            (isset($template['title']) && stripos($template['title'], 'opcr') !== false) ||
            (isset($existingFormData['doc_type']) && strtolower($existingFormData['doc_type']) === 'opcr')) {
            $isOpcr = true;
        }
    }
?>

<!-- EMBEDDED CSS TO ENSURE BULLETPROOF RENDERING INDEPENDENT OF TAILWIND COMPILATION -->
<style>
    .spms-sheet-container {
        width: 100%;
        max-width: <?= $isDpcr ? '1400px' : ($isOpcr ? '1350px' : '1280px') ?>;
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

    .spms-table-responsive-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 0.5rem;
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
    .budget-input-wrapper {
        display: flex;
        align-items: center;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background: #ffffff;
        padding: 1px 3px;
        gap: 2px;
        box-sizing: border-box;
        transition: border-color 0.15s ease, background-color 0.15s ease;
        overflow: hidden;
    }
    .budget-input-wrapper:hover {
        border-color: #94a3b8;
    }
    .budget-input-wrapper:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 1px #0284c7;
        background: #f0f9ff;
    }
    .header-budget-currency {
        border: none;
        border-bottom: 1px dashed #0284c7;
        background: transparent;
        color: #ba372a;
        outline: none;
        font-size: 9.5px;
        text-align: center;
        font-weight: bold;
        cursor: pointer;
    }
    .header-budget-currency:focus {
        border-bottom-style: solid;
        color: #0284c7;
    }
    @media print {
        .budget-input-wrapper {
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
        }
        .header-budget-currency {
            border: none !important;
        }
        .field-budget-currency {
            border-right: none !important;
            font-size: 8.5px !important;
            font-weight: 700 !important;
        }
        .field-budget {
            font-size: 8.5px !important;
            letter-spacing: -0.2px !important;
            text-align: right !important;
            font-weight: 600 !important;
            width: 100% !important;
        }
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
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .spms-score-input::-webkit-outer-spin-button,
    .spms-score-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
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
        padding: 6px 12px;
        border: 1px dashed #94a3b8;
        border-radius: 4px;
        background: #f8fafc;
        color: #0284c7;
        font-weight: 700;
        font-size: 11px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-add-dashed:hover {
        background: #f0f9ff;
        border-color: #0284c7;
        color: #0369a1;
    }

    .btn-del-row {
        color: #ef4444;
        background: transparent;
        border: none;
        padding: 4px;
        cursor: pointer;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-del-row:hover {
        background: #fee2e2;
    }

    /* Print styling rules */
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .print-hide {
            display: none !important;
        }
        .spms-sheet-container {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .spms-table {
            border: 1px solid #000000 !important;
            width: 100% !important;
        }
        .spms-table th, .spms-table td {
            border: 1px solid #000000 !important;
            color: #000000 !important;
        }
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
            <span class="text-slate-400 text-xs font-semibold hidden md:inline shrink-0"><?= $isDpcr ? 'Department Evaluation /' : ($isOpcr ? 'Office Evaluation /' : ($isIperf ? 'COS & Job Order Evaluation /' : 'Faculty Evaluation /')) ?></span>
            <input type="text" name="title" id="template-title" placeholder="<?= $isDpcr ? 'Department Performance Commitment and Review (DPCR)' : ($isOpcr ? 'Office Performance Commitment and Review (OPCR)' : ($isIperf ? 'Individual Performance Evaluation Rating Form (IPERF)' : 'Template Title...')) ?>"
                value="<?= $template ? esc($template['title']) : ($isDpcr ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' : ($isOpcr ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : ($isIperf ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' : 'College Faculty IPCR Form (Teaching, Research, Extension)'))) ?>"
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
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 14px; font-weight: 900; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #000000; line-height: 1.4;">
                <?= $isIperf 
                    ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' 
                    : ($isDpcr ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' : ($isOpcr ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)')) ?>
            </h2>
            <?php if ($isIperf): ?>
            <div style="font-size: 11px; font-style: italic; color: #000000; margin-top: 4px;">
                (attach rubrics for the rating of actual accomplishments vis-à-vis expected outputs)
            </div>
            <?php endif; ?>
        </div>

        <?php if ($isIperf): ?>
        <!-- IPERF 3-ROW METADATA MATRIX (EXACT REPLICA OF PHOTO) -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 18px; font-size: 11px;">
            <tr style="border-bottom: 1px solid #000000;">
                <td style="width: 18%; padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Name of Employee:</td>
                <td style="width: 42%; padding: 6px 8px; border-right: 1px solid #000000;">
                    <input type="text" id="ratee-name" value="<?= esc($existingFormData['ratee']['name'] ?? '') ?>" placeholder="indicate full name (First Name Middle Initial Last Name, Extension; e.g., Juan D. Cruz III)" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a; font-weight: bold;">
                </td>
                <td style="width: 15%; padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Classification:</td>
                <td style="width: 25%; padding: 6px 8px;">
                    <select id="ratee-classification" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px 4px; font-size: 11px; color: #000; font-weight: bold; background: #ffffff;">
                        <option value="Contract of Service (COS)" <?= (($existingFormData['ratee']['classification'] ?? ($existingFormData['classification'] ?? '')) === 'Contract of Service (COS)') ? 'selected' : '' ?>>Contract of Service (COS)</option>
                        <option value="Job Order" <?= (($existingFormData['ratee']['classification'] ?? ($existingFormData['classification'] ?? '')) === 'Job Order') ? 'selected' : '' ?>>Job Order</option>
                    </select>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #000000;">
                <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Position:</td>
                <td style="padding: 6px 8px; border-right: 1px solid #000000;">
                    <input type="text" id="ratee-position" value="<?= esc($existingFormData['ratee']['position'] ?? '') ?>" placeholder="indicate the full position title specified in the contract/job order" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a;">
                </td>
                <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Rating Period:</td>
                <td style="padding: 6px 8px;">
                    <input type="text" id="ratee-period" value="<?= esc($existingFormData['ratee']['period'] ?? '') ?>" placeholder="e.g., July - December 2024" style="width: 100%; border: none; outline: none; font-size: 11px; color: #000; font-weight: bold;">
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Office:</td>
                <td colspan="3" style="padding: 6px 8px;">
                    <input type="text" id="ratee-dept" value="<?= esc($existingFormData['ratee']['dept'] ?? '') ?>" placeholder="indicate in full the specific area of assignment (e.g., CIS - Department of Development Communication)" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a;">
                </td>
            </tr>
        </table>
        <!-- Hidden compatibility elements -->
        <input type="hidden" id="ratee-sign-name" value="<?= esc($existingFormData['rateeSign']['name'] ?? ($existingFormData['ratee']['name'] ?? '')) ?>">
        <input type="hidden" id="ratee-sign-date" value="<?= esc($existingFormData['rateeSign']['date'] ?? '') ?>">
        <div id="approvers-container" style="display: none;">
            <div class="approver-item">
                <input type="hidden" class="field-approver-name" id="approver-name" value="<?= esc($existingFormData['approver']['name'] ?? '') ?>">
                <input type="hidden" class="field-approver-pos" id="approver-pos" value="<?= esc($existingFormData['approver']['position'] ?? '') ?>">
                <input type="hidden" class="field-approver-date" id="approver-date" value="<?= esc($existingFormData['approver']['date'] ?? '') ?>">
            </div>
        </div>
        <?php else: ?>
        <!-- Preamble with Red Hint Placeholders -->
        <div style="margin-bottom: 18px; font-size: 11px; line-height: 1.6; text-align: justify; color: #000000;">
            I, <input type="text" id="ratee-name" value="<?= esc($existingFormData['ratee']['name'] ?? '') ?>" placeholder="FULL NAME HERE" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
            <input type="text" id="ratee-position" value="<?= esc($existingFormData['ratee']['position'] ?? '') ?>" placeholder="Position and Official Designation" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 200px; outline: none; padding: 2px 4px;"> of the 
            <input type="text" id="ratee-dept" value="<?= esc($existingFormData['ratee']['dept'] ?? '') ?>" placeholder="Office Name" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period 
            <input type="text" id="ratee-period" value="<?= esc($existingFormData['ratee']['period'] ?? '') ?>" placeholder="January - June or July - December and Year; e.g., July - December 2024" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 320px; outline: none; padding: 2px 4px;">.
        </div>

        <!-- Approver, Ratee, and Rating Scale Matrix -->
        <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 18px; font-size: 11px;">
            <tr>
                <!-- Left: Approved By (Extreme Left-most) -->
                <td style="vertical-align: top; border: none; padding: 0 20px 0 0; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="font-weight: bold; color: #000000;">APPROVED BY:</div>
                        <button type="button" onclick="addApproverBlock()" class="print-hide" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; font-size: 10px; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 4px; cursor: pointer;" title="Add another approving signatory">
                            + Add Signatory
                        </button>
                    </div>
                    <div id="approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                        <?php 
                            $approversList = $existingFormData['approvers'] ?? [];
                            if (empty($approversList) && !empty($existingFormData['approver'])) {
                                $approversList = [$existingFormData['approver']];
                            }
                            if (empty($approversList)) {
                                $approversList = [['name' => '', 'position' => '', 'date' => '']];
                            }
                            foreach ($approversList as $idx => $appr): 
                        ?>
                        <div class="approver-item" style="<?= $idx > 0 ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : '' ?> position: relative;">
                            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                <tr>
                                    <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Name:</td>
                                    <td style="border: none; padding: 3px 0;">
                                        <input type="text" class="field-approver-name" <?= $idx === 0 ? 'id="approver-name"' : '' ?> value="<?= esc($appr['name'] ?? '') ?>" placeholder="(name of office head)" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 60%;">
                                        <?php if ($idx > 0): ?>
                                        <button type="button" onclick="removeApproverBlock(this)" class="print-hide" style="margin-left: 6px; color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; font-size: 9px; padding: 1px 5px; border-radius: 3px; cursor: pointer; font-weight: bold;" title="Remove this signatory">✕ Remove</button>
                                        <?php else: ?>
                                        <span style="color: #ba372a; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Position:</td>
                                    <td style="border: none; padding: 3px 0;">
                                        <input type="text" class="field-approver-pos" <?= $idx === 0 ? 'id="approver-pos"' : '' ?> value="<?= esc($appr['position'] ?? '') ?>" placeholder="(position of office head)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 80%;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Date:</td>
                                    <td style="border: none; padding: 3px 0;">
                                        <input type="date" class="field-approver-date" <?= $idx === 0 ? 'id="approver-date"' : '' ?> value="<?= esc($appr['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="color: <?= !empty($appr['date']) ? '#0f172a' : '#ba372a' ?>; border: none; border-bottom: 1px solid <?= !empty($appr['date']) ? '#cbd5e1' : '#ba372a' ?>; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit; cursor: pointer;">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </td>

                <!-- Right: Name of Employee & Rating Scale (Extreme Right-most) -->
                <td style="vertical-align: top; border: none; padding: 0; text-align: right; width: 1%; white-space: nowrap;">
                    <div style="display: inline-block; text-align: left; min-width: 240px;">
                        <!-- Employee Block -->
                        <div style="margin-bottom: 14px;">
                            <div>
                                <input type="text" id="ratee-sign-name" value="<?= esc($existingFormData['rateeSign']['name'] ?? '') ?>" placeholder="(full name here)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; text-align: left; width: 240px; outline: none; font-size: 11px; padding: 2px 0;">
                            </div>
                            <div style="font-size: 11px; color: #000000; margin-top: 3px;">Name of Employee</div>
                            <div style="margin-top: 4px; font-size: 11px; color: #000000;">
                                Date: <input type="date" id="ratee-sign-date" value="<?= esc($existingFormData['rateeSign']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid <?= !empty($existingFormData['rateeSign']['date']) ? '#cbd5e1' : '#ba372a' ?>; width: 130px; outline: none; font-size: 11px; color: <?= !empty($existingFormData['rateeSign']['date']) ? '#0f172a' : '#ba372a' ?>; background: transparent; font-family: inherit; cursor: pointer;">
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
        <?php endif; ?>

        <!-- MAIN TABLE OF DELIVERABLES & RATINGS -->
        <div class="spms-table-responsive-wrapper" style="width: 100%; overflow-x: auto; min-height: fit-content; display: block;">
            <table class="spms-table">
                <?php if ($isDpcr): ?>
                <!-- DPCR 10-Column Layout -->
                <colgroup>
                    <col style="width: 14%;"> <!-- PROGRAMS, PROJECTS, ACTIVITIES -->
                    <col style="width: 17%;"> <!-- SUCCESS INDICATORS -->
                    <col style="width: 10%;"> <!-- ALLOTTED BUDGET -->
                    <col style="width: 13%;"> <!-- INDIVIDUALS / OFFICES ACCOUNTABLE -->
                    <col style="width: 18%;"> <!-- ACTUAL ACCOMPLISHMENTS -->
                    <col style="width: 3.5%;"> <!-- Q -->
                    <col style="width: 3.5%;"> <!-- T -->
                    <col style="width: 3.5%;"> <!-- E -->
                    <col style="width: 4.5%;"> <!-- Ave. -->
                    <col style="width: 13%;"> <!-- REMARKS -->
                    <col style="width: 3%;">  <!-- ACT -->
                </colgroup>

                <!-- Two-Row Header with Light Blue #cfe2f3 Background -->
                <thead>
                    <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                            ALLOTTED BUDGET
                            <div style="font-weight: normal; font-size: 9px; margin-top: 2px; display: inline-flex; align-items: center; justify-content: center; gap: 2px;">
                                (<input type="text" id="header-budget-currency" class="header-budget-currency" value="₱" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px;">)
                            </div>
                        </th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS /<br>OFFICES ACCOUNTABLE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                    </tr>
                    <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                </thead>
                <?php elseif ($isOpcr): ?>
                <!-- OPCR 10-Column Layout Matching Vice President Institutional Sheet -->
                <colgroup>
                    <col style="width: 14%;"> <!-- PROJECT/ PROGRAM/ ACTIVITIES -->
                    <col style="width: 17%;"> <!-- SUCCESS INDICATORS (TARGETS + MEASURES) PERFORMANCE -->
                    <col style="width: 10%;"> <!-- ALLOTTED BUDGET -->
                    <col style="width: 13%;"> <!-- DIVISIONS ACCOUNTABLE -->
                    <col style="width: 18%;"> <!-- ACTUAL ACCOMPLISHMENT -->
                    <col style="width: 3.5%;"> <!-- Q -->
                    <col style="width: 3.5%;"> <!-- T -->
                    <col style="width: 3.5%;"> <!-- E -->
                    <col style="width: 4.5%;"> <!-- AVE -->
                    <col style="width: 13%;"> <!-- REMARKS -->
                    <col style="width: 3%;">  <!-- ACT -->
                </colgroup>

                <!-- Two-Row Header with Soft Yellow/Amber #fff2cc Background -->
                <thead>
                    <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT/<br>PROGRAM/<br>ACTIVITIES</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS (TARGETS +<br>MEASURES) PERFORMANCE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                            ALLOTTED<br>BUDGET
                            <div style="font-weight: normal; font-size: 9px; margin-top: 2px; display: inline-flex; align-items: center; justify-content: center; gap: 2px;">
                                (<input type="text" id="header-budget-currency-opcr" class="header-budget-currency" value="₱" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px;">)
                            </div>
                        </th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS<br>ACCOUNTABLE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENT</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATINGS</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                    </tr>
                    <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">AVE</th>
                    </tr>
                </thead>
                <?php elseif ($isIperf): ?>
                <!-- IPERF 8-Column Layout (+ Action column in web view) Matching COS & Job Order Sheet -->
                <colgroup>
                    <col style="width: 24%;"> <!-- OFFICE PPA -->
                    <col style="width: 24%;"> <!-- EXPECTED OUTPUTS -->
                    <col style="width: 24%;"> <!-- ACTUAL ACCOMPLISHMENTS -->
                    <col style="width: 4%;">  <!-- Q -->
                    <col style="width: 4%;">  <!-- T -->
                    <col style="width: 4%;">  <!-- E -->
                    <col style="width: 5%;">  <!-- Ave. -->
                    <col style="width: 12%;"> <!-- REMARKS -->
                    <col style="width: 3%;">  <!-- ACT -->
                </colgroup>

                <!-- Two-Row Header with Soft Neutral #f1f5f9 Background -->
                <thead>
                    <tr style="background-color: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                            OFFICE PPA<br><span style="font-size: 9px; font-weight: normal;">(PROGRAMS, PROJECTS, ACTIVITIES)</span><br>
                            <span style="color: #ba372a; font-size: 8.5px; font-style: italic; font-weight: normal;">(aligned with the deliverables of the office)</span>
                        </th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                            EXPECTED OUTPUTS<br>
                            <span style="color: #ba372a; font-size: 8.5px; font-style: italic; font-weight: normal;">(based on contract or duties and responsibilities in the request to hire personnel)</span>
                        </th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                    </tr>
                    <tr style="background-color: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                </thead>
                <?php else: ?>
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

                <!-- Two-Row Header: Q, T, E, Ave side-by-side with Light Blue #cfe2f3 Background -->
                <thead>
                    <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">MAJOR FINAL OUTPUT</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(TARGETS + MEASURES)</span></th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                    </tr>
                    <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                </thead>
                <?php endif; ?>

                <?php if ($isIperf): ?>
                <!-- IPERF FLAT DELIVERABLES LIST (NO CATEGORIES) -->
                <tbody id="tbody-core">
                </tbody>
                <!-- Add Row Footer for IPERF -->
                <tbody class="print-hide">
                    <tr>
                        <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                            <button type="button" onclick="addTableRow('core')" class="btn-add-dashed">
                                + Add Deliverable / Office PPA Row
                            </button>
                        </td>
                    </tr>
                </tbody>

                <!-- OVERALL AVERAGE RATING ROW (EXACT REPLICA OF PHOTO) -->
                <tfoot>
                    <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="6" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #000000; border: 1px solid #000; text-align: left;">
                            OVERALL AVERAGE RATING
                        </td>
                        <td style="padding: 8px 4px; text-align: center; font-weight: 900; color: #0284c7; font-size: 12px; border: 1px solid #000;" id="iperf-overall-average">
                            0.000
                        </td>
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #000;">
                            <span id="iperf-adjectival-badge" style="display: inline-block; background: #475569; color: #ffffff; font-weight: 900; font-size: 10px; text-transform: uppercase; padding: 2px 8px; border-radius: 4px;">
                                PENDING EVALUATION
                            </span>
                        </td>
                        <td style="border: 1px solid #000;" class="print-hide"></td>
                    </tr>
                </tfoot>

                <!-- Hidden strategic and support containers for JS compatibility -->
                <tbody id="tbody-strategic" style="display: none;"></tbody>
                <tbody id="tbody-support" style="display: none;"></tbody>

                <?php else: ?>
                <!-- 1. CORE FUNCTIONS -->
                <tbody id="tbody-core">
                    <tr style="background-color: #fce5cd; border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">
                        <?php if ($isDpcr): ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            CORE FUNCTIONS (60%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php elseif ($isOpcr): ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            CORE MANDATE (60%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php else: ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            CORE FUNCTIONS (70%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="4" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
                <!-- Add Row Footer for Core -->
                <tbody class="print-hide">
                    <tr>
                        <td colspan="<?= ($isDpcr || $isOpcr) ? 11 : 9 ?>" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                            <button type="button" onclick="addTableRow('core')" class="btn-add-dashed">
                                + Add Deliverable Row to Core Functions
                            </button>
                        </td>
                    </tr>
                </tbody>

                <!-- 2. STRATEGIC FUNCTIONS -->
                <tbody id="tbody-strategic">
                    <tr style="background-color: #fce5cd; border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">
                        <?php if ($isDpcr): ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            STRATEGIC FUNCTIONS (30%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php elseif ($isOpcr): ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            STRATEGIC FUNCTIONS (25%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php else: ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            STRATEGIC FUNCTIONS (20%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="4" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
                <!-- Add Row Footer for Strategic -->
                <tbody class="print-hide">
                    <tr>
                        <td colspan="<?= ($isDpcr || $isOpcr) ? 11 : 9 ?>" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                            <button type="button" onclick="addTableRow('strategic')" class="btn-add-dashed">
                                + Add Deliverable Row to Strategic Functions
                            </button>
                        </td>
                    </tr>
                </tbody>

                <!-- 3. SUPPORT FUNCTIONS -->
                <tbody id="tbody-support">
                    <tr style="background-color: #fce5cd; border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">
                        <?php if ($isDpcr): ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            SUPPORT FUNCTIONS (10%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php elseif ($isOpcr): ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            SUPPORT FUNCTIONS (15%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php else: ?>
                        <td colspan="5" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                            SUPPORT FUNCTIONS (10%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                        </td>
                        <td colspan="4" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                            <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                            </span>
                        </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
                <!-- Add Row Footer for Support -->
                <tbody class="print-hide">
                    <tr>
                        <td colspan="<?= ($isDpcr || $isOpcr) ? 11 : 9 ?>" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                            <button type="button" onclick="addTableRow('support')" class="btn-add-dashed">
                                + Add Deliverable Row to Support Functions
                            </button>
                        </td>
                    </tr>
                </tbody>
                <?php endif; ?>
            </table>
        </div>

        <?php if (!$isIperf): ?>
        <!-- GRAND SUMMARY & NAVY RATING BAR (BULLETPROOF TABLE LAYOUT FOR BOTH DPCR AND IPCR) -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
            <tr>
                <!-- Left: Formula Explanation -->
                <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top; background: #fafafa;">
                    <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Formula Weights:</div>
                    <div style="font-size: 11px; color: #334155; margin-top: 6px; line-height: 1.5;">
                        <?= $isDpcr 
                            ? 'Core Functions (60%) + Strategic Functions (30%) + Support Functions (10%).' 
                            : ($isOpcr 
                                ? 'Core Mandate (60%) + Strategic Functions (25%) + Support Functions (15%).'
                                : 'Core Functions (70%) + Strategic Functions (20%) + Support Functions (10%).') ?>
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
                                (Average: <span id="sum-core-avg">0.000</span> × <span id="mult-core-val"><?= ($isDpcr || $isOpcr) ? '0.60' : '0.70' ?></span>)
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
                                (Average: <span id="sum-strategic-avg">0.000</span> × <span id="mult-strategic-val"><?= $isDpcr ? '0.30' : ($isOpcr ? '0.25' : '0.20') ?></span>)
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
                                (Average: <span id="sum-support-avg">0.000</span> × <span id="mult-support-val"><?= $isOpcr ? '0.15' : '0.10' ?></span>)
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
        <?php else: ?>
        <!-- Hidden elements to preserve JS compatibility for IPERF -->
        <div style="display: none;">
            <span id="badge-core-subtotal">0.000</span>
            <span id="badge-strategic-subtotal">0.000</span>
            <span id="badge-support-subtotal">0.000</span>
            <span id="sum-core-score">0.000</span>
            <span id="sum-core-avg">0.000</span>
            <span id="mult-core-val">1.00</span>
            <span id="sum-strategic-score">0.000</span>
            <span id="sum-strategic-avg">0.000</span>
            <span id="mult-strategic-val">0.00</span>
            <span id="sum-support-score">0.000</span>
            <span id="sum-support-avg">0.000</span>
            <span id="mult-support-val">0.00</span>
            <span id="grand-score">0.000</span>
            <span id="adjectival-badge">PENDING EVALUATION</span>
            <span id="grand-formula"></span>
        </div>
        <?php endif; ?>

        <!-- REMARKS BOX (ROW 29 IN EXCEL SPREADSHEET) -->
        <div style="border: 1px solid #000; <?= $isIperf ? '' : 'border-top: none;' ?> padding: 8px 10px; box-sizing: border-box;">
            <div style="font-weight: bold; font-size: 11px; color: #000000; margin-bottom: 4px;">
                Remarks/Suggestions/Recommendations on Ratee's Performance:
            </div>
            <textarea id="pmt-remarks" rows="2" placeholder="Enter remarks/suggestions/recommendations on ratee's performance..." class="spms-textarea" style="width: 100%; border: 1px solid transparent; font-style: italic; font-size: 11px;"><?= esc($existingFormData['pmtRemarks'] ?? '') ?></textarea>
        </div>

        <?php if ($isIperf): ?>
        <!-- IPERF TWO-PHASE SIGNATORIES & REFERENCE GUIDES (EXACT REPLICA OF PHOTO) -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 18px; font-size: 11px;">
            <tr>
                <!-- Phase 1: Start of Period (Columns A-B) -->
                <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                    <div style="color: #ba372a; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                        signed at the start of the rating period
                    </div>

                    <!-- Targets Prepared By -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-weight: bold; margin-bottom: 4px;">Targets prepared by:</div>
                        <input type="text" id="sig-targets-prepared-name" value="<?= esc($existingFormData['signatories']['targets_prepared_by']['name'] ?? ($existingFormData['signatories']['ratee'] ?? ($existingFormData['ratee']['name'] ?? ''))) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Employee (Ratee)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-targets-prepared-date" value="<?= esc($existingFormData['signatories']['targets_prepared_by']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>

                    <!-- Approved By -->
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px;">Approved by:</div>
                        <input type="text" id="sig-targets-approved-name" value="<?= esc($existingFormData['signatories']['targets_approved_by']['name'] ?? ($existingFormData['signatories']['dean'] ?? '')) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-targets-approved-date" value="<?= esc($existingFormData['signatories']['targets_approved_by']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>
                </td>

                <!-- Phase 2: End of Period (Columns C-D) -->
                <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                    <div style="color: #ba372a; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                        signed at the end of the rating period
                    </div>

                    <!-- Rated By -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-weight: bold; margin-bottom: 4px;">Rated by:</div>
                        <input type="text" id="sig-eval-rated-name" value="<?= esc($existingFormData['signatories']['eval_rated_by']['name'] ?? ($existingFormData['signatories']['dean'] ?? '')) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-eval-rated-date" value="<?= esc($existingFormData['signatories']['eval_rated_by']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>

                    <!-- Conforme -->
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px;">Conforme:</div>
                        <input type="text" id="sig-eval-conforme-name" value="<?= esc($existingFormData['signatories']['eval_conforme']['name'] ?? ($existingFormData['signatories']['ratee'] ?? ($existingFormData['ratee']['name'] ?? ''))) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Employee (Ratee)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-eval-conforme-date" value="<?= esc($existingFormData['signatories']['eval_conforme']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>
                </td>

                <!-- Side Reference: Measures & Rating Guide (Columns E-H) -->
                <td style="width: 28%; vertical-align: top; padding: 10px 12px; background: #fdfdfd;">
                    <!-- Measures -->
                    <div style="margin-bottom: 12px;">
                        <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px;">Measures:</div>
                        <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                            <tr><td style="width: 20px; font-weight: bold; padding: 1px 0;">Q</td><td style="padding: 1px 0;">Quality</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">T</td><td style="padding: 1px 0;">Timeliness</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">E</td><td style="padding: 1px 0;">Efficiency</td></tr>
                        </table>
                    </div>

                    <!-- Rating Guide -->
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px;">Rating Guide:</div>
                        <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                            <tr><td style="width: 20px; font-weight: bold; padding: 1px 0;">5</td><td style="padding: 1px 0;">Outstanding</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">4</td><td style="padding: 1px 0;">Very Satisfactory</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">3</td><td style="padding: 1px 0;">Satisfactory</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">2</td><td style="padding: 1px 0;">Unsatisfactory</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">1</td><td style="padding: 1px 0;">Poor</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <!-- Compatibility hidden inputs for standard signatures -->
        <input type="hidden" id="sig-ratee-name" value="<?= esc($existingFormData['signatories']['ratee'] ?? ($existingFormData['ratee']['name'] ?? '')) ?>">
        <input type="hidden" id="sig-ratee-pos" value="">
        <input type="hidden" id="sig-dean-name" value="<?= esc($existingFormData['signatories']['dean'] ?? '') ?>">
        <input type="hidden" id="sig-dean-pos" value="">
        <input type="hidden" id="sig-vp-name" value="">
        <?php else: ?>
        <!-- BOTTOM SIGNATORIES (ROWS 32-35 EXCEL SPREADSHEET EXACT REPLICA) -->
        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px; margin-top: 18px;">
            <tr>
                <!-- Left: Ratee (Columns A-C) -->
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 15px 0 0;">
                    <div style="margin-bottom: 6px;">Name and Signature of Ratee: 
                        <input type="text" id="sig-ratee-name" value="<?= esc($existingFormData['signatories']['ratee'] ?? '') ?>" placeholder="(name here)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 55%;">
                    </div>
                    <div style="margin-bottom: 6px;">Position: 
                        <input type="text" id="sig-ratee-pos" value="" placeholder="(position here)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 70%;">
                    </div>
                    <div style="margin-bottom: 6px;">Date: <input type="date" id="sig-ratee-date" value="<?= esc($existingFormData['signatories']['ratee_date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;"></div>
                </td>

                <!-- Right: Office Head (Columns D-H) -->
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 15px;">
                    <div style="margin-bottom: 6px;">Final Rating by: 
                        <input type="text" id="sig-dean-name" value="<?= esc($existingFormData['signatories']['dean'] ?? '') ?>" placeholder="(name of office head)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 55%;">
                    </div>
                    <div style="margin-bottom: 6px;">Position: 
                        <input type="text" id="sig-dean-pos" value="" placeholder="(position of office head)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 70%;">
                    </div>
                    <div style="margin-bottom: 6px;">Date: <input type="date" id="sig-dean-date" value="<?= esc($existingFormData['signatories']['dean_date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;"></div>
                    <div style="color: #ba372a; font-size: 10px; font-style: italic;">(may add signatories depending on position/designation)</div>
                </td>
            </tr>
        </table>
        <input type="hidden" id="sig-vp-name" value="">
        <?php endif; ?>

    </article>
</main>

<!-- HIDDEN FORM FOR SAVING TEMPLATE -->
<?= form_open('templates/store', ['id' => 'template-save-form', 'class' => 'hidden']) ?>
    <input type="hidden" name="template_id" value="<?= $template ? esc($template['id']) : '' ?>">
    <input type="hidden" name="title" id="form-post-title" value="">
    <input type="hidden" name="tabs" id="form-post-tabs" value="">
<?= form_close() ?>

        <?php if ($isIperf): ?>
        <!-- IPERF TWO-PHASE SIGNATORIES & REFERENCE GUIDES (EXACT REPLICA OF PHOTO) -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 18px; font-size: 11px;">
            <tr>
                <!-- Phase 1: Start of Period (Columns A-B) -->
                <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                    <div style="color: #ba372a; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                        signed at the start of the rating period
                    </div>

                    <!-- Targets Prepared By -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-weight: bold; margin-bottom: 4px;">Targets prepared by:</div>
                        <input type="text" id="sig-targets-prepared-name" value="<?= esc($existingFormData['signatories']['targets_prepared_by']['name'] ?? ($existingFormData['signatories']['ratee'] ?? ($existingFormData['ratee']['name'] ?? ''))) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Employee (Ratee)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-targets-prepared-date" value="<?= esc($existingFormData['signatories']['targets_prepared_by']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>

                    <!-- Approved By -->
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px;">Approved by:</div>
                        <input type="text" id="sig-targets-approved-name" value="<?= esc($existingFormData['signatories']['targets_approved_by']['name'] ?? ($existingFormData['signatories']['dean'] ?? '')) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-targets-approved-date" value="<?= esc($existingFormData['signatories']['targets_approved_by']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>
                </td>

                <!-- Phase 2: End of Period (Columns C-D) -->
                <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                    <div style="color: #ba372a; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                        signed at the end of the rating period
                    </div>

                    <!-- Rated By -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-weight: bold; margin-bottom: 4px;">Rated by:</div>
                        <input type="text" id="sig-eval-rated-name" value="<?= esc($existingFormData['signatories']['eval_rated_by']['name'] ?? ($existingFormData['signatories']['dean'] ?? '')) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-eval-rated-date" value="<?= esc($existingFormData['signatories']['eval_rated_by']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>

                    <!-- Conforme -->
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px;">Conforme:</div>
                        <input type="text" id="sig-eval-conforme-name" value="<?= esc($existingFormData['signatories']['eval_conforme']['name'] ?? ($existingFormData['signatories']['ratee'] ?? ($existingFormData['ratee']['name'] ?? ''))) ?>" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;">
                        <div style="font-size: 10px; color: #334155; margin-top: 2px;">Employee (Ratee)</div>
                        <div style="margin-top: 4px; font-size: 10.5px;">
                            Date: <input type="date" id="sig-eval-conforme-date" value="<?= esc($existingFormData['signatories']['eval_conforme']['date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;">
                        </div>
                    </div>
                </td>

                <!-- Side Reference: Measures & Rating Guide (Columns E-H) -->
                <td style="width: 28%; vertical-align: top; padding: 10px 12px; background: #fdfdfd;">
                    <!-- Measures -->
                    <div style="margin-bottom: 12px;">
                        <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px;">Measures:</div>
                        <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                            <tr><td style="width: 20px; font-weight: bold; padding: 1px 0;">Q</td><td style="padding: 1px 0;">Quality</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">T</td><td style="padding: 1px 0;">Timeliness</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">E</td><td style="padding: 1px 0;">Efficiency</td></tr>
                        </table>
                    </div>

                    <!-- Rating Guide -->
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px;">Rating Guide:</div>
                        <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                            <tr><td style="width: 20px; font-weight: bold; padding: 1px 0;">5</td><td style="padding: 1px 0;">Outstanding</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">4</td><td style="padding: 1px 0;">Very Satisfactory</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">3</td><td style="padding: 1px 0;">Satisfactory</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">2</td><td style="padding: 1px 0;">Unsatisfactory</td></tr>
                            <tr><td style="font-weight: bold; padding: 1px 0;">1</td><td style="padding: 1px 0;">Poor</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <!-- Compatibility hidden inputs for standard signatures -->
        <input type="hidden" id="sig-ratee-name" value="<?= esc($existingFormData['signatories']['ratee'] ?? ($existingFormData['ratee']['name'] ?? '')) ?>">
        <input type="hidden" id="sig-ratee-pos" value="">
        <input type="hidden" id="sig-dean-name" value="<?= esc($existingFormData['signatories']['dean'] ?? '') ?>">
        <input type="hidden" id="sig-dean-pos" value="">
        <input type="hidden" id="sig-vp-name" value="">
        <?php else: ?>
        <!-- BOTTOM SIGNATORIES (ROWS 32-35 EXCEL SPREADSHEET EXACT REPLICA) -->
        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px; margin-top: 18px;">
            <tr>
                <!-- Left: Ratee (Columns A-C) -->
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 15px 0 0;">
                    <div style="margin-bottom: 6px;">Name and Signature of Ratee: 
                        <input type="text" id="sig-ratee-name" value="<?= esc($existingFormData['signatories']['ratee'] ?? '') ?>" placeholder="(name here)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 55%;">
                    </div>
                    <div style="margin-bottom: 6px;">Position: 
                        <input type="text" id="sig-ratee-pos" value="" placeholder="(position here)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 70%;">
                    </div>
                    <div style="margin-bottom: 6px;">Date: <input type="date" id="sig-ratee-date" value="<?= esc($existingFormData['signatories']['ratee_date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;"></div>
                </td>

                <!-- Right: Office Head (Columns D-H) -->
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 15px;">
                    <div style="margin-bottom: 6px;">Final Rating by: 
                        <input type="text" id="sig-dean-name" value="<?= esc($existingFormData['signatories']['dean'] ?? '') ?>" placeholder="(name of office head)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 55%;">
                    </div>
                    <div style="margin-bottom: 6px;">Position: 
                        <input type="text" id="sig-dean-pos" value="" placeholder="(position of office head)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 70%;">
                    </div>
                    <div style="margin-bottom: 6px;">Date: <input type="date" id="sig-dean-date" value="<?= esc($existingFormData['signatories']['dean_date'] ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #cbd5e1; width: 130px; outline: none; font-size: 11px; background: transparent; font-family: inherit; cursor: pointer;"></div>
                    <div style="color: #ba372a; font-size: 10px; font-style: italic;">(may add signatories depending on position/designation)</div>
                </td>
            </tr>
        </table>
        <input type="hidden" id="sig-vp-name" value="">
        <?php endif; ?>

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
    const IS_DPCR = <?= $isDpcr ? 'true' : 'false' ?>;
    const IS_OPCR = <?= $isOpcr ? 'true' : 'false' ?>;
    const IS_IPERF = <?= $isIperf ? 'true' : 'false' ?>;

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

    // Default Seed Data Matching Official Forms
    const DEFAULT_BLUEPRINT = IS_DPCR ? {
        core: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" },
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        support: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ]
    } : (IS_OPCR ? {
        core: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    } : (IS_IPERF ? {
        core: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [],
        support: []
    } : {
        core: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    }));

    // Category Weights: DPCR (60/30/10) vs OPCR (60/25/15) vs IPERF (100% flat) vs IPCR (70/20/10)
    const CATEGORY_WEIGHTS = IS_DPCR ? {
        core: 0.60,
        strategic: 0.30,
        support: 0.10
    } : (IS_OPCR ? {
        core: 0.60,
        strategic: 0.25,
        support: 0.15
    } : (IS_IPERF ? {
        core: 1.0,
        strategic: 0,
        support: 0
    } : {
        core: 0.70,
        strategic: 0.20,
        support: 0.10
    }));

    // Helper: sanitize budget input and cap at 12 whole digits (up to 999 Billion) + 2 decimal places
    function sanitizeBudgetInput(raw) {
        let clean = String(raw || '').replace(/[^0-9.]/g, '');
        const parts = clean.split('.');
        let whole = parts[0] || '';
        let decimal = parts.length > 1 ? parts.slice(1).join('') : null;
        if (whole.length > 12) {
            whole = whole.substring(0, 12);
        }
        if (decimal !== null && decimal.length > 2) {
            decimal = decimal.substring(0, 2);
        }
        return decimal !== null ? whole + '.' + decimal : whole;
    }

    // Helper: full exact comma-formatted string (e.g. 10,000,000,000.00)
    function formatBudgetFull(num) {
        if (isNaN(num) || num === null || num === undefined) return '';
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Auto-shrink font size for large budget values + dynamic hover tooltip
    function adjustBudgetFontSize(input) {
        if (!input) return;
        const len = (input.value || '').length;
        if (len >= 18) {
            input.style.fontSize = '7.5px';
            input.style.letterSpacing = '-0.5px';
        } else if (len >= 15) {
            input.style.fontSize = '8px';
            input.style.letterSpacing = '-0.3px';
        } else if (len >= 12) {
            input.style.fontSize = '9px';
            input.style.letterSpacing = '-0.2px';
        } else if (len >= 9) {
            input.style.fontSize = '10px';
            input.style.letterSpacing = 'normal';
        } else {
            input.style.fontSize = '11px';
            input.style.letterSpacing = 'normal';
        }
        const wrapper = input.closest('.budget-input-wrapper');
        const cur = wrapper ? (wrapper.querySelector('.field-budget-currency')?.value || '₱') : '₱';
        input.title = input.value ? `${cur} ${input.value}` : 'Allotted Budget (Numbers only)';
    }

    function applyBudgetBlur(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        const clean = sanitizeBudgetInput(raw);
        if (clean !== '') {
            const num = parseFloat(clean);
            if (!isNaN(num) && num >= 0) {
                el.dataset.rawValue = clean;
                const full = formatBudgetFull(num);
                el.dataset.fullValue = full;
                el.value = full;
            } else {
                el.value = '';
                el.dataset.rawValue = '';
                el.dataset.fullValue = '';
            }
        } else {
            el.value = '';
            el.dataset.rawValue = '';
            el.dataset.fullValue = '';
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetFocus(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        if (raw !== '') {
            el.value = raw;
            setTimeout(() => { try { el.select(); } catch(e) {} }, 0);
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetInput(el) {
        if (!el) return;
        const oldVal = el.value;
        const clean = sanitizeBudgetInput(oldVal);
        if (clean !== oldVal) {
            el.value = clean;
        }
        el.dataset.rawValue = clean;
        const num = parseFloat(clean);
        if (!isNaN(num)) {
            el.dataset.fullValue = formatBudgetFull(num);
        } else {
            el.dataset.fullValue = clean;
        }
        adjustBudgetFontSize(el);
    }

    document.addEventListener("DOMContentLoaded", function () {
        window.currentDocCurrency = (INITIAL_FORM_DATA && (INITIAL_FORM_DATA.budget_currency || INITIAL_FORM_DATA.currency)) ? (INITIAL_FORM_DATA.budget_currency || INITIAL_FORM_DATA.currency) : '₱';
        document.querySelectorAll('.header-budget-currency').forEach(el => {
            el.value = window.currentDocCurrency;
            el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
            el.addEventListener('input', function() {
                const val = this.value || '₱';
                this.style.width = Math.max(18, (this.value.length + 1) * 6.5) + 'px';
                window.currentDocCurrency = val;
                document.querySelectorAll('.header-budget-currency').forEach(other => {
                    if (other !== this) other.value = this.value;
                });
                document.querySelectorAll('.field-budget-currency').forEach(rowCur => {
                    rowCur.value = val;
                    rowCur.style.width = Math.max(18, (val.length + 1) * 6.5) + 'px';
                    const bInput = rowCur.closest('.budget-input-wrapper')?.querySelector('.field-budget');
                    if (bInput) adjustBudgetFontSize(bInput);
                });
            });
            el.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '₱';
                    this.dispatchEvent(new Event('input'));
                }
            });
        });
        populateInitialRows();
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
        recalculateForm();
    });
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

    // Default Seed Data Matching Official Forms
    const DEFAULT_BLUEPRINT = IS_DPCR ? {
        core: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" },
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        support: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ]
    } : (IS_OPCR ? {
        core: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    } : (IS_IPERF ? {
        core: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [],
        support: []
    } : {
        core: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    }));

    // Category Weights: DPCR (60/30/10) vs OPCR (60/25/15) vs IPERF (100% flat) vs IPCR (70/20/10)
    const CATEGORY_WEIGHTS = IS_DPCR ? {
        core: 0.60,
        strategic: 0.30,
        support: 0.10
    } : (IS_OPCR ? {
        core: 0.60,
        strategic: 0.25,
        support: 0.15
    } : (IS_IPERF ? {
        core: 1.0,
        strategic: 0,
        support: 0
    } : {
        core: 0.70,
        strategic: 0.20,
        support: 0.10
    }));

    // Helper: sanitize budget input and cap at 12 whole digits (up to 999 Billion) + 2 decimal places
    function sanitizeBudgetInput(raw) {
        let clean = String(raw || '').replace(/[^0-9.]/g, '');
        const parts = clean.split('.');
        let whole = parts[0] || '';
        let decimal = parts.length > 1 ? parts.slice(1).join('') : null;
        if (whole.length > 12) {
            whole = whole.substring(0, 12);
        }
        if (decimal !== null && decimal.length > 2) {
            decimal = decimal.substring(0, 2);
        }
        return decimal !== null ? whole + '.' + decimal : whole;
    }

    // Helper: full exact comma-formatted string (e.g. 10,000,000,000.00)
    function formatBudgetFull(num) {
        if (isNaN(num) || num === null || num === undefined) return '';
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Auto-shrink font size for large budget values + dynamic hover tooltip
    function adjustBudgetFontSize(input) {
        if (!input) return;
        const len = (input.value || '').length;
        if (len >= 18) {
            input.style.fontSize = '7.5px';
            input.style.letterSpacing = '-0.5px';
        } else if (len >= 15) {
            input.style.fontSize = '8px';
            input.style.letterSpacing = '-0.3px';
        } else if (len >= 12) {
            input.style.fontSize = '9px';
            input.style.letterSpacing = '-0.2px';
        } else if (len >= 9) {
            input.style.fontSize = '10px';
            input.style.letterSpacing = 'normal';
        } else {
            input.style.fontSize = '11px';
            input.style.letterSpacing = 'normal';
        }
        const wrapper = input.closest('.budget-input-wrapper');
        const cur = wrapper ? (wrapper.querySelector('.field-budget-currency')?.value || '₱') : '₱';
        input.title = input.value ? `${cur} ${input.value}` : 'Allotted Budget (Numbers only)';
    }

    function applyBudgetBlur(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        const clean = sanitizeBudgetInput(raw);
        if (clean !== '') {
            const num = parseFloat(clean);
            if (!isNaN(num) && num >= 0) {
                el.dataset.rawValue = clean;
                const full = formatBudgetFull(num);
                el.dataset.fullValue = full;
                el.value = full;
            } else {
                el.value = '';
                el.dataset.rawValue = '';
                el.dataset.fullValue = '';
            }
        } else {
            el.value = '';
            el.dataset.rawValue = '';
            el.dataset.fullValue = '';
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetFocus(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        if (raw !== '') {
            el.value = raw;
            setTimeout(() => { try { el.select(); } catch(e) {} }, 0);
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetInput(el) {
        if (!el) return;
        const oldVal = el.value;
        const clean = sanitizeBudgetInput(oldVal);
        if (clean !== oldVal) {
            el.value = clean;
        }
        el.dataset.rawValue = clean;
        const num = parseFloat(clean);
        if (!isNaN(num)) {
            el.dataset.fullValue = formatBudgetFull(num);
        } else {
            el.dataset.fullValue = clean;
        }
        adjustBudgetFontSize(el);
    }

    document.addEventListener("DOMContentLoaded", function () {
        window.currentDocCurrency = (INITIAL_FORM_DATA && (INITIAL_FORM_DATA.budget_currency || INITIAL_FORM_DATA.currency)) ? (INITIAL_FORM_DATA.budget_currency || INITIAL_FORM_DATA.currency) : '₱';
        document.querySelectorAll('.header-budget-currency').forEach(el => {
            el.value = window.currentDocCurrency;
            el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
            el.addEventListener('input', function() {
                const val = this.value || '₱';
                this.style.width = Math.max(18, (this.value.length + 1) * 6.5) + 'px';
                window.currentDocCurrency = val;
                document.querySelectorAll('.header-budget-currency').forEach(other => {
                    if (other !== this) other.value = this.value;
                });
                document.querySelectorAll('.field-budget-currency').forEach(rowCur => {
                    rowCur.value = val;
                    rowCur.style.width = Math.max(18, (val.length + 1) * 6.5) + 'px';
                    const bInput = rowCur.closest('.budget-input-wrapper')?.querySelector('.field-budget');
                    if (bInput) adjustBudgetFontSize(bInput);
                });
            });
            el.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '₱';
                    this.dispatchEvent(new Event('input'));
                }
            });
        });
        populateInitialRows();
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
        recalculateForm();
    });

    function populateInitialRows() {
        ['core', 'strategic', 'support'].forEach(cat => {
            const tbody = document.getElementById(`tbody-${cat}`);
            if (tbody) {
                tbody.querySelectorAll(`.table-row-${cat}`).forEach(el => el.remove());
            }
        });

        const sourceData = (INITIAL_FORM_DATA && INITIAL_FORM_DATA.categories) ? INITIAL_FORM_DATA.categories : DEFAULT_BLUEPRINT;

        // Populate Core (expand at least 1 row if empty)
        const coreRows = (sourceData.core && sourceData.core.length > 0) ? sourceData.core : (DEFAULT_BLUEPRINT.core || []);
        coreRows.forEach(row => addTableRow('core', row));

        if (!IS_IPERF) {
            // Populate Strategic (expand at least 1 row if empty)
            const strategicRows = (sourceData.strategic && sourceData.strategic.length > 0) ? sourceData.strategic : (DEFAULT_BLUEPRINT.strategic || []);
            strategicRows.forEach(row => addTableRow('strategic', row));

            // Populate Support (expand at least 1 row if empty)
            const supportRows = (sourceData.support && sourceData.support.length > 0) ? sourceData.support : (DEFAULT_BLUEPRINT.support || []);
            supportRows.forEach(row => addTableRow('support', row));
        }
    }

    function addTableRow(category, rowData = null) {
        const tbody = document.getElementById(`tbody-${category}`);
        if (!tbody) return;

        const data = rowData || (IS_DPCR ? {
            mfo: "", indicators: "", budget: "", budget_currency: "₱", accountable: "",
            accomplishments: "", q: "", t: "", e: "", remarks: "",
            std_5: "", std_4: "", std_3: "", std_2: "", std_1: ""
        } : (IS_OPCR ? {
            mfo: "", indicators: "", budget: "", budget_currency: "₱", accountable: "",
            accomplishments: "", q: "", t: "", e: "", remarks: ""
        } : (IS_IPERF ? {
            mfo: "", indicators: "", accomplishments: "",
            q: "", t: "", e: "", remarks: ""
        } : {
            mfo: "", indicators: "", accomplishments: "",
            q: "", t: "", e: "", remarks: ""
        })));

        const tr = document.createElement('tr');
        tr.className = `table-row-${category}`;
        tr.style.borderBottom = '1px solid #000000';

        const rawBudget = (data.budget !== undefined && data.budget !== null && String(data.budget).trim() !== '')
            ? sanitizeBudgetInput(String(data.budget))
            : '';
        const numBudget = parseFloat(rawBudget);
        const fullBudget = !isNaN(numBudget) ? formatBudgetFull(numBudget) : rawBudget;
        const displayBudget = !isNaN(numBudget) ? formatBudgetDisplay(numBudget) : rawBudget;
        const rowCurrency = data.budget_currency || (typeof window !== 'undefined' && window.currentDocCurrency) || '₱';
        
        if (IS_IPERF) {
            tr.innerHTML = `
                <!-- Office PPA (Programs, Projects, Activities) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter Office PPA (aligned with deliverables)...">${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- Expected Outputs -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter expected outputs (contract / responsibilities)...">${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- Actual Accomplishments -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments...">${escapeHtml(data.accomplishments)}</textarea>
                </td>

                <!-- Rating Q, T, E Inputs (Flat Whole Numbers 1 to 5) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-q">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-t">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
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
        } else if (IS_DPCR) {
            tr.innerHTML = `
                <!-- Programs, Projects, Activities -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter programs, projects, activities...">${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- Success Indicators (Targets + Measures) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)...">${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Allotted Budget - Decimal with Editable Currency -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="budget-input-wrapper">
                        <input type="text" class="field-budget-currency" value="${escapeHtml(rowCurrency)}" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px; text-align: center; font-weight: 700; color: #0284c7; border: none; border-right: 1px solid #e2e8f0; outline: none; background: transparent; font-size: 10px; padding: 1px 2px 1px 0; cursor: pointer; flex-shrink: 0;">
                        <input type="text" inputmode="decimal" spellcheck="false" autocomplete="off" class="spms-input field-budget" maxlength="18" data-raw-value="${escapeHtml(rawBudget)}" data-full-value="${escapeHtml(fullBudget)}" value="${escapeHtml(fullBudget)}" placeholder="0.00" style="flex: 1; min-width: 0; width: 100%; border: none; outline: none; font-size: 11px; text-align: right; background: transparent; font-family: inherit; font-weight: 500; color: #0f172a; padding: 1px 1px; transition: font-size 0.1s ease;">
                    </div>
                </td>

                <!-- Individuals / Offices Accountable -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accountable" rows="3" placeholder="Offices / individuals...">${escapeHtml(data.accountable || '')}</textarea>
                </td>

                <!-- Actual Accomplishments -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments...">${escapeHtml(data.accomplishments)}</textarea>
                </td>

                <!-- Rating Q, T, E Inputs (Amber #ffe599 matching Excel sheet) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #ffe599;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-q" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #ffe599;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-t" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #ffe599;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-e" style="background-color: #ffe599;">
                </td>

                <!-- Row Average (Auto) -->
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
        } else if (IS_OPCR) {
            tr.innerHTML = `
                <!-- Project / Program / Activities -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter project / program / activities...">${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- Success Indicators (Targets + Measures) Performance -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)...">${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Allotted Budget - Decimal with Editable Currency -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="budget-input-wrapper">
                        <input type="text" class="field-budget-currency" value="${escapeHtml(rowCurrency)}" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px; text-align: center; font-weight: 700; color: #0284c7; border: none; border-right: 1px solid #e2e8f0; outline: none; background: transparent; font-size: 10px; padding: 1px 2px 1px 0; cursor: pointer; flex-shrink: 0;">
                        <input type="text" inputmode="decimal" spellcheck="false" autocomplete="off" class="spms-input field-budget" maxlength="18" data-raw-value="${escapeHtml(rawBudget)}" data-full-value="${escapeHtml(fullBudget)}" value="${escapeHtml(fullBudget)}" placeholder="0.00" style="flex: 1; min-width: 0; width: 100%; border: none; outline: none; font-size: 11px; text-align: right; background: transparent; font-family: inherit; font-weight: 500; color: #0f172a; padding: 1px 1px; transition: font-size 0.1s ease;">
                    </div>
                </td>

                <!-- Divisions Accountable -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accountable" rows="3" placeholder="Divisions accountable...">${escapeHtml(data.accountable || '')}</textarea>
                </td>

                <!-- Actual Accomplishment -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishment...">${escapeHtml(data.accomplishments)}</textarea>
                </td>

                <!-- Rating Q, T, E Inputs (Amber #ffe599 matching Excel sheet) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #ffe599;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-q" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #ffe599;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-t" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #ffe599;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-e" style="background-color: #ffe599;">
                </td>

                <!-- Row Average (Auto) -->
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
        } else {
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

                <!-- Rating Q, T, E Inputs (Flat Whole Numbers 1 to 5) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-q">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
                        oninput="handleScoreInput(this)" 
                        onkeydown="handleScoreKeydown(event, this)" 
                        ondblclick="clearScore(this)" 
                        class="spms-score-input field-t">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        title="Enter 1 to 5. Erase or press Esc to clear" 
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
        }

        tbody.appendChild(tr);

        // Decimal formatting and currency sync for Budget inputs
        const budgetInput = tr.querySelector('.field-budget');
        const curInput = tr.querySelector('.field-budget-currency');
        if (budgetInput) {
            budgetInput.setAttribute('spellcheck', 'false');
            budgetInput.setAttribute('autocomplete', 'off');
            budgetInput.setAttribute('maxlength', '16');
            adjustBudgetFontSize(budgetInput);
        }
        if (curInput) {
            const syncW = (el) => {
                el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
            };
            syncW(curInput);
            curInput.addEventListener('input', function() {
                syncW(this);
                if (budgetInput) adjustBudgetFontSize(budgetInput);
            });
            curInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '₱';
                    syncW(this);
                }
                if (budgetInput) adjustBudgetFontSize(budgetInput);
            });
        }

        recalculateForm();
    }

    // Global delegation for .field-budget to strictly enforce numbers-only input everywhere
    document.addEventListener('keydown', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key) ||
            (e.ctrlKey || e.metaKey)) {
            return;
        }
        if (/^[0-9]$/.test(e.key)) {
            const val = el.value || '';
            const selStart = el.selectionStart;
            const selEnd = el.selectionEnd;
            const newVal = val.substring(0, selStart) + e.key + val.substring(selEnd);
            const parts = newVal.split('.');
            if (parts[0].length > 12) {
                e.preventDefault();
                return;
            }
            if (parts.length > 1 && parts[1].length > 2) {
                e.preventDefault();
                return;
            }
            return;
        }
        if (e.key === '.') {
            const selStart = el.selectionStart;
            const selEnd = el.selectionEnd;
            const selectedText = el.value.substring(selStart, selEnd);
            if (!el.value.includes('.') || selectedText.includes('.')) {
                return;
            }
        }
        e.preventDefault();
    });

    document.addEventListener('input', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetInput(el);
    });

    document.addEventListener('paste', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text') || '';
        const clean = sanitizeBudgetInput(text);
        document.execCommand('insertText', false, clean);
        applyBudgetInput(el);
    });

    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetFocus(el);
    });

    document.addEventListener('focusout', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetBlur(el);
    });

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
        if (IS_IPERF) {
            // Flat unweighted arithmetic calculation for IPERF
            const rows = document.querySelectorAll('.table-row-core');
            let sumOfRowAvgs = 0;
            let totalRated = 0;

            rows.forEach(row => {
                const q = parseWholeScore(row.querySelector('.field-q')?.value);
                const t = parseWholeScore(row.querySelector('.field-t')?.value);
                const e = parseWholeScore(row.querySelector('.field-e')?.value);

                let rowSum = 0;
                let rowInputs = 0;
                if (q !== null) { rowSum += q; rowInputs++; }
                if (t !== null) { rowSum += t; rowInputs++; }
                if (e !== null) { rowSum += e; rowInputs++; }

                let rowAvg = rowInputs > 0 ? (rowSum / rowInputs) : 0;
                const avgSpan = row.querySelector('.field-row-avg');
                if (avgSpan) avgSpan.innerText = rowInputs > 0 ? rowAvg.toFixed(2) : '—';

                if (rowInputs > 0) {
                    sumOfRowAvgs += rowAvg;
                    totalRated++;
                }
            });

            let overallAvg = totalRated > 0 ? (sumOfRowAvgs / totalRated) : 0;
            const overallAvgEl = document.getElementById('iperf-overall-average');
            if (overallAvgEl) overallAvgEl.innerText = totalRated > 0 ? overallAvg.toFixed(3) : '0.000';

            const adjectival = getAdjectivalRating(overallAvg, totalRated);
            const badgeEl = document.getElementById('iperf-adjectival-badge');
            if (badgeEl) {
                badgeEl.innerText = adjectival.text;
                badgeEl.style.background = adjectival.color;
            }

            const grandScoreEl = document.getElementById('grand-score');
            if (grandScoreEl) grandScoreEl.innerText = totalRated > 0 ? overallAvg.toFixed(3) : '0.000';
            return;
        }

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

    // Auto-expand textareas before printing
    window.addEventListener('beforeprint', () => {
        document.querySelectorAll('#printable-form textarea').forEach(ta => {
            ta.style.height = 'auto';
            ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
        });
        document.querySelectorAll('.field-budget').forEach(input => {
            if (input.dataset && input.dataset.fullValue) {
                input.value = input.dataset.fullValue;
            }
        });
    });

    window.addEventListener('afterprint', () => {
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
    });

    function addApproverBlock(name = '', position = '', date = '') {
        const container = document.getElementById('approvers-container');
        if (!container) return;
        const index = container.querySelectorAll('.approver-item').length;
        const div = document.createElement('div');
        div.className = 'approver-item';
        div.style.cssText = (index > 0 ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : '') + ' position: relative;';

        const namePlaceholder = IS_DPCR ? '(name of office head)' : 'Name of Approving Authority';
        const posPlaceholder = IS_DPCR ? '(position of office head)' : 'Official Designation';
        const nameColor = IS_DPCR ? '#ba372a' : '#dc2626';

        div.innerHTML = `
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                <tr>
                    <td style="width: ${IS_DPCR ? '65px' : '60px'}; border: none; padding: 3px 0; font-weight: bold; color: ${IS_DPCR ? '#000' : '#64748b'};">Name:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-name" ${index === 0 ? 'id="approver-name"' : ''} value="${escapeHtml(name)}" placeholder="${namePlaceholder}" style="font-weight: bold; color: ${nameColor}; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: ${IS_DPCR ? '60%' : '100%'};">
                        ${index > 0 ? `
                        <button type="button" onclick="removeApproverBlock(this)" class="print-hide" style="margin-left: 6px; color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; font-size: 9px; padding: 1px 5px; border-radius: 3px; cursor: pointer; font-weight: bold;" title="Remove this signatory">✕ Remove</button>
                        ` : (IS_DPCR ? `
                        <span style="color: #ba372a; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                        ` : '')}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${IS_DPCR ? '#000' : '#64748b'};">Position:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-pos" ${index === 0 ? 'id="approver-pos"' : ''} value="${escapeHtml(position)}" placeholder="${posPlaceholder}" style="color: ${IS_DPCR ? '#ba372a' : '#1e293b'}; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: ${IS_DPCR ? '80%' : '100%'};">
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${IS_DPCR ? '#000' : '#64748b'};">Date:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="date" class="field-approver-date" ${index === 0 ? 'id="approver-date"' : ''} value="${escapeHtml(date)}" onclick="this.showPicker && this.showPicker()" style="color: ${date ? '#0f172a' : '#ba372a'}; border: none; border-bottom: 1px solid ${date ? '#cbd5e1' : '#ba372a'}; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit; cursor: pointer;">
                    </td>
                </tr>
            </table>
        `;
        container.appendChild(div);
    }

    function removeApproverBlock(btn) {
        const item = btn.closest('.approver-item');
        if (item) {
            item.remove();
        }
    }

    function extractApprovers() {
        const items = document.querySelectorAll('#approvers-container .approver-item');
        const approvers = [];
        items.forEach(item => {
            const name = item.querySelector('.field-approver-name')?.value || '';
            const position = item.querySelector('.field-approver-pos')?.value || '';
            const date = item.querySelector('.field-approver-date')?.value || '';
            if (name || position || date || items.length === 1) {
                approvers.push({ name, position, date });
            }
        });
        if (approvers.length === 0) {
            approvers.push({
                name: document.getElementById('approver-name')?.value || '',
                position: document.getElementById('approver-pos')?.value || '',
                date: document.getElementById('approver-date')?.value || ''
            });
        }
        return approvers;
    }

    function saveTemplateForm() {
        const saveBtn = document.getElementById('btn-save-template');
        const saveBtnText = document.getElementById('save-btn-text');
        
        saveBtn.disabled = true;
        saveBtnText.innerText = 'Saving...';

        // Gather structured data
        const defaultTitle = IS_DPCR 
            ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' 
            : (IS_OPCR ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : (IS_IPERF ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)'));
        const title = document.getElementById('template-title').value.trim() || defaultTitle;
        const approversList = extractApprovers();
        
        const formDataPayload = {
            title: title,
            doc_type: IS_DPCR ? 'dpcr' : (IS_OPCR ? 'opcr' : (IS_IPERF ? 'iperf' : 'ipcr')),
            currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            budget_currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            weights: CATEGORY_WEIGHTS,
            classification: document.getElementById('ratee-classification')?.value || '',
            ratee: {
                name: document.getElementById('ratee-name')?.value || '',
                position: document.getElementById('ratee-position')?.value || '',
                dept: document.getElementById('ratee-dept')?.value || '',
                period: document.getElementById('ratee-period')?.value || '',
                classification: document.getElementById('ratee-classification')?.value || ''
            },
            approvers: approversList,
            approver: approversList[0] || { name: '', position: '', date: '' },
            rateeSign: {
                name: document.getElementById('ratee-sign-name')?.value || document.getElementById('ratee-name')?.value || '',
                date: document.getElementById('ratee-sign-date')?.value || ''
            },
            categories: {
                core: extractRowsData('core'),
                strategic: IS_IPERF ? [] : extractRowsData('strategic'),
                support: IS_IPERF ? [] : extractRowsData('support')
            },
            pmtRemarks: document.getElementById('pmt-remarks')?.value || '',
            signatories: IS_IPERF ? {
                targets_prepared_by: {
                    name: document.getElementById('sig-targets-prepared-name')?.value || '',
                    date: document.getElementById('sig-targets-prepared-date')?.value || ''
                },
                targets_approved_by: {
                    name: document.getElementById('sig-targets-approved-name')?.value || '',
                    date: document.getElementById('sig-targets-approved-date')?.value || ''
                },
                eval_rated_by: {
                    name: document.getElementById('sig-eval-rated-name')?.value || '',
                    date: document.getElementById('sig-eval-rated-date')?.value || ''
                },
                eval_conforme: {
                    name: document.getElementById('sig-eval-conforme-name')?.value || '',
                    date: document.getElementById('sig-eval-conforme-date')?.value || ''
                },
                ratee: document.getElementById('sig-targets-prepared-name')?.value || document.getElementById('ratee-name')?.value || '',
                dean: document.getElementById('sig-targets-approved-name')?.value || '',
                vp: ''
            } : {
                ratee: document.getElementById('sig-ratee-name')?.value || '',
                ratee_date: document.getElementById('sig-ratee-date')?.value || '',
                dean: document.getElementById('sig-dean-name')?.value || '',
                dean_date: document.getElementById('sig-dean-date')?.value || '',
                vp: document.getElementById('sig-vp-name')?.value || ''
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
        if (e.key === 'Escape') {
            e.preventDefault();
            clearScore(input);
            return;
        }
        // Disallow non-numeric keys: 'e', 'E', '+', '-', '.', ','
        if (['e', 'E', '+', '-', '.', ','].includes(e.key)) {
            e.preventDefault();
            return;
        }
        // Disallow '0' directly since SPMS ratings are 1 to 5
        if (e.key === '0') {
            e.preventDefault();
            return;
        }
        // ArrowDown at 1 or blank clears back to blank
        if (e.key === 'ArrowDown' && (input.value === '1' || input.value === '0' || input.value === '')) {
            e.preventDefault();
            clearScore(input);
            return;
        }
        // ArrowUp when blank starts at 1
        if (e.key === 'ArrowUp' && (input.value === '' || input.value === null)) {
            e.preventDefault();
            input.value = '1';
            handleScoreInput(input);
            return;
        }
        // ArrowUp at 5 stays at 5
        if (e.key === 'ArrowUp' && input.value === '5') {
            e.preventDefault();
            return;
        }
    }

    function handleScoreInput(input) {
        let raw = input.value.trim();
        if (raw === '') {
            input.value = '';
            recalculateForm();
            return;
        }
        // If user typed a new digit while one already existed (e.g. was 5, typed 3 -> "53"),
        // take the newly typed digit if it's 1-5 so replacing digits feels effortless
        if (raw.length > 1) {
            const lastChar = raw.slice(-1);
            if (['1', '2', '3', '4', '5'].includes(lastChar)) {
                raw = lastChar;
            }
        }
        const num = parseInt(raw, 10);
        // Only allow 1 to 5. If 0, > 5, or invalid, clear to blank
        if (isNaN(num) || num < 1 || num > 5) {
            input.value = '';
        } else {
            input.value = num;
        }
        recalculateForm();
    }

    function parseWholeScore(val) {
        if (val === null || val === undefined) return null;
        const str = String(val).trim();
        if (str === '') return null;
        const num = parseInt(str, 10);
        if (isNaN(num) || num < 1 || num > 5) return null;
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

    // Auto-expand textareas before printing
    window.addEventListener('beforeprint', () => {
        document.querySelectorAll('#printable-form textarea').forEach(ta => {
            ta.style.height = 'auto';
            ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
        });
        document.querySelectorAll('.field-budget').forEach(input => {
            if (input.dataset && input.dataset.fullValue) {
                input.value = input.dataset.fullValue;
            }
        });
    });

    window.addEventListener('afterprint', () => {
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
    });

    function addApproverBlock(name = '', position = '', date = '') {
        const container = document.getElementById('approvers-container');
        if (!container) return;
        const index = container.querySelectorAll('.approver-item').length;
        const div = document.createElement('div');
        div.className = 'approver-item';
        div.style.cssText = (index > 0 ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : '') + ' position: relative;';

        const namePlaceholder = IS_DPCR ? '(name of office head)' : 'Name of Approving Authority';
        const posPlaceholder = IS_DPCR ? '(position of office head)' : 'Official Designation';
        const nameColor = IS_DPCR ? '#ba372a' : '#dc2626';

        div.innerHTML = `
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                <tr>
                    <td style="width: ${IS_DPCR ? '65px' : '60px'}; border: none; padding: 3px 0; font-weight: bold; color: ${IS_DPCR ? '#000' : '#64748b'};">Name:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-name" ${index === 0 ? 'id="approver-name"' : ''} value="${escapeHtml(name)}" placeholder="${namePlaceholder}" style="font-weight: bold; color: ${nameColor}; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: ${IS_DPCR ? '60%' : '100%'};">
                        ${index > 0 ? `
                        <button type="button" onclick="removeApproverBlock(this)" class="print-hide" style="margin-left: 6px; color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; font-size: 9px; padding: 1px 5px; border-radius: 3px; cursor: pointer; font-weight: bold;" title="Remove this signatory">✕ Remove</button>
                        ` : (IS_DPCR ? `
                        <span style="color: #ba372a; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                        ` : '')}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${IS_DPCR ? '#000' : '#64748b'};">Position:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-pos" ${index === 0 ? 'id="approver-pos"' : ''} value="${escapeHtml(position)}" placeholder="${posPlaceholder}" style="color: ${IS_DPCR ? '#ba372a' : '#1e293b'}; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: ${IS_DPCR ? '80%' : '100%'};">
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${IS_DPCR ? '#000' : '#64748b'};">Date:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="date" class="field-approver-date" ${index === 0 ? 'id="approver-date"' : ''} value="${escapeHtml(date)}" onclick="this.showPicker && this.showPicker()" style="color: ${date ? '#0f172a' : '#ba372a'}; border: none; border-bottom: 1px solid ${date ? '#cbd5e1' : '#ba372a'}; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit; cursor: pointer;">
                    </td>
                </tr>
            </table>
        `;
        container.appendChild(div);
    }

    function removeApproverBlock(btn) {
        const item = btn.closest('.approver-item');
        if (item) {
            item.remove();
        }
    }

    function extractApprovers() {
        const items = document.querySelectorAll('#approvers-container .approver-item');
        const approvers = [];
        items.forEach(item => {
            const name = item.querySelector('.field-approver-name')?.value || '';
            const position = item.querySelector('.field-approver-pos')?.value || '';
            const date = item.querySelector('.field-approver-date')?.value || '';
            if (name || position || date || items.length === 1) {
                approvers.push({ name, position, date });
            }
        });
        if (approvers.length === 0) {
            approvers.push({
                name: document.getElementById('approver-name')?.value || '',
                position: document.getElementById('approver-pos')?.value || '',
                date: document.getElementById('approver-date')?.value || ''
            });
        }
        return approvers;
    }

    function saveTemplateForm() {
        const saveBtn = document.getElementById('btn-save-template');
        const saveBtnText = document.getElementById('save-btn-text');
        
        saveBtn.disabled = true;
        saveBtnText.innerText = 'Saving...';

        // Gather structured data
        const defaultTitle = IS_DPCR 
            ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' 
            : (IS_OPCR ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : (IS_IPERF ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)'));
        const title = document.getElementById('template-title').value.trim() || defaultTitle;
        const approversList = extractApprovers();
        
        const formDataPayload = {
            title: title,
            doc_type: IS_DPCR ? 'dpcr' : (IS_OPCR ? 'opcr' : (IS_IPERF ? 'iperf' : 'ipcr')),
            currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            budget_currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            weights: CATEGORY_WEIGHTS,
            classification: document.getElementById('ratee-classification')?.value || '',
            ratee: {
                name: document.getElementById('ratee-name')?.value || '',
                position: document.getElementById('ratee-position')?.value || '',
                dept: document.getElementById('ratee-dept')?.value || '',
                period: document.getElementById('ratee-period')?.value || '',
                classification: document.getElementById('ratee-classification')?.value || ''
            },
            approvers: approversList,
            approver: approversList[0] || { name: '', position: '', date: '' },
            rateeSign: {
                name: document.getElementById('ratee-sign-name')?.value || document.getElementById('ratee-name')?.value || '',
                date: document.getElementById('ratee-sign-date')?.value || ''
            },
            categories: {
                core: extractRowsData('core'),
                strategic: IS_IPERF ? [] : extractRowsData('strategic'),
                support: IS_IPERF ? [] : extractRowsData('support')
            },
            pmtRemarks: document.getElementById('pmt-remarks')?.value || '',
            signatories: IS_IPERF ? {
                targets_prepared_by: {
                    name: document.getElementById('sig-targets-prepared-name')?.value || '',
                    date: document.getElementById('sig-targets-prepared-date')?.value || ''
                },
                targets_approved_by: {
                    name: document.getElementById('sig-targets-approved-name')?.value || '',
                    date: document.getElementById('sig-targets-approved-date')?.value || ''
                },
                eval_rated_by: {
                    name: document.getElementById('sig-eval-rated-name')?.value || '',
                    date: document.getElementById('sig-eval-rated-date')?.value || ''
                },
                eval_conforme: {
                    name: document.getElementById('sig-eval-conforme-name')?.value || '',
                    date: document.getElementById('sig-eval-conforme-date')?.value || ''
                },
                ratee: document.getElementById('sig-targets-prepared-name')?.value || document.getElementById('ratee-name')?.value || '',
                dean: document.getElementById('sig-targets-approved-name')?.value || '',
                vp: ''
            } : {
                ratee: document.getElementById('sig-ratee-name')?.value || '',
                ratee_date: document.getElementById('sig-ratee-date')?.value || '',
                dean: document.getElementById('sig-dean-name')?.value || '',
                dean_date: document.getElementById('sig-dean-date')?.value || '',
                vp: document.getElementById('sig-vp-name')?.value || ''
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

            const rowData = {
                mfo: row.querySelector('.field-mfo')?.value || '',
                indicators: row.querySelector('.field-indicators')?.value || '',
                accomplishments: row.querySelector('.field-accomplishments')?.value || '',
                q: q !== null ? q : '',
                t: t !== null ? t : '',
                e: e !== null ? e : '',
                remarks: row.querySelector('.field-remarks')?.value || ''
            };

            if (IS_DPCR || IS_OPCR) {
                const bInput = row.querySelector('.field-budget');
                rowData.budget = bInput?.dataset?.rawValue || bInput?.dataset?.fullValue || bInput?.value.trim() || '';
                rowData.budget_currency = row.querySelector('.field-budget-currency')?.value.trim() || '₱';
                rowData.accountable = row.querySelector('.field-accountable')?.value || '';
            }
            if (IS_DPCR) {
                rowData.std_5 = row.querySelector('.field-std-5')?.value || '';
                rowData.std_4 = row.querySelector('.field-std-4')?.value || '';
                rowData.std_3 = row.querySelector('.field-std-3')?.value || '';
                rowData.std_2 = row.querySelector('.field-std-2')?.value || '';
                rowData.std_1 = row.querySelector('.field-std-1')?.value || '';
            }

            result.push(rowData);
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
    function extractRowsData(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        const result = [];

        rows.forEach(row => {
            const q = parseWholeScore(row.querySelector('.field-q')?.value);
            const t = parseWholeScore(row.querySelector('.field-t')?.value);
            const e = parseWholeScore(row.querySelector('.field-e')?.value);

            const rowData = {
                mfo: row.querySelector('.field-mfo')?.value || '',
                indicators: row.querySelector('.field-indicators')?.value || '',
                accomplishments: row.querySelector('.field-accomplishments')?.value || '',
                q: q !== null ? q : '',
                t: t !== null ? t : '',
                e: e !== null ? e : '',
                remarks: row.querySelector('.field-remarks')?.value || ''
            };

            if (IS_DPCR || IS_OPCR) {
                const bInput = row.querySelector('.field-budget');
                rowData.budget = bInput?.dataset?.rawValue || bInput?.dataset?.fullValue || bInput?.value.trim() || '';
                rowData.budget_currency = row.querySelector('.field-budget-currency')?.value.trim() || '₱';
                rowData.accountable = row.querySelector('.field-accountable')?.value || '';
            }
            if (IS_DPCR) {
                rowData.std_5 = row.querySelector('.field-std-5')?.value || '';
                rowData.std_4 = row.querySelector('.field-std-4')?.value || '';
                rowData.std_3 = row.querySelector('.field-std-3')?.value || '';
                rowData.std_2 = row.querySelector('.field-std-2')?.value || '';
                rowData.std_1 = row.querySelector('.field-std-1')?.value || '';
            }

            result.push(rowData);
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