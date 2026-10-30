<style>
    .spms-sheet-container {
        width: 100%;
        min-width: fit-content;
        max-width: <?= $isDocDpcr ? '1600px' : ($isDocOpcr ? '1550px' : '1440px') ?>;
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 25px -3px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04);
        border: 1px solid #cbd5e1;
        padding: 16px 12px;
        box-sizing: border-box;
        font-family: inherit;
        display: block !important;
        height: auto !important;
        min-height: fit-content !important;
        border-radius: 8px;
    }
    @media (min-width: 640px) {
        .spms-sheet-container {
            padding: 24px 28px;
        }
    }
    @media (min-width: 1024px) {
        .spms-sheet-container {
            padding: 36px 40px;
            border-radius: 6px;
        }
    }
    .spms-sheet-container input::placeholder,
    .spms-sheet-container textarea::placeholder {
        color: #94a3b8;
        font-style: italic;
        opacity: 0.9;
    }
    .spms-meta-input {
        color: #0f172a;
        border: none;
        border-bottom: 1.5px solid #cbd5e1;
        outline: none;
        background: transparent;
        font-family: inherit;
        font-size: 11px;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .spms-meta-input:focus {
        border-bottom-color: #059669;
        background: #ffffff;
    }
    .spms-meta-input:disabled {
        border-bottom-color: #e2e8f0;
        color: #334155;
        cursor: not-allowed;
    }
    /* Hide number input spinners / up-down arrows for weights and meta inputs */
    input[data-cat-weight]::-webkit-outer-spin-button,
    input[data-cat-weight]::-webkit-inner-spin-button,
    .spms-cat-weight-input::-webkit-outer-spin-button,
    .spms-cat-weight-input::-webkit-inner-spin-button,
    .spms-meta-input::-webkit-outer-spin-button,
    .spms-meta-input::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
        display: none !important;
    }
    input[data-cat-weight],
    .spms-cat-weight-input,
    .spms-meta-input[type="number"] {
        -moz-appearance: textfield !important;
        appearance: textfield !important;
    }
    .spms-table-responsive-wrapper {
        width: 100%;
        overflow: visible;
        margin-bottom: 0.5rem;
        position: relative;
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
    @media screen and (max-width: 1023px) {
        .spms-table {
            min-width: <?= $isDocDpcr ? '1180px' : ($isDocOpcr ? '1050px' : ($isDocIperf ? '850px' : '880px')) ?> !important;
        }
    }
    @media print {
        .spms-sheet-container {
            max-width: 100% !important;
            width: 100% !important;
            min-width: 0 !important;
            box-shadow: none !important;
        }
    }
    .spms-table tbody {
        display: table-row-group !important;
        height: auto !important;
    }
    .spms-table th, .spms-table td {
        border: 1px solid #000000;
    }
    /* Seamless Full-Bleed Excel/Sheets Table Cells */
    .spms-table td.spms-cell-input {
        padding: 0 !important;
        margin: 0 !important;
        position: relative !important;
        vertical-align: top !important;
        background-color: #ffffff;
        height: 100%;
    }
    .spms-table td {
        -webkit-touch-callout: none !important;
    }
    .spms-table td.spms-cell-active,
    .spms-table td.spms-cell-input:focus-within,
    .spms-table td.spms-score-cell:focus-within {
        outline: 2px solid #059669 !important;
        outline-offset: -2px !important;
        z-index: 10 !important;
        background-color: #ffffff !important;
    }
    /* Exclude Category Header Rows and Weight Inputs from Spreadsheet Cell Selection */
    .spms-table td[id^="label-cat-"],
    .spms-table td[id^="label-cat-"].spms-cell-active,
    .spms-table td[id^="label-cat-"]:focus-within,
    .spms-table tr.spms-category-header td,
    .spms-table tr.spms-category-header td.spms-cell-active,
    .spms-table tr.spms-category-header td:focus-within {
        outline: none !important;
        box-shadow: none !important;
    }
    html.dark .spms-table td[id^="label-cat-"],
    html.dark .spms-table tr.spms-category-header td {
        background-color: #27272a !important;
    }
    .spms-cat-weight-input:focus {
        background: transparent !important;
        outline: none !important;
        border-bottom-color: #10b981 !important;
    }
    /* Supervising Authorities & TWG 2-Column Responsive Layout */
    .spms-supervisors-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 1.25rem !important;
    }
    @media (min-width: 640px) {
        .spms-supervisors-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 1.5rem !important;
            align-items: start !important;
        }
    }
    .spms-table td.spms-cell-input,
    .spms-table td[data-field-name],
    .spms-table td.spms-score-cell {
        cursor: pointer;
        touch-action: pan-x pan-y manipulation !important;
        -webkit-tap-highlight-color: transparent !important;
        -webkit-touch-callout: none !important;
    }
    :root {
        --spms-row-min-height: 68px;
    }
    .spms-table tr {
        min-height: var(--spms-row-min-height, 68px);
    }
    .spms-table td.spms-cell-input,
    .spms-table td.spms-score-cell {
        min-height: var(--spms-row-min-height, 68px);
    }
    .field-row-avg-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: var(--spms-row-min-height, 68px);
    }
    .spms-textarea {
        width: 100% !important;
        height: 100%;
        min-height: var(--spms-row-min-height, 68px);
        background: transparent;
        border: none !important;
        padding: 8px 10px !important;
        font-size: 11px;
        line-height: 1.45;
        color: #0f172a;
        resize: none !important;
        box-sizing: border-box;
        border-radius: 0 !important;
        font-family: inherit;
        outline: none !important;
        display: block;
        overflow-y: hidden;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .spms-textarea::-webkit-resizer {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        border: none !important;
        background: transparent !important;
    }
    .spms-textarea:disabled {
        resize: none !important;
    }
    .spms-textarea:hover:not(:disabled) {
        background: rgba(248, 250, 252, 0.4);
    }
    .spms-textarea:focus {
        background: #ffffff;
        outline: none !important;
    }
    .spms-table textarea:disabled,
    .spms-table input:disabled {
        color: #334155;
        background: transparent !important;
        cursor: pointer !important;
        pointer-events: none !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        -webkit-touch-callout: none !important;
    }
    .field-mfo:disabled {
        color: #0f172a !important;
        font-weight: 600;
        background: transparent !important;
    }
    .budget-input-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100% !important;
        min-height: var(--spms-row-min-height, 68px) !important;
        padding: 4px 6px !important;
        box-sizing: border-box !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        outline: none !important;
        cursor: text !important;
    }
    .spms-score-cell {
        position: relative;
        padding: 0 !important;
        vertical-align: middle !important;
        text-align: center !important;
        background-color: #ffffff;
    }
    .spms-score-input {
        width: 100%;
        height: 100%;
        min-height: var(--spms-row-min-height, 68px);
        text-align: center;
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
        background: transparent;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        box-sizing: border-box;
        -moz-appearance: textfield;
        appearance: textfield;
        outline: none !important;
    }
    .spms-score-input::-webkit-outer-spin-button,
    .spms-score-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .spms-score-input:hover:not(:disabled) {
        background: rgba(248, 250, 252, 0.4);
    }
    .spms-score-input:focus {
        background: #ffffff;
        outline: none !important;
    }
    .spms-score-input:disabled {
        background: transparent !important;
        color: #94a3b8;
        cursor: default;
    }

    /* Excel-style Cell Comment Corner Triangle (Amber for Open, Green for Resolved) */
    .has-cell-comment::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 9px 9px 0;
        border-color: transparent #d97706 transparent transparent;
        pointer-events: none;
        z-index: 20;
    }
    .has-cell-comment.comment-resolved::after {
        border-color: transparent #10b981 transparent transparent;
    }
    .has-cell-comment:hover::after {
        border-width: 0 11px 11px 0;
    }

    /* Right-Click Context Menu */
    #spms-context-menu {
        position: fixed;
        z-index: 99998;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        padding: 4px 0;
        min-width: 190px;
        font-size: 12px;
        color: #1e293b;
        user-select: none;
        display: none;
    }
    .dark #spms-context-menu {
        background: #18181b;
        border-color: #27272a;
        color: #f4f4f5;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    }
    .spms-ctx-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 12px;
        cursor: pointer;
        transition: background-color 0.1s ease;
        gap: 12px;
    }
    .spms-ctx-item:hover:not(.disabled) {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .dark .spms-ctx-item:hover:not(.disabled) {
        background-color: #27272a;
        color: #ffffff;
    }
    .spms-ctx-item.disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .spms-ctx-item .ctx-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .spms-ctx-item .ctx-shortcut {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 500;
    }
    .spms-ctx-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 4px 0;
    }
    .dark .spms-ctx-divider {
        background: #27272a;
    }

    /* Mobile long-press optimization for spreadsheet grid */
    td.spms-cell-input, td[data-field-name], td.spms-score-cell, .spms-textarea {
        -webkit-touch-callout: none !important;
        touch-action: pan-x pan-y manipulation;
    }

    /* Floating Threaded Comments Overlay Card */
    #spms-comment-overlay {
        position: fixed;
        z-index: 99999;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        width: 360px;
        max-width: 90vw;
        max-height: 480px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .dark #spms-comment-overlay {
        background: #18181b;
        border-color: #3f3f46;
        color: #f4f4f5;
    }
    /* Score Role Attribution Indicators (Minimal Corner Notification Marker) */
    .spms-score-cell {
        position: relative;
        padding: 2px 2px !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
    .spms-table td.spms-cell-budget {
        vertical-align: middle !important;
        text-align: center !important;
        height: 100% !important;
        padding: 0 !important;
        position: relative !important;
    }
    .budget-input-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100% !important;
        min-height: var(--spms-row-min-height, 68px) !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        padding: 4px 6px !important;
        gap: 3px !important;
        box-sizing: border-box !important;
        box-shadow: none !important;
        outline: none !important;
        cursor: text !important;
    }
    .budget-input-wrapper:hover:not(:has(:disabled)) {
        background: rgba(248, 250, 252, 0.4) !important;
        border: none !important;
        box-shadow: none !important;
    }
    .budget-input-wrapper:focus-within {
        border: none !important;
        box-shadow: none !important;
        background: #ffffff !important;
        outline: none !important;
    }
    html.dark .budget-input-wrapper:hover:not(:has(:disabled)) {
        background: rgba(255, 255, 255, 0.03) !important;
    }
    html.dark .budget-input-wrapper:focus-within {
        background: transparent !important;
    }
    .budget-input-wrapper:has(:disabled) {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        cursor: default !important;
    }
    .field-budget-currency {
        border: none !important;
        border-right: none !important;
        background: transparent !important;
        outline: none !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #0284c7 !important;
        padding: 0 !important;
        cursor: pointer;
        flex-shrink: 0;
        text-align: center;
        box-shadow: none !important;
        line-height: 1 !important;
    }
    .field-budget-currency:disabled {
        color: #64748b !important;
        cursor: default;
    }
    .field-budget {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        outline: none !important;
        background: transparent !important;
        box-shadow: none !important;
        font-size: 11px !important;
        text-align: center !important;
        font-family: inherit !important;
        font-weight: 500 !important;
        color: #0f172a !important;
        padding: 0 4px !important;
        box-sizing: border-box !important;
        line-height: 1.4 !important;
    }
    .field-budget::placeholder {
        text-align: center !important;
        padding-right: 0 !important;
    }
    .field-budget:disabled {
        color: #64748b !important;
        background: transparent !important;
        cursor: default;
    }
    .btn-add-dashed, .btn-add-row, #tfoot-add-core, #tfoot-add-strategic, #tfoot-add-support, [id^="tfoot-add-"] {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
    }
    .header-budget-currency {
        border: none;
        border-bottom: 1px dashed #10b981;
        background: transparent;
        color: #047857;
        outline: none;
        font-size: 9.5px;
        text-align: center;
        font-weight: bold;
        cursor: pointer;
    }
    .header-budget-currency:focus {
        border-bottom-style: solid;
        color: #047857;
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
    .btn-add-dashed {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        border: none !important;
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
    .subtotal-badge {
        font-size: 10px;
        font-weight: 800;
        background: #0284c7;
        color: #ffffff;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-block;
    }
    .spms-category-header {
        background: #e2e8f0;
        border-top: 2px solid #000000;
        border-bottom: 1px solid #000000;
    }

    /* Responsive Mobile Meta, Summary & Signatories Matrix Layouts */
    @media screen and (max-width: 767px) {
        .spms-meta-matrix,
        .spms-meta-matrix > tbody,
        .spms-meta-matrix > tbody > tr,
        .spms-meta-matrix > tr {
            display: block !important;
            width: 100% !important;
        }
        .spms-meta-matrix > tbody > tr > td,
        .spms-meta-matrix > tr > td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }
        .spms-meta-matrix > tbody > tr > td:last-child,
        .spms-meta-matrix > tr > td:last-child {
            border-bottom: none !important;
        }

        .spms-summary-matrix,
        .spms-summary-matrix > tbody,
        .spms-summary-matrix > tbody > tr,
        .spms-summary-matrix > tr {
            display: block !important;
            width: 100% !important;
        }
        .spms-summary-matrix > tbody > tr > td,
        .spms-summary-matrix > tr > td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }
        .spms-summary-matrix > tbody > tr > td:last-child,
        .spms-summary-matrix > tr > td:last-child {
            border-bottom: none !important;
        }

        .spms-signatories-matrix,
        .spms-signatories-matrix > tbody,
        .spms-signatories-matrix > tbody > tr,
        .spms-signatories-matrix > tr {
            display: block !important;
            width: 100% !important;
        }
        .spms-signatories-matrix > tbody > tr > td,
        .spms-signatories-matrix > tr > td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }
        .spms-signatories-matrix > tbody > tr > td:last-child,
        .spms-signatories-matrix > tr > td:last-child {
            border-bottom: none !important;
        }
    }

    @media screen and (max-width: 639px) {
        .spms-navy-bar {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 12px 10px !important;
            gap: 8px !important;
        }
        .spms-navy-bar > td {
            display: block !important;
            width: 100% !important;
            padding: 2px 0 !important;
            text-align: center !important;
            border: none !important;
        }
    }

    @media print {
        @page {
            size: letter landscape;
            margin: 8mm 12mm;
        }
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 10px !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }
        header, nav, aside, .print-hide, .twg-col, #tab-bar, .tox {
            display: none !important;
        }
        main, #editor-container, #spms-form-workspace {
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
        .spms-table-responsive-wrapper {
            overflow: visible !important;
            width: 100% !important;
        }
        .spms-table {
            width: 100% !important;
            min-width: 0 !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
            page-break-inside: auto;
        }
        .spms-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .spms-meta-matrix, .spms-navy-bar, .spms-summary-matrix, .spms-summary-matrix tr, .spms-signatories-matrix, .spms-sheet-container > div, .spms-table-responsive-wrapper {
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
        .spms-meta-input {
            border-bottom: 1px solid #000000 !important;
        }
        #ratee-preamble-container input,
        #basis-roles-container input {
            max-width: 100% !important;
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
        /* Institutional Letterhead in Print (Official BSU & CSC colors) */
        .spms-letterhead-republic { color: #111827 !important; }
        .spms-letterhead-title { color: #436531 !important; text-shadow: none !important; }
        .spms-letterhead-location { color: #1f2937 !important; }
        .spms-letterhead-dept { color: #111827 !important; }
        input[type="date"] { color-scheme: light !important; }
        input[type="date"]::-webkit-calendar-picker-indicator { display: none !important; }
    }

    /* ===== Q/T/E whole-cell N/A toggle (Target Setting phase) ===== */
    .spms-score-cell.qte-toggleable {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .spms-score-cell.qte-toggleable .spms-score-input {
        pointer-events: none;
        cursor: pointer !important;
    }
    .spms-score-cell.qte-toggleable:hover {
        background-color: #ecfdf5 !important;
        box-shadow: inset 0 0 0 2px #10b981;
    }
    .spms-score-cell.qte-na,
    .spms-score-cell.qte-toggleable.qte-na {
        background-color: #f1f5f9 !important;
        background-image: repeating-linear-gradient(135deg, transparent 0 6px, rgba(100, 116, 139, 0.15) 6px 12px) !important;
    }
    .spms-score-cell.qte-na .spms-score-input,
    .spms-score-cell.qte-toggleable.qte-na .spms-score-input {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: transparent !important;
        user-select: none !important;
        pointer-events: none !important;
        width: 100% !important;
        height: 100% !important;
    }
    .spms-score-cell.qte-toggleable.qte-na:hover {
        background-color: #fef2f2 !important;
        box-shadow: inset 0 0 0 2px #f87171 !important;
    }
    .spms-score-cell.qte-toggleable:not(.qte-na):focus-visible {
        outline: 2px solid #059669 !important;
        outline-offset: -2px;
    }
    .spms-table td.spms-score-cell.qte-na,
    .spms-table td.spms-score-cell.qte-na.spms-cell-active,
    .spms-table td.spms-score-cell.qte-na:focus,
    .spms-table td.spms-score-cell.qte-na:focus-within,
    .spms-table td.spms-score-cell.qte-na:focus-visible {
        outline: none !important;
        box-shadow: none !important;
    }
    .spms-table td.spms-score-cell.qte-na:focus-within,
    .spms-table td.spms-score-cell.qte-toggleable.qte-na:focus-within {
        background-color: #f1f5f9 !important;
    }
    html.dark .spms-table td.spms-score-cell.qte-na,
    html.dark .spms-table td.spms-score-cell.qte-na.spms-cell-active,
    html.dark .spms-table td.spms-score-cell.qte-na:focus,
    html.dark .spms-table td.spms-score-cell.qte-na:focus-within,
    html.dark .spms-table td.spms-score-cell.qte-na:focus-visible {
        outline: none !important;
        box-shadow: none !important;
    }
    html.dark .spms-table td.spms-score-cell.qte-na:focus-within,
    html.dark .spms-table td.spms-score-cell.qte-toggleable.qte-na:focus-within {
        background-color: #1e1e22 !important;
    }
    @keyframes qtePulse {
        0% { transform: scale(0.85); opacity: 0.4; }
        100% { transform: scale(1); opacity: 1; }
    }
    .spms-score-cell.qte-just-toggled .spms-score-input {
        animation: qtePulse 0.18s ease-out;
    }

    /* Baseline Letterhead typography and colors (Screen & Print) */
    .spms-letterhead-republic { color: #111827; }
    .spms-letterhead-title { color: #436531; }
    .spms-letterhead-location { color: #1f2937; }
    .spms-letterhead-dept { color: #111827; }

    /* Date picker color-scheme baseline */
    input[type="date"] {
        color-scheme: light;
    }

    /* High-Visibility Modern Scrollbars (Base & Light Mode) */
    .custom-scrollbar,
    textarea,
    .spms-textarea,
    .rubric-cell-textarea {
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 rgba(0, 0, 0, 0.05);
    }
    .custom-scrollbar::-webkit-scrollbar,
    textarea::-webkit-scrollbar,
    .spms-textarea::-webkit-scrollbar,
    .rubric-cell-textarea::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track,
    textarea::-webkit-scrollbar-track,
    .spms-textarea::-webkit-scrollbar-track,
    .rubric-cell-textarea::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.04);
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb,
    textarea::-webkit-scrollbar-thumb,
    .spms-textarea::-webkit-scrollbar-thumb,
    .rubric-cell-textarea::-webkit-scrollbar-thumb {
        background-color: #94a3b8;
        border-radius: 9999px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover,
    textarea::-webkit-scrollbar-thumb:hover,
    .spms-textarea::-webkit-scrollbar-thumb:hover,
    .rubric-cell-textarea::-webkit-scrollbar-thumb:hover {
        background-color: #64748b;
    }

    /* Rubric Matrix Full-Bleed Cell Inputs (Screen) */
    #digital-rubrics-table {
        border-collapse: collapse;
    }
    #digital-rubrics-table td.rubric-score-td {
        padding: 0 !important;
        margin: 0 !important;
        position: relative !important;
        vertical-align: top !important;
        height: 100%;
        background-color: #ffffff;
        -webkit-touch-callout: none !important;
        touch-action: pan-x pan-y manipulation;
    }
    #digital-rubrics-table td.rubric-score-td:focus-within,
    #digital-rubrics-table td.rubric-score-td.spms-cell-active {
        outline: 2px solid #059669 !important;
        outline-offset: -2px !important;
        z-index: 10 !important;
        background-color: #ffffff !important;
    }
    #digital-rubrics-table td.rubric-score-td.rubric-dim-na {
        background-color: #f8fafc !important;
        cursor: default !important;
    }
    .rubric-cell-textarea {
        width: 100% !important;
        height: 100% !important;
        min-height: 56px;
        padding: 10px 12px !important;
        margin: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #0f172a;
        font-size: 11px;
        line-height: 1.45;
        resize: none !important;
        box-sizing: border-box;
        display: block;
        font-family: inherit;
        outline: none !important;
        box-shadow: none !important;
        overflow-y: hidden !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        transition: background-color 0.15s ease;
        -webkit-touch-callout: none !important;
    }
    .rubric-cell-textarea:hover:not(:disabled) {
        background: rgba(248, 250, 252, 0.6) !important;
    }
    .rubric-cell-textarea:focus {
        background: #ffffff !important;
    }
    .rubric-cell-textarea:disabled,
    .rubric-cell-textarea[readonly] {
        cursor: default !important;
        pointer-events: none !important;
    }
    .rubric-cell-textarea.is-na,
    .rubric-cell-textarea:disabled {
        cursor: default !important;
        pointer-events: none !important;
        text-align: center !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: #94a3b8 !important;
        padding-top: 18px !important;
        letter-spacing: 0.05em;
        user-select: none;
    }

    /* ===== DOCUMENT SHEET DARK MODE (screen only; print always stays black-on-white) ===== */
    @media screen {
        html.dark .spms-sheet-container {
            background: #18181b !important;
            color: #e4e4e7 !important;
            border-color: #27272a !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.04) !important;
        }
        /* Grid lines */
        html.dark .spms-sheet-container table,
        html.dark .spms-sheet-container th,
        html.dark .spms-sheet-container td,
        html.dark .spms-sheet-container tr,
        html.dark .spms-sheet-container [style*="solid #000"],
        html.dark .spms-sheet-container [style*="#cbd5e1"],
        html.dark .spms-sheet-container [style*="#e2e8f0"],
        html.dark .spms-sheet-container [style*="solid #94a3b8"] {
            border-color: #3f3f46 !important;
        }
        /* Surfaces */
        html.dark .spms-sheet-container [style*="background: #ffffff"],
        html.dark .spms-sheet-container [style*="background-color: #ffffff"],
        html.dark .spms-sheet-container [style*="background: #fdfdfd"],
        html.dark .spms-sheet-container [style*="background: #fcfcfc"] {
            background: #18181b !important;
        }
        html.dark .spms-sheet-container [style*="background: #f8fafc"],
        html.dark .spms-sheet-container [style*="background-color: #f8fafc"],
        html.dark .spms-sheet-container [style*="background: #fafafa"] {
            background: #1f1f23 !important;
        }
        html.dark .spms-sheet-container [style*="background: #f1f5f9"],
        html.dark .spms-sheet-container [style*="background-color: #f1f5f9"],
        html.dark .spms-sheet-container [style*="background: #e2e8f0"],
        html.dark .spms-sheet-container .spms-category-header {
            background: #27272a !important;
        }
        html.dark .spms-sheet-container [style*="background: #ecfdf5"] { background: rgba(16, 185, 129, 0.12) !important; }
        html.dark .spms-sheet-container [style*="background: #f0f9ff"] { background: rgba(14, 165, 233, 0.12) !important; }
        html.dark .spms-sheet-container [style*="background: #fee2e2"] { background: rgba(239, 68, 68, 0.15) !important; }
        /* Text */
        html.dark .spms-sheet-container [style*="color: #000"],
        html.dark .spms-sheet-container [style*="color: #0f172a"],
        html.dark .spms-sheet-container [style*="color: #111827"],
        html.dark .spms-sheet-container [style*="color: #1f2937"],
        html.dark .spms-sheet-container [style*="color: #1e293b"] {
            color: #f4f4f5 !important;
        }
        html.dark .spms-sheet-container [style*="color: #334155"],
        html.dark .spms-sheet-container [style*="color: #475569"] { color: #d4d4d8 !important; }
        html.dark .spms-sheet-container [style*="color: #64748b"] { color: #a1a1aa !important; }
        html.dark .spms-sheet-container [style*="color: #047857"] { color: #34d399 !important; }
        html.dark .spms-sheet-container [style*="color: #0284c7"],
        html.dark .spms-sheet-container [style*="color: #0369a1"] { color: #38bdf8 !important; }
        html.dark .spms-sheet-container input::placeholder,
        html.dark .spms-sheet-container textarea::placeholder { color: #71717a !important; }

        /* Editable cells */
        html.dark .spms-table td.spms-cell-input,
        html.dark .spms-score-cell { background-color: #18181b !important; }
        html.dark .spms-table td.spms-cell-input:focus-within,
        html.dark .spms-table td.spms-score-cell:focus-within { background-color: #1c1c20 !important; outline-color: #10b981 !important; }
        html.dark .spms-textarea,
        html.dark .spms-score-input,
        html.dark .field-budget,
        html.dark .spms-meta-input { color: #f4f4f5 !important; }
        html.dark .spms-textarea:hover:not(:disabled),
        html.dark .spms-score-input:hover:not(:disabled) { background: rgba(255, 255, 255, 0.03) !important; }
        html.dark .spms-textarea:focus,
        html.dark .spms-score-input:focus,
        html.dark .spms-meta-input:focus { background: transparent !important; }
        html.dark .spms-textarea:disabled,
        html.dark .spms-score-input:disabled,
        html.dark .budget-input-wrapper:has(:disabled) { background: transparent !important; color: #a1a1aa !important; }
        html.dark .field-mfo:disabled { background: transparent !important; color: #e4e4e7 !important; }
        html.dark .field-budget:disabled { color: #a1a1aa !important; }
        html.dark .spms-meta-input { border-bottom-color: #3f3f46 !important; }
        html.dark .spms-meta-input:disabled { color: #d4d4d8 !important; }
        html.dark .budget-input-wrapper:focus-within { background: transparent !important; }

        /* Q/T/E toggle in dark */
        html.dark .spms-textarea::-webkit-resizer {
            display: none !important;
            border: none !important;
        }
        html.dark .spms-score-cell.qte-toggleable:hover { background-color: rgba(16, 185, 129, 0.12) !important; }
        html.dark .spms-score-cell.qte-na,
        html.dark .spms-score-cell.qte-toggleable.qte-na {
            background-color: #1e1e22 !important;
            background-image: repeating-linear-gradient(135deg, transparent 0 6px, rgba(255, 255, 255, 0.06) 6px 12px) !important;
        }
        html.dark .spms-score-cell.qte-na .spms-score-input,
        html.dark .spms-score-cell.qte-toggleable.qte-na .spms-score-input {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: transparent !important;
            width: 100% !important;
            height: 100% !important;
        }
        html.dark .spms-score-cell.qte-toggleable.qte-na:hover {
            background-color: rgba(239, 68, 68, 0.15) !important;
            box-shadow: inset 0 0 0 2px #ef4444 !important;
        }

        /* Institutional Official Letterhead in Dark Mode */
        html.dark .spms-official-letterhead {
            border-bottom-color: #3f3f46 !important;
        }
        html.dark .spms-letterhead-republic {
            color: #e4e4e7 !important;
        }
        html.dark .spms-letterhead-title {
            color: #4ade80 !important;
            text-shadow: 0 0 16px rgba(74, 222, 128, 0.25);
        }
        html.dark .spms-letterhead-location {
            color: #a1a1aa !important;
        }
        html.dark .spms-letterhead-dept {
            color: #93c5fd !important;
        }

        /* Native Date Picker & Calendar Icon in Dark Mode */
        html.dark input[type="date"],
        html.dark .spms-meta-input[type="date"],
        html.dark .spms-sheet-container input[type="date"] {
            color-scheme: dark !important;
        }
        html.dark input[type="date"]::-webkit-calendar-picker-indicator,
        html.dark .spms-meta-input[type="date"]::-webkit-calendar-picker-indicator,
        html.dark .spms-sheet-container input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer !important;
            opacity: 0.9 !important;
            transition: opacity 0.15s ease, transform 0.15s ease;
        }
        html.dark input[type="date"]::-webkit-calendar-picker-indicator:hover,
        html.dark .spms-meta-input[type="date"]::-webkit-calendar-picker-indicator:hover,
        html.dark .spms-sheet-container input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1 !important;
            transform: scale(1.08);
        }

        /* High-Contrast Visible Scrollbars in Dark Mode */
        html.dark .custom-scrollbar,
        html.dark textarea,
        html.dark .spms-textarea,
        html.dark .rubric-cell-textarea {
            scrollbar-color: #71717a rgba(24, 24, 27, 0.6) !important;
        }
        html.dark ::-webkit-scrollbar,
        html.dark .custom-scrollbar::-webkit-scrollbar,
        html.dark textarea::-webkit-scrollbar,
        html.dark .spms-textarea::-webkit-scrollbar,
        html.dark .rubric-cell-textarea::-webkit-scrollbar {
            width: 8px !important;
            height: 8px !important;
        }
        html.dark ::-webkit-scrollbar-track,
        html.dark .custom-scrollbar::-webkit-scrollbar-track,
        html.dark textarea::-webkit-scrollbar-track,
        html.dark .spms-textarea::-webkit-scrollbar-track,
        html.dark .rubric-cell-textarea::-webkit-scrollbar-track {
            background: rgba(24, 24, 27, 0.7) !important;
            border-radius: 9999px !important;
        }
        html.dark ::-webkit-scrollbar-thumb,
        html.dark .custom-scrollbar::-webkit-scrollbar-thumb,
        html.dark textarea::-webkit-scrollbar-thumb,
        html.dark .spms-textarea::-webkit-scrollbar-thumb,
        html.dark .rubric-cell-textarea::-webkit-scrollbar-thumb {
            background-color: #71717a !important;
            border-radius: 9999px !important;
            border: 1.5px solid #18181b !important;
            background-clip: padding-box !important;
        }
        html.dark ::-webkit-scrollbar-thumb:hover,
        html.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover,
        html.dark textarea::-webkit-scrollbar-thumb:hover,
        html.dark .spms-textarea::-webkit-scrollbar-thumb:hover,
        html.dark .rubric-cell-textarea::-webkit-scrollbar-thumb:hover {
            background-color: #a1a1aa !important;
        }
        html.dark ::-webkit-scrollbar-thumb:active,
        html.dark .custom-scrollbar::-webkit-scrollbar-thumb:active,
        html.dark textarea::-webkit-scrollbar-thumb:active,
        html.dark .spms-textarea::-webkit-scrollbar-thumb:active,
        html.dark .rubric-cell-textarea::-webkit-scrollbar-thumb:active {
            background-color: #d4d4d8 !important;
        }

        /* Rubric Matrix Full-Bleed Cells in Dark Mode */
        html.dark #digital-rubrics-table td.rubric-score-td {
            background-color: #18181b !important;
        }
        html.dark #digital-rubrics-table td.rubric-score-td:focus-within,
        html.dark #digital-rubrics-table td.rubric-score-td.spms-cell-active {
            outline-color: #10b981 !important;
            background-color: #1c1c20 !important;
        }
        html.dark #digital-rubrics-table td.rubric-score-td.rubric-dim-na {
            background-color: #1f1f23 !important;
        }
        html.dark .rubric-cell-textarea {
            color: #f4f4f5 !important;
        }
        html.dark .rubric-cell-textarea:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.03) !important;
        }
        html.dark .rubric-cell-textarea:focus {
            background: #1c1c20 !important;
        }
        html.dark .rubric-cell-textarea.is-na,
        html.dark .rubric-cell-textarea:disabled {
            color: #52525b !important;
        }
    }
</style>
