<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class CscExcelExporter
{
    /**
     * Generate and stream a standardized CSC SPMS spreadsheet.
     *
     * @param array $doc Document record from database
     * @param array|null $formData Parsed form data from tabs
     * @param array $ownerInfo Ratee account details
     * @param array|null $superiorInfo Supervisor details
     * @return void
     */
    public static function export(array $doc, ?array $formData, array $ownerInfo, ?array $superiorInfo = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ---------------------------------------------------------------------
        // 1. PAGE SETUP (CSC Standard: Letter, Landscape, Fit-to-Width)
        // ---------------------------------------------------------------------
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setShowGridLines(true);

        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.4);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.4);

        // Determine Form Type & Weights
        $titleRaw = strtoupper($formData['title'] ?? ($doc['title'] ?? ''));
        $docTypeUpper = strtoupper($doc['doc_type'] ?? 'IPCR');

        $isOpcr = str_contains($titleRaw, 'OPCR') || str_contains($titleRaw, 'OFFICE') || $docTypeUpper === 'OPCR';
        $isDpcr = str_contains($titleRaw, 'DPCR') || str_contains($titleRaw, 'DIVISION') || str_contains($titleRaw, 'DEPARTMENT') || $docTypeUpper === 'DPCR';
        $isIperf = str_contains($titleRaw, 'IPERF') || str_contains($titleRaw, 'NON-TEACHING') || $docTypeUpper === 'IPERF';

        if ($isOpcr) {
            $sheetTitle = 'OPCR - Office';
            $formFullTitle = 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)';
            $coreW = 0.60; $stratW = 0.25; $suppW = 0.15;
            $coreLabel = '1. Core Functions — Office Mandate & Strategic Targets (60%)';
            $stratLabel = '2. Strategic Functions — Research, Citations & Extension (25%)';
            $suppLabel = '3. Support Functions — Institutional Governance & Operations (15%)';
            $rateeRoleLabel = 'Unit Head / Executive';
            $approverRoleLabel = 'University President / PMT Chair';
        } elseif ($isDpcr) {
            $sheetTitle = 'DPCR - Division';
            $formFullTitle = 'DIVISION / DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)';
            $coreW = 0.60; $stratW = 0.25; $suppW = 0.15;
            $coreLabel = '1. Core Functions — Department Academic Operations (60%)';
            $stratLabel = '2. Strategic Functions — Department Extension & Research (25%)';
            $suppLabel = '3. Support Functions — Academic Support & Administration (15%)';
            $rateeRoleLabel = 'Department Chairperson / Unit Head';
            $approverRoleLabel = 'College Dean / Approving Authority';
        } elseif ($isIperf) {
            $sheetTitle = 'IPERF - Non-Teaching';
            $formFullTitle = 'INDIVIDUAL PERFORMANCE EVALUATION AND REVIEW FORM (IPERF)';
            $coreW = 0.70; $stratW = 0.20; $suppW = 0.10;
            $coreLabel = '1. Core Functions — Administrative & Technical Mandate (70%)';
            $stratLabel = '2. Strategic Functions — Process Improvement & Special Projects (20%)';
            $suppLabel = '3. Support Functions — Office Support & Cross-Functional Services (10%)';
            $rateeRoleLabel = 'Administrative Staff / Non-Teaching';
            $approverRoleLabel = 'Administrative Unit Head / Supervisor';
        } else {
            // Default: IPCR (Faculty)
            $sheetTitle = 'IPCR - Faculty';
            $formFullTitle = 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR) — FACULTY';
            $coreW = 0.70; $stratW = 0.20; $suppW = 0.10;
            $coreLabel = '1. Core Functions — Instruction & Teaching Load (70%)';
            $stratLabel = '2. Strategic Functions — Research, Citations & Extension Services (20%)';
            $suppLabel = '3. Support Functions — Committee Work, Thesis Advising & Governance (10%)';
            $rateeRoleLabel = 'Faculty Member / Professor';
            $approverRoleLabel = 'College Dean / Unit Head';
        }

        $sheet->setTitle(substr($sheetTitle, 0, 31));

        // ---------------------------------------------------------------------
        // 2. COLUMN WIDTHS (Standardized 8-Column Layout A to H)
        // ---------------------------------------------------------------------
        $sheet->getColumnDimension('A')->setWidth(26); // MFO / PAPs
        $sheet->getColumnDimension('B')->setWidth(26); // Success Indicators
        $sheet->getColumnDimension('C')->setWidth(26); // Actual Accomplishments
        $sheet->getColumnDimension('D')->setWidth(6.5); // Q
        $sheet->getColumnDimension('E')->setWidth(6.5); // E
        $sheet->getColumnDimension('F')->setWidth(6.5); // T
        $sheet->getColumnDimension('G')->setWidth(9.5); // Ave.
        $sheet->getColumnDimension('H')->setWidth(18); // Remarks

        // Base Font: Arial 10
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(9.5);

        // ---------------------------------------------------------------------
        // 3. INSTITUTIONAL CSC & BSU HEADER (Rows 1 to 4)
        // ---------------------------------------------------------------------
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->getStyle('A1')->getFont()->setSize(10)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'BENGUET STATE UNIVERSITY');
        $sheet->getStyle('A2')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'La Trinidad, Benguet • Strategic Performance Management System');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', $formFullTitle);
        $sheet->getStyle('A4')->getFont()->setSize(11.5)->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---------------------------------------------------------------------
        // 4. RATEE COMMITMENT STATEMENT (Rows 6 to 7)
        // ---------------------------------------------------------------------
        $rateeName = $formData['ratee']['name'] ?? ($ownerInfo['name'] ?? '—');
        $rateePos  = $formData['ratee']['position'] ?? ($ownerInfo['position'] ?? '—');
        $rateeDept = $formData['ratee']['dept'] ?? ($ownerInfo['dept'] ?? '—');
        $rateePeriod = $formData['ratee']['period'] ?? ($ownerInfo['period'] ?? '—');

        $commitmentText = "I, " . ($rateeName ?: '[Name]') . ", " . ($rateePos ?: '[Position]') . " of the " . ($rateeDept ?: '[College / Unit]') . ", commit to deliver and agree to be rated on the attainment of targets for " . ($rateePeriod ?: '[Rating Period]') . " in accordance with the indicated measures.";

        $sheet->mergeCells('A6:H7');
        $sheet->setCellValue('A6', $commitmentText);
        $sheet->getStyle('A6')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFont()->setItalic(true)->setSize(9.5);
        $sheet->getStyle('A6:H7')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A6:H7')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // ---------------------------------------------------------------------
        // 5. TOP APPROVAL, RATEE SIGN-OFF & CSC RATING SCALE GUIDE (Rows 9 to 15)
        // ---------------------------------------------------------------------
        $approverName = $formData['approver']['name'] ?? (trim(($superiorInfo['first_name'] ?? '') . ' ' . ($superiorInfo['last_name'] ?? '')) ?: '—');
        $approverPos  = $formData['approver']['position'] ?? ($superiorInfo['position'] ?? $approverRoleLabel);
        $approverDate = $formData['approver']['date'] ?? '';

        $rateeSignName = $formData['rateeSign']['name'] ?? $rateeName;
        $rateeSignDate = $formData['rateeSign']['date'] ?? '';

        // Header Labels
        $sheet->mergeCells('A9:C9');
        $sheet->setCellValue('A9', 'APPROVED BY:');
        $sheet->mergeCells('D9:F9');
        $sheet->setCellValue('D9', 'RATEE SIGN-OFF:');
        $sheet->mergeCells('G9:H9');
        $sheet->setCellValue('G9', 'CSC / BSU RATING SCALE:');

        $sheet->getStyle('A9:H9')->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle('A9:H9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');

        // Content
        $sheet->mergeCells('A10:C10');
        $sheet->setCellValue('A10', 'Name: ' . $approverName);
        $sheet->mergeCells('A11:C11');
        $sheet->setCellValue('A11', 'Position: ' . $approverPos);
        $sheet->mergeCells('A12:C12');
        $sheet->setCellValue('A12', 'Date: ' . ($approverDate ?: '_____________'));
        $sheet->mergeCells('A13:C14');
        $sheet->setCellValue('A13', 'Signature: __________________________');
        $sheet->getStyle('A13')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('D10:F10');
        $sheet->setCellValue('D10', 'Name: ' . $rateeSignName);
        $sheet->mergeCells('D11:F11');
        $sheet->setCellValue('D11', 'Position: ' . $rateePos);
        $sheet->mergeCells('D12:F12');
        $sheet->setCellValue('D12', 'Date: ' . ($rateeSignDate ?: '_____________'));
        $sheet->mergeCells('D13:F14');
        $sheet->setCellValue('D13', 'Signature: __________________________');
        $sheet->getStyle('D13')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // CSC Rating Scale Box
        $sheet->mergeCells('G10:H10'); $sheet->setCellValue('G10', '5 — Outstanding (4.500 – 5.000)');
        $sheet->mergeCells('G11:H11'); $sheet->setCellValue('G11', '4 — Very Satisfactory (3.500 – 4.499)');
        $sheet->mergeCells('G12:H12'); $sheet->setCellValue('G12', '3 — Satisfactory (2.500 – 3.499)');
        $sheet->mergeCells('G13:H13'); $sheet->setCellValue('G13', '2 — Unsatisfactory (1.500 – 2.499)');
        $sheet->mergeCells('G14:H14'); $sheet->setCellValue('G14', '1 / 0 — Poor / Unmet (< 1.499)');

        $sheet->getStyle('A9:C14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D9:F14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('G9:H14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('G10:H14')->getFont()->setSize(8);

        // ---------------------------------------------------------------------
        // 6. DELIVERABLES MATRIX HEADERS (Rows 16 to 17)
        // ---------------------------------------------------------------------
        $sheet->mergeCells('A16:A17');
        $sheet->setCellValue('A16', "MAJOR FINAL OUTPUT\n(MFO / PAPs)");

        $sheet->mergeCells('B16:B17');
        $sheet->setCellValue('B16', "SUCCESS INDICATORS\n(Targets + Measures)");

        $sheet->mergeCells('C16:C17');
        $sheet->setCellValue('C16', "ACTUAL ACCOMPLISHMENTS\n(Outputs Delivered)");

        $sheet->mergeCells('D16:G16');
        $sheet->setCellValue('D16', "RATING");

        $sheet->setCellValue('D17', 'Q');
        $sheet->setCellValue('E17', 'E');
        $sheet->setCellValue('F17', 'T');
        $sheet->setCellValue('G17', 'Ave.');

        $sheet->mergeCells('H16:H17');
        $sheet->setCellValue('H16', "REMARKS");

        $sheet->getStyle('A16:H17')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A16:H17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A16:H17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle('A16:H17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Repeat Header row on every printed page
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(16, 17);

        // ---------------------------------------------------------------------
        // 7. POPULATE CATEGORIES & ACCOMPLISHMENT ROWS (Rows 18+)
        // ---------------------------------------------------------------------
        $currentRow = 18;
        $categoriesData = $formData['categories'] ?? [];

        $categoryConfigs = [
            'core' => [
                'label'   => $coreLabel,
                'weight'  => $coreW,
                'bgColor' => 'DCFCE7', // Emerald light
                'txtColor'=> '166534',
                'rows'    => $categoriesData['core'] ?? []
            ],
            'strategic' => [
                'label'   => $stratLabel,
                'weight'  => $stratW,
                'bgColor' => 'E0F2FE', // Sky light
                'txtColor'=> '075985',
                'rows'    => $categoriesData['strategic'] ?? []
            ],
            'support' => [
                'label'   => $suppLabel,
                'weight'  => $suppW,
                'bgColor' => 'FEF3C7', // Amber light
                'txtColor'=> '92400E',
                'rows'    => $categoriesData['support'] ?? []
            ]
        ];

        $categoryRowRanges = [];

        foreach ($categoryConfigs as $catKey => $config) {
            // Category Section Header Band
            $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", $config['label']);
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(9.5);

            $sheet->mergeCells("G{$currentRow}:H{$currentRow}");
            $subtotalCellCoord = "G{$currentRow}";
            $sheet->setCellValue($subtotalCellCoord, "Subtotal: 0.000");
            $sheet->getStyle($subtotalCellCoord)->getFont()->setBold(true)->setSize(9.5);
            $sheet->getStyle($subtotalCellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($config['bgColor']);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);

            $headerRowIdx = $currentRow;
            $currentRow++;

            $startRowIdx = $currentRow;
            $items = !empty($config['rows']) ? $config['rows'] : [
                ['mfo' => '', 'indicators' => '', 'accomplishments' => '', 'q' => '', 'e' => '', 't' => '', 'remarks' => '']
            ];

            foreach ($items as $item) {
                $sheet->setCellValue("A{$currentRow}", $item['mfo'] ?? '');
                $sheet->setCellValue("B{$currentRow}", $item['indicators'] ?? '');
                $sheet->setCellValue("C{$currentRow}", $item['accomplishments'] ?? '');

                // Q, E, T scores
                $qVal = is_numeric($item['q'] ?? null) ? (int)$item['q'] : '';
                $eVal = is_numeric($item['e'] ?? null) ? (int)$item['e'] : '';
                $tVal = is_numeric($item['t'] ?? null) ? (int)$item['t'] : '';

                if ($qVal !== '') $sheet->setCellValue("D{$currentRow}", $qVal);
                if ($eVal !== '') $sheet->setCellValue("E{$currentRow}", $eVal);
                if ($tVal !== '') $sheet->setCellValue("F{$currentRow}", $tVal);

                // Row Average Formula: =IF(COUNT(D{row}:F{row})>0, AVERAGE(D{row}:F{row}), "")
                $sheet->setCellValue("G{$currentRow}", "=IF(COUNT(D{$currentRow}:F{$currentRow})>0, AVERAGE(D{$currentRow}:F{$currentRow}), \"\")");

                $sheet->setCellValue("H{$currentRow}", $item['remarks'] ?? '');

                // Alignments & Styles
                $sheet->getStyle("A{$currentRow}:C{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("D{$currentRow}:G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("H{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getStyle("G{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
                $sheet->getStyle("G{$currentRow}")->getFont()->setBold(true)->getColor()->setRGB('0369A1');
                $sheet->getStyle("G{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F9FF');

                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $currentRow++;
            }

            $endRowIdx = $currentRow - 1;
            $categoryRowRanges[$catKey] = [
                'headerRow' => $headerRowIdx,
                'start'     => $startRowIdx,
                'end'       => $endRowIdx,
                'weight'    => $config['weight']
            ];

            // Set Subtotal Formula on Header Row: =IFERROR(AVERAGE(G{start}:G{end}) * weight, 0)
            $sheet->setCellValue("G{$headerRowIdx}", "=IFERROR(AVERAGE(G{$startRowIdx}:G{$endRowIdx}) * {$config['weight']}, 0)");
            $sheet->getStyle("G{$headerRowIdx}")->getNumberFormat()->setFormatCode('"Subtotal: "0.000');
        }

        // ---------------------------------------------------------------------
        // 8. GRAND SUMMARY & CALCULATION MATRIX
        // ---------------------------------------------------------------------
        $summaryStartRow = $currentRow;

        // Formula Explanation Box (Cols A to C)
        $sheet->mergeCells("A{$summaryStartRow}:C" . ($summaryStartRow + 2));
        $weightText = "Formula Weights:\nCore Function (" . ($coreW * 100) . "%) + Strategic Function (" . ($stratW * 100) . "%) + Support Functions (" . ($suppW * 100) . "%).\nValidated against standard Civil Service Commission SPMS Guidelines.";
        $sheet->setCellValue("A{$summaryStartRow}", $weightText);
        $sheet->getStyle("A{$summaryStartRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$summaryStartRow}")->getFont()->setSize(8.5)->setItalic(true);
        $sheet->getStyle("A{$summaryStartRow}:C" . ($summaryStartRow + 2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $sheet->getStyle("A{$summaryStartRow}:C" . ($summaryStartRow + 2))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // Category Breakdown Rows (Cols D to H)
        $cats = ['core' => 'CORE FUNCTION', 'strategic' => 'STRATEGIC FUNCTION', 'support' => 'SUPPORT FUNCTIONS'];
        $idx = 0;
        $subtotalRowRefs = [];

        foreach ($cats as $k => $cName) {
            $r = $summaryStartRow + $idx;
            $range = $categoryRowRanges[$k];

            $sheet->mergeCells("D{$r}:E{$r}");
            $sheet->setCellValue("D{$r}", $cName);
            $sheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(9);

            $sheet->setCellValue("F{$r}", "× " . number_format($range['weight'], 2));
            $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Ave score: =IFERROR(AVERAGE(G{start}:G{end}), 0)
            $sheet->setCellValue("G{$r}", "=IFERROR(AVERAGE(G{$range['start']}:G{$range['end']}), 0)");
            $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Weighted Subtotal: =G{r} * weight
            $sheet->setCellValue("H{$r}", "=G{$r} * {$range['weight']}");
            $sheet->getStyle("H{$r}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("H{$r}")->getFont()->setBold(true);
            $sheet->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $subtotalRowRefs[] = "H{$r}";
            $sheet->getStyle("D{$r}:H{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $idx++;
        }

        $currentRow = $summaryStartRow + 3;

        // Dark Navy Final Average Rating Bar
        $sheet->mergeCells("A{$currentRow}:D{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "FINAL AVERAGE RATING");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Grand Total Formula: =SUM(H{r1}, H{r2}, H{r3})
        $sheet->mergeCells("E{$currentRow}:G{$currentRow}");
        $sheet->setCellValue("E{$currentRow}", "=SUM(" . implode(',', $subtotalRowRefs) . ")");
        $sheet->getStyle("E{$currentRow}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('38BDF8'); // Cyan bold
        $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("E{$currentRow}")->getNumberFormat()->setFormatCode('0.000');

        // Adjectival Rating Formula
        $sheet->setCellValue("H{$currentRow}", "=IF(E{$currentRow}>=4.5,\"OUTSTANDING\",IF(E{$currentRow}>=3.5,\"VERY SATISFACTORY\",IF(E{$currentRow}>=2.5,\"SATISFACTORY\",IF(E{$currentRow}>=1.5,\"UNSATISFACTORY\",\"POOR\"))))");
        $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0A192F');
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension($currentRow)->setRowHeight(28);

        $currentRow++;

        // ---------------------------------------------------------------------
        // 9. PMT & DEAN REMARKS BOX
        // ---------------------------------------------------------------------
        $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "Performance Management Team (PMT) & Dean Remarks / Recommendations:");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("A{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

        $currentRow++;
        $sheet->mergeCells("A{$currentRow}:H" . ($currentRow + 1));
        $pmtRemarks = $formData['pmtRemarks'] ?? '';
        $sheet->setCellValue("A{$currentRow}", $pmtRemarks ?: '(No additional remarks recorded.)');
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('334155');
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A" . ($currentRow - 1) . ":H" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $currentRow += 2;

        // ---------------------------------------------------------------------
        // 10. TRIPARTITE INSTITUTIONAL SIGNATORIES BLOCK
        // ---------------------------------------------------------------------
        $sigRatee = $formData['signatories']['ratee'] ?? $rateeName;
        $sigRateeDate = $formData['signatories']['rateeDate'] ?? $rateeSignDate;

        $sigDean = $formData['signatories']['dean'] ?? $approverName;
        $sigDeanDate = $formData['signatories']['deanDate'] ?? $approverDate;

        $sigVp = $formData['signatories']['vp'] ?? '';
        $sigVpDate = $formData['signatories']['vpDate'] ?? '';

        // Headers
        $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "Discussed with ({$rateeRoleLabel}):");

        $sheet->mergeCells("D{$currentRow}:F{$currentRow}");
        $sheet->setCellValue("D{$currentRow}", "Assessed by (Supervisor / Dean):");

        $sheet->mergeCells("G{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("G{$currentRow}", "Final Approval (PMT / VP / President):");

        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('475569');
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');

        $currentRow++;
        $sigRowStart = $currentRow;

        // Signature Lines
        $sheet->mergeCells("A{$currentRow}:C" . ($currentRow + 1));
        $sheet->setCellValue("A{$currentRow}", "\n\n____________________________________\n" . ($sigRatee ?: 'Ratee Signature') . "\n{$rateeRoleLabel}\nDate: " . ($sigRateeDate ?: '_____________'));

        $sheet->mergeCells("D{$currentRow}:F" . ($currentRow + 1));
        $sheet->setCellValue("D{$currentRow}", "\n\n____________________________________\n" . ($sigDean ?: 'Supervisor Signature') . "\nCollege Dean / Immediate Supervisor\nDate: " . ($sigDeanDate ?: '_____________'));

        $sheet->mergeCells("G{$currentRow}:H" . ($currentRow + 1));
        $sheet->setCellValue("G{$currentRow}", "\n\n____________________________________\n" . ($sigVp ?: 'Approving Authority Signature') . "\nVice President / PMT Chair / President\nDate: " . ($sigVpDate ?: '_____________'));

        $sheet->getStyle("A{$sigRowStart}:H" . ($currentRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle("A{$sigRowStart}:H" . ($currentRow + 1))->getFont()->setSize(8.5);
        $sheet->getStyle("A" . ($sigRowStart - 1) . ":C" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("D" . ($sigRowStart - 1) . ":F" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("G" . ($sigRowStart - 1) . ":H" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getRowDimension($sigRowStart)->setRowHeight(40);
        $sheet->getRowDimension($sigRowStart + 1)->setRowHeight(40);

        // ---------------------------------------------------------------------
        // 11. STREAM OUTPUT (.xlsx Download)
        // ---------------------------------------------------------------------
        $sanitizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rateeName ?: 'Employee');
        $sanitizedDocType = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docTypeUpper);
        $fileName = "{$sanitizedDocType}_{$sanitizedName}_" . date('Ymd') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
