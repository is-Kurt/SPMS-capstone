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
        $isIperf = (!$isOpcr && !$isDpcr) && (str_contains($titleRaw, 'IPERF') || str_contains($titleRaw, 'CONTRACT OF SERVICE') || str_contains($titleRaw, 'JOB ORDER') || $docTypeUpper === 'IPERF');

        if ($isIperf) {
            self::exportIperf($spreadsheet, $formData ?? [], $doc, $ownerInfo, $superiorInfo);
            $rateeName = $formData['ratee']['name'] ?? ($ownerInfo['name'] ?? 'Employee');
            $sanitizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rateeName ?: 'Employee');
            $sanitizedDocType = 'IPERF';
            $fileName = "{$sanitizedDocType}_{$sanitizedName}_" . date('Ymd') . ".xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        if ($isOpcr) {
            $sheetTitle = 'OPCR - Office';
            $formFullTitle = 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)';
            $coreW = $formData['weights']['core'] ?? 0.60;
            $stratW = $formData['weights']['strategic'] ?? 0.25;
            $suppW = $formData['weights']['support'] ?? 0.15;
            $coreLabel = 'CORE MANDATE (' . round($coreW * 100) . '%)';
            $stratLabel = 'STRATEGIC FUNCTIONS (' . round($stratW * 100) . '%)';
            $suppLabel = 'SUPPORT FUNCTIONS (' . round($suppW * 100) . '%)';
            $rateeRoleLabel = 'Vice President / Head of Office';
            $approverRoleLabel = 'University President / PMT Chair';
        } elseif ($isDpcr) {
            $sheetTitle = 'DPCR - Division';
            $formFullTitle = 'DIVISION / DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)';
            $coreW = $formData['weights']['core'] ?? 0.60;
            $stratW = $formData['weights']['strategic'] ?? 0.30;
            $suppW = $formData['weights']['support'] ?? 0.10;
            $coreLabel = '1. Core Functions — Department Academic Operations (' . round($coreW * 100) . '%)';
            $stratLabel = '2. Strategic Functions — Department Extension & Research (' . round($stratW * 100) . '%)';
            $suppLabel = '3. Support Functions — Academic Support & Administration (' . round($suppW * 100) . '%)';
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
            // Default: IPCR
            $sheetTitle = 'IPCR';
            $formFullTitle = 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW';
            $coreW = 0.70; $stratW = 0.20; $suppW = 0.10;
            $coreLabel = 'CORE FUNCTIONS (70%)';
            $stratLabel = 'STRATEGIC FUNCTIONS (20%)';
            $suppLabel = 'SUPPORT FUNCTIONS (10%)';
            $rateeRoleLabel = 'Ratee';
            $approverRoleLabel = 'College Dean / Unit Head';
        }

        $sheet->setTitle(substr($sheetTitle, 0, 31));

        $lastCol = $isOpcr ? 'J' : 'H';

        // ---------------------------------------------------------------------
        // 2. COLUMN WIDTHS
        // ---------------------------------------------------------------------
        if ($isOpcr) {
            // 10-Column Standard for VP / OPCR
            $sheet->getColumnDimension('A')->setWidth(26); // PROJECT/ PROGRAM/ ACTIVITIES
            $sheet->getColumnDimension('B')->setWidth(28); // SUCCESS INDICATORS (TARGETS + MEASURES) PERFORMANCE
            $sheet->getColumnDimension('C')->setWidth(16); // ALLOTTED BUDGET
            $sheet->getColumnDimension('D')->setWidth(20); // DIVISIONS ACCOUNTABLE
            $sheet->getColumnDimension('E')->setWidth(26); // ACTUAL ACCOMPLISHMENT
            $sheet->getColumnDimension('F')->setWidth(6.5); // Q
            $sheet->getColumnDimension('G')->setWidth(6.5); // T
            $sheet->getColumnDimension('H')->setWidth(6.5); // E
            $sheet->getColumnDimension('I')->setWidth(9.5); // AVE
            $sheet->getColumnDimension('J')->setWidth(20); // REMARKS
        } else {
            // Standardized 8-Column Layout A to H
            $sheet->getColumnDimension('A')->setWidth(26); // MFO / PAPs
            $sheet->getColumnDimension('B')->setWidth(26); // Success Indicators
            $sheet->getColumnDimension('C')->setWidth(26); // Actual Accomplishments
            $sheet->getColumnDimension('D')->setWidth(6.5); // Q
            $sheet->getColumnDimension('E')->setWidth(6.5); // E
            $sheet->getColumnDimension('F')->setWidth(6.5); // T
            $sheet->getColumnDimension('G')->setWidth(9.5); // Ave.
            $sheet->getColumnDimension('H')->setWidth(18); // Remarks
        }

        // Base Font: Arial 10
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(9.5);

        // ---------------------------------------------------------------------
        // 3. INSTITUTIONAL CSC & BSU HEADER (Rows 1 to 4)
        // ---------------------------------------------------------------------
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->getStyle('A1')->getFont()->setSize(10)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'BENGUET STATE UNIVERSITY');
        $sheet->getStyle('A2')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'La Trinidad, Benguet • Strategic Performance Management System');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A4:{$lastCol}4");
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

        $sheet->mergeCells("A6:{$lastCol}7");
        $sheet->setCellValue('A6', $commitmentText);
        $sheet->getStyle('A6')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFont()->setItalic(true)->setSize(9.5);
        $sheet->getStyle("A6:{$lastCol}7")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A6:{$lastCol}7")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // ---------------------------------------------------------------------
        // 5. TOP APPROVAL, RATEE SIGN-OFF & CSC RATING SCALE GUIDE (Rows 9 to 15)
        // ---------------------------------------------------------------------
        if (!empty($formData['approvers']) && is_array($formData['approvers'])) {
            $approverNames = array_filter(array_map(fn($a) => trim($a['name'] ?? ''), $formData['approvers']));
            $approverPoses = array_filter(array_map(fn($a) => trim($a['position'] ?? ''), $formData['approvers']));
            $approverDates = array_filter(array_map(fn($a) => trim($a['date'] ?? ''), $formData['approvers']));
            $approverName = !empty($approverNames) ? implode(' / ', $approverNames) : ($formData['approver']['name'] ?? '—');
            $approverPos  = !empty($approverPoses) ? implode(' / ', $approverPoses) : ($formData['approver']['position'] ?? $approverRoleLabel);
            $approverDate = !empty($approverDates) ? implode(' / ', $approverDates) : ($formData['approver']['date'] ?? '');
        } else {
            $approverName = $formData['approver']['name'] ?? (trim(($superiorInfo['first_name'] ?? '') . ' ' . ($superiorInfo['last_name'] ?? '')) ?: '—');
            $approverPos  = $formData['approver']['position'] ?? ($superiorInfo['position'] ?? $approverRoleLabel);
            $approverDate = $formData['approver']['date'] ?? '';
        }

        $rateeSignName = $formData['rateeSign']['name'] ?? $rateeName;
        $rateeSignDate = $formData['rateeSign']['date'] ?? '';

        if ($isOpcr) {
            // Header Labels for OPCR
            $sheet->mergeCells('A9:D9');
            $sheet->setCellValue('A9', 'APPROVED BY:');
            $sheet->mergeCells('E9:G9');
            $sheet->setCellValue('E9', 'RATEE SIGN-OFF:');
            $sheet->mergeCells('H9:J9');
            $sheet->setCellValue('H9', 'CSC / BSU RATING SCALE:');

            $sheet->getStyle('A9:J9')->getFont()->setBold(true)->setSize(8.5);
            $sheet->getStyle('A9:J9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');

            // Content
            $sheet->mergeCells('A10:D10');
            $sheet->setCellValue('A10', 'Name: ' . $approverName);
            $sheet->mergeCells('A11:D11');
            $sheet->setCellValue('A11', 'Position: ' . $approverPos);
            $sheet->mergeCells('A12:D12');
            $sheet->setCellValue('A12', 'Date: ' . ($approverDate ?: '_____________'));
            $sheet->mergeCells('A13:D14');
            $sheet->setCellValue('A13', 'Signature: __________________________');
            $sheet->getStyle('A13')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->mergeCells('E10:G10');
            $sheet->setCellValue('E10', 'Name: ' . $rateeSignName);
            $sheet->mergeCells('E11:G11');
            $sheet->setCellValue('E11', 'Position: ' . $rateePos);
            $sheet->mergeCells('E12:G12');
            $sheet->setCellValue('E12', 'Date: ' . ($rateeSignDate ?: '_____________'));
            $sheet->mergeCells('E13:G14');
            $sheet->setCellValue('E13', 'Signature: __________________________');
            $sheet->getStyle('E13')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // CSC Rating Scale Box
            $sheet->mergeCells('H10:J10'); $sheet->setCellValue('H10', '5 — Outstanding (4.500 – 5.000)');
            $sheet->mergeCells('H11:J11'); $sheet->setCellValue('H11', '4 — Very Satisfactory (3.500 – 4.499)');
            $sheet->mergeCells('H12:J12'); $sheet->setCellValue('H12', '3 — Satisfactory (2.500 – 3.499)');
            $sheet->mergeCells('H13:J13'); $sheet->setCellValue('H13', '2 — Unsatisfactory (1.500 – 2.499)');
            $sheet->mergeCells('H14:J14'); $sheet->setCellValue('H14', '1 / 0 — Poor / Unmet (< 1.499)');

            $sheet->getStyle('A9:D14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E9:G14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H9:J14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H10:J14')->getFont()->setSize(8);
        } elseif (!$isDpcr) {
            // Rows 5-7: Employee Name Block on Right (Cols D to H)
            $sheet->mergeCells('D5:H5');
            $sheet->setCellValue('D5', $rateeSignName ?: '(full name here)');
            $sheet->getStyle('D5')->getFont()->setBold(true)->getColor()->setRGB('BA372A');
            $sheet->mergeCells('D6:H6');
            $sheet->setCellValue('D6', 'Name of Employee');
            $sheet->mergeCells('D7:H7');
            $sheet->setCellValue('D7', 'Date: ' . ($rateeSignDate ?: '_____________'));

            // Rows 9-14: Approved By (Cols A to C)
            $sheet->setCellValue('A9', 'APPROVED BY:');
            $sheet->getStyle('A9')->getFont()->setBold(true)->setSize(9);
            $sheet->mergeCells('A10:C10');
            $sheet->setCellValue('A10', 'Name: ' . ($approverName ?: '(name of office head)'));
            $sheet->mergeCells('A11:C11');
            $sheet->setCellValue('A11', 'Position: ' . ($approverPos ?: '(position of office head)'));
            $sheet->mergeCells('A12:C12');
            $sheet->setCellValue('A12', 'Date: ' . ($approverDate ?: '_____________'));

            // Rating Scale (Cols D to H)
            $sheet->mergeCells('D9:H9');
            $sheet->setCellValue('D9', 'Rating Scale:');
            $sheet->getStyle('D9')->getFont()->setBold(true)->setSize(8.5);
            $sheet->mergeCells('D10:H10'); $sheet->setCellValue('D10', '5 — Outstanding');
            $sheet->mergeCells('D11:H11'); $sheet->setCellValue('D11', '4 — Very Satisfactory');
            $sheet->mergeCells('D12:H12'); $sheet->setCellValue('D12', '3 — Satisfactory');
            $sheet->mergeCells('D13:H13'); $sheet->setCellValue('D13', '2 — Unsatisfactory');
            $sheet->mergeCells('D14:H14'); $sheet->setCellValue('D14', '1 — Poor');
            $sheet->getStyle('D10:H14')->getFont()->setSize(8);
        } else {
            // Header Labels for DPCR
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
        }

        // ---------------------------------------------------------------------
        // 6. DELIVERABLES MATRIX HEADERS (Rows 16 to 17)
        // ---------------------------------------------------------------------
        if ($isOpcr) {
            $sheet->mergeCells('A16:A17');
            $sheet->setCellValue('A16', "PROJECT/ PROGRAM/\nACTIVITIES");

            $sheet->mergeCells('B16:B17');
            $sheet->setCellValue('B16', "SUCCESS INDICATORS\n(TARGETS + MEASURES)\nPERFORMANCE");

            $docCurrency = trim((string)($formData['budget_currency'] ?? ($formData['currency'] ?? '₱')));
            $sheet->mergeCells('C16:C17');
            $sheet->setCellValue('C16', "ALLOTTED\nBUDGET" . ($docCurrency !== '' ? "\n({$docCurrency})" : ''));

            $sheet->mergeCells('D16:D17');
            $sheet->setCellValue('D16', "DIVISIONS\nACCOUNTABLE");

            $sheet->mergeCells('E16:E17');
            $sheet->setCellValue('E16', "ACTUAL\nACCOMPLISHMENT");

            $sheet->mergeCells('F16:I16');
            $sheet->setCellValue('F16', "RATINGS");

            $sheet->setCellValue('F17', 'Q');
            $sheet->setCellValue('G17', 'T');
            $sheet->setCellValue('H17', 'E');
            $sheet->setCellValue('I17', 'AVE');

            $sheet->mergeCells('J16:J17');
            $sheet->setCellValue('J16', "REMARKS");

            $sheet->getStyle('A16:J17')->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle('A16:J17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle('A16:J17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
            $sheet->getStyle('A16:J17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        } else {
            $sheet->mergeCells('A16:A17');
            $sheet->setCellValue('A16', "MAJOR FINAL OUTPUT\n(MFO / PAPs)");

            $sheet->mergeCells('B16:B17');
            $sheet->setCellValue('B16', "SUCCESS INDICATORS\n(Targets + Measures)");

            $sheet->mergeCells('C16:C17');
            $sheet->setCellValue('C16', "ACTUAL ACCOMPLISHMENTS\n(Outputs Delivered)");

            $sheet->mergeCells('D16:G16');
            $sheet->setCellValue('D16', "RATING");

            $sheet->setCellValue('D17', 'Q');
            $sheet->setCellValue('E17', 'T');
            $sheet->setCellValue('F17', 'E');
            $sheet->setCellValue('G17', 'Ave.');

            $sheet->mergeCells('H16:H17');
            $sheet->setCellValue('H16', "REMARKS");

            $sheet->getStyle('A16:H17')->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle('A16:H17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle('A16:H17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');
            $sheet->getStyle('A16:H17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

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
                'bgColor' => $isOpcr ? 'FFF2CC' : 'DCFCE7', // Emerald light
                'txtColor'=> $isOpcr ? '000000' : '166534',
                'rows'    => $categoriesData['core'] ?? []
            ],
            'strategic' => [
                'label'   => $stratLabel,
                'weight'  => $stratW,
                'bgColor' => $isOpcr ? 'FFF2CC' : 'E0F2FE', // Sky light
                'txtColor'=> $isOpcr ? '000000' : '075985',
                'rows'    => $categoriesData['strategic'] ?? []
            ],
            'support' => [
                'label'   => $suppLabel,
                'weight'  => $suppW,
                'bgColor' => 'FFF2CC', // Amber light
                'txtColor'=> '000000',
                'rows'    => $categoriesData['support'] ?? []
            ]
        ];

        $categoryRowRanges = [];

        foreach ($categoryConfigs as $catKey => $config) {
            // Category Section Header Band
            if ($isOpcr) {
                $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", $config['label']);
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(9.5);

                $sheet->mergeCells("I{$currentRow}:J{$currentRow}");
                $subtotalCellCoord = "I{$currentRow}";
                $sheet->setCellValue($subtotalCellCoord, "Subtotal: 0.000");
                $sheet->getStyle($subtotalCellCoord)->getFont()->setBold(true)->setSize(9.5);
                $sheet->getStyle($subtotalCellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
                $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getRowDimension($currentRow)->setRowHeight(22);
            } elseif (!$isDpcr) {
                $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", $config['label']);
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');
                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getRowDimension($currentRow)->setRowHeight(22);
            } else {
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
            }

            $headerRowIdx = $currentRow;
            $currentRow++;

            $startRowIdx = $currentRow;
            $items = !empty($config['rows']) ? $config['rows'] : [
                ['mfo' => '', 'indicators' => '', 'budget' => '', 'accountable' => '', 'accomplishments' => '', 'q' => '', 'e' => '', 't' => '', 'remarks' => '']
            ];

            foreach ($items as $item) {
                if ($isOpcr) {
                    $sheet->setCellValue("A{$currentRow}", $item['mfo'] ?? '');
                    $sheet->setCellValue("B{$currentRow}", $item['indicators'] ?? '');
                    $budgetVal = trim((string)($item['budget'] ?? ''));
                    if ($budgetVal !== '') {
                        $curr = trim((string)($item['budget_currency'] ?? ($formData['budget_currency'] ?? ($formData['currency'] ?? '₱'))));
                        if (!str_starts_with($budgetVal, $curr)) {
                            $budgetVal = $curr . ' ' . $budgetVal;
                        }
                    }
                    $sheet->setCellValue("C{$currentRow}", $budgetVal);
                    $sheet->setCellValue("D{$currentRow}", $item['accountable'] ?? '');
                    $sheet->setCellValue("E{$currentRow}", $item['accomplishments'] ?? '');

                    $qVal = is_numeric($item['q'] ?? null) ? (int)$item['q'] : '';
                    $tVal = is_numeric($item['t'] ?? null) ? (int)$item['t'] : '';
                    $eVal = is_numeric($item['e'] ?? null) ? (int)$item['e'] : '';

                    if ($qVal !== '') $sheet->setCellValue("F{$currentRow}", $qVal);
                    if ($tVal !== '') $sheet->setCellValue("G{$currentRow}", $tVal);
                    if ($eVal !== '') $sheet->setCellValue("H{$currentRow}", $eVal);

                    // Row Average Formula: =IF(COUNT(F{row}:H{row})>0, AVERAGE(F{row}:H{row}), "")
                    $sheet->setCellValue("I{$currentRow}", "=IF(COUNT(F{$currentRow}:H{$currentRow})>0, AVERAGE(F{$currentRow}:H{$currentRow}), \"\")");

                    $sheet->setCellValue("J{$currentRow}", $item['remarks'] ?? '');

                    // Alignments & Styles
                    $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("F{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("J{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

                    $sheet->getStyle("F{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFE599');

                    $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
                    $sheet->getStyle("I{$currentRow}")->getFont()->setBold(true)->getColor()->setRGB('0369A1');
                    $sheet->getStyle("I{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F9FF');

                    $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                } else {
                    $sheet->setCellValue("A{$currentRow}", $item['mfo'] ?? '');
                    $sheet->setCellValue("B{$currentRow}", $item['indicators'] ?? '');
                    $sheet->setCellValue("C{$currentRow}", $item['accomplishments'] ?? '');

                    // Q, E, T scores (In IPCR column order: D=Q, E=T, F=E)
                    $qVal = is_numeric($item['q'] ?? null) ? (int)$item['q'] : '';
                    $tVal = is_numeric($item['t'] ?? null) ? (int)$item['t'] : '';
                    $eVal = is_numeric($item['e'] ?? null) ? (int)$item['e'] : '';

                    if ($qVal !== '') $sheet->setCellValue("D{$currentRow}", $qVal);
                    if ($tVal !== '') $sheet->setCellValue("E{$currentRow}", $tVal);
                    if ($eVal !== '') $sheet->setCellValue("F{$currentRow}", $eVal);

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
                }

                $currentRow++;
            }

            $endRowIdx = $currentRow - 1;
            $categoryRowRanges[$catKey] = [
                'headerRow' => $headerRowIdx,
                'start'     => $startRowIdx,
                'end'       => $endRowIdx,
                'weight'    => $config['weight']
            ];

            if ($isOpcr) {
                // Set Subtotal Formula on Header Row: =IFERROR(AVERAGE(I{start}:I{end}) * weight, 0)
                $sheet->setCellValue("I{$headerRowIdx}", "=IFERROR(AVERAGE(I{$startRowIdx}:I{$endRowIdx}) * {$config['weight']}, 0)");
                $sheet->getStyle("I{$headerRowIdx}")->getNumberFormat()->setFormatCode('"Subtotal: "0.000');
            } elseif ($isDpcr) {
                // Set Subtotal Formula on Header Row: =IFERROR(AVERAGE(G{start}:G{end}) * weight, 0)
                $sheet->setCellValue("G{$headerRowIdx}", "=IFERROR(AVERAGE(G{$startRowIdx}:G{$endRowIdx}) * {$config['weight']}, 0)");
                $sheet->getStyle("G{$headerRowIdx}")->getNumberFormat()->setFormatCode('"Subtotal: "0.000');
            }
        }

        // ---------------------------------------------------------------------
        // 8. GRAND SUMMARY & CALCULATION MATRIX
        // ---------------------------------------------------------------------
        if ($isOpcr) {
            $summaryStartRow = $currentRow;

            // Formula Explanation Box (Cols A to E)
            $sheet->mergeCells("A{$summaryStartRow}:E" . ($summaryStartRow + 2));
            $weightText = "Formula Weights:\nCore Mandate (" . ($coreW * 100) . "%) + Strategic Functions (" . ($stratW * 100) . "%) + Support Functions (" . ($suppW * 100) . "%).\nValidated against Benguet State University OPCR SPMS Guidelines.";
            $sheet->setCellValue("A{$summaryStartRow}", $weightText);
            $sheet->getStyle("A{$summaryStartRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("A{$summaryStartRow}")->getFont()->setSize(8.5)->setItalic(true);
            $sheet->getStyle("A{$summaryStartRow}:E" . ($summaryStartRow + 2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFDF5');
            $sheet->getStyle("A{$summaryStartRow}:E" . ($summaryStartRow + 2))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            // Category Breakdown Rows (Cols F to J)
            $cats = ['core' => 'CORE MANDATE', 'strategic' => 'STRATEGIC FUNCTIONS', 'support' => 'SUPPORT FUNCTIONS'];
            $idx = 0;
            $subtotalRowRefs = [];

            foreach ($cats as $k => $cName) {
                $r = $summaryStartRow + $idx;
                $range = $categoryRowRanges[$k];

                $sheet->mergeCells("F{$r}:G{$r}");
                $sheet->setCellValue("F{$r}", $cName);
                $sheet->getStyle("F{$r}")->getFont()->setBold(true)->setSize(9);

                $sheet->setCellValue("H{$r}", "× " . number_format($range['weight'], 2));
                $sheet->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Ave score: =IFERROR(AVERAGE(I{start}:I{end}), 0)
                $sheet->setCellValue("I{$r}", "=IFERROR(AVERAGE(I{$range['start']}:I{$range['end']}), 0)");
                $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('0.000');
                $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Weighted Subtotal: =I{r} * weight
                $sheet->setCellValue("J{$r}", "=I{$r} * {$range['weight']}");
                $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('0.000');
                $sheet->getStyle("J{$r}")->getFont()->setBold(true);
                $sheet->getStyle("J{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $subtotalRowRefs[] = "J{$r}";
                $sheet->getStyle("F{$r}:J{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $idx++;
            }

            $currentRow = $summaryStartRow + 3;

            // Dark Navy Final Average Rating Bar
            $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "FINAL AVERAGE RATING");
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Grand Total Formula: =SUM(J{r1}, J{r2}, J{r3})
            $sheet->mergeCells("F{$currentRow}:I{$currentRow}");
            $sheet->setCellValue("F{$currentRow}", "=SUM(" . implode(',', $subtotalRowRefs) . ")");
            $sheet->getStyle("F{$currentRow}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('38BDF8'); // Cyan bold
            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode('0.000');

            // Adjectival Rating Formula
            $sheet->setCellValue("J{$currentRow}", "=IF(F{$currentRow}>=4.5,\"OUTSTANDING\",IF(F{$currentRow}>=3.5,\"VERY SATISFACTORY\",IF(F{$currentRow}>=2.5,\"SATISFACTORY\",IF(F{$currentRow}>=1.5,\"UNSATISFACTORY\",\"POOR\"))))");
            $sheet->getStyle("J{$currentRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0A192F');
            $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getRowDimension($currentRow)->setRowHeight(28);

            $currentRow++;
        } elseif (!$isDpcr) {
            $coreRef = $categoryRowRanges['core'];
            $stratRef = $categoryRowRanges['strategic'];
            $suppRef = $categoryRowRanges['support'];

            // Row 24: CORE
            $sheet->mergeCells("D{$currentRow}:G{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "CORE");
            $sheet->getStyle("D{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("H{$currentRow}", "=IFERROR(AVERAGE(G{$coreRef['start']}:G{$coreRef['end']}) * {$coreRef['weight']}, 0)");
            $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $coreRowIdx = $currentRow;
            $currentRow++;

            // Row 25: STRATEGIC
            $sheet->mergeCells("D{$currentRow}:G{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "STRATEGIC");
            $sheet->getStyle("D{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("H{$currentRow}", "=IFERROR(AVERAGE(G{$stratRef['start']}:G{$stratRef['end']}) * {$stratRef['weight']}, 0)");
            $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $stratRowIdx = $currentRow;
            $currentRow++;

            // Row 26: SUPPORT
            $sheet->mergeCells("D{$currentRow}:G{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "SUPPORT");
            $sheet->getStyle("D{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("H{$currentRow}", "=IFERROR(AVERAGE(G{$suppRef['start']}:G{$suppRef['end']}) * {$suppRef['weight']}, 0)");
            $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $suppRowIdx = $currentRow;
            $currentRow++;

            // Row 27: FINAL AVERAGE RATING
            $sheet->mergeCells("D{$currentRow}:G{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "FINAL AVERAGE RATING");
            $sheet->getStyle("D{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("H{$currentRow}", "=SUM(H{$coreRowIdx}:H{$suppRowIdx})");
            $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $currentRow++;

            // Row 28: Other Accomplishments
            $sheet->setCellValue("A{$currentRow}", "Other Accomplishments");
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $currentRow++;
        } else {
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
        }

        // ---------------------------------------------------------------------
        // 9. PMT & DEAN REMARKS BOX
        // ---------------------------------------------------------------------
        $remarksTitle = ($isDpcr || (!$isOpcr && !$isIperf)) 
            ? "Remarks/Suggestions/Recommendations on Ratee's Performance:"
            : "Performance Management Team (PMT) & Dean Remarks / Recommendations:";

        $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", $remarksTitle);
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("A{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

        $currentRow++;
        $sheet->mergeCells("A{$currentRow}:{$lastCol}" . ($currentRow + 1));
        $pmtRemarks = $formData['pmtRemarks'] ?? '';
        $sheet->setCellValue("A{$currentRow}", $pmtRemarks ?: '(No additional remarks recorded.)');
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('334155');
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A" . ($currentRow - 1) . ":{$lastCol}" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $currentRow += 2;

        // ---------------------------------------------------------------------
        // 10. INSTITUTIONAL SIGNATORIES BLOCK
        // ---------------------------------------------------------------------
        $sigRatee = $formData['signatories']['ratee'] ?? $rateeName;
        $sigRateeDate = $formData['signatories']['rateeDate'] ?? $rateeSignDate;

        $sigDean = $formData['signatories']['dean'] ?? $approverName;
        $sigDeanDate = $formData['signatories']['deanDate'] ?? $approverDate;

        $sigVp = $formData['signatories']['vp'] ?? '';
        $sigVpDate = $formData['signatories']['vpDate'] ?? '';

        if ($isOpcr) {
            // 3-Column Signatories for OPCR (Vice President)
            $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "Discussed with (Vice President / Ratee):");

            $sheet->mergeCells("D{$currentRow}:G{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "Assessed by (University President / PMT Chair):");

            $sheet->mergeCells("H{$currentRow}:J{$currentRow}");
            $sheet->setCellValue("H{$currentRow}", "Final Approval (University President):");

            $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('475569');
            $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');

            $currentRow++;
            $sigRowStart = $currentRow;

            $sheet->mergeCells("A{$currentRow}:C" . ($currentRow + 1));
            $sheet->setCellValue("A{$currentRow}", "\n\n____________________________________\n" . ($sigRatee ?: 'Ratee Signature') . "\nVice President\nDate: " . ($sigRateeDate ?: '_____________'));

            $sheet->mergeCells("D{$currentRow}:G" . ($currentRow + 1));
            $sheet->setCellValue("D{$currentRow}", "\n\n____________________________________\n" . ($sigDean ?: 'PMT Chair Signature') . "\nUniversity President / PMT Chair\nDate: " . ($sigDeanDate ?: '_____________'));

            $sheet->mergeCells("H{$currentRow}:J" . ($currentRow + 1));
            $sheet->setCellValue("H{$currentRow}", "\n\n____________________________________\n" . ($sigVp ?: 'Approving Authority Signature') . "\nUniversity President\nDate: " . ($sigVpDate ?: '_____________'));

            $sheet->getStyle("A{$sigRowStart}:J" . ($currentRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A{$sigRowStart}:J" . ($currentRow + 1))->getFont()->setSize(8.5);
            $sheet->getStyle("A" . ($sigRowStart - 1) . ":C" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("D" . ($sigRowStart - 1) . ":G" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("H" . ($sigRowStart - 1) . ":J" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $sheet->getRowDimension($sigRowStart)->setRowHeight(40);
            $sheet->getRowDimension($sigRowStart + 1)->setRowHeight(40);
        } elseif (!$isDpcr && !$isIperf) {
            // Institutional 2-Column Signatories for IPCR (Exact Excel Spreadsheet Layout)
            $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "Name and Signature of Ratee: " . ($sigRatee ?: '(name here)'));
            $sheet->mergeCells("D{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "Final Rating by: " . ($sigDean ?: '(name of office head)'));
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setSize(9);

            $currentRow++;
            $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "Position: " . ($rateePos ?: '(position here)'));
            $sheet->mergeCells("D{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "Position: " . ($approverPos ?: '(position of office head)'));
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setSize(9);

            $currentRow++;
            $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "Date: " . ($sigRateeDate ?: ''));
            $sheet->mergeCells("D{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("D{$currentRow}", "Date: " . ($sigDeanDate ?: ''));
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setSize(9);
        } elseif ($isDpcr) {
            // 2-Column Institutional Signatories for DPCR
            $sheet->mergeCells("A{$currentRow}:D{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "Name and Signature of Ratee:");

            $sheet->mergeCells("E{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("E{$currentRow}", "Final Rating by:");

            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('000000');
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');

            $currentRow++;
            $sigRowStart = $currentRow;

            $sheet->mergeCells("A{$currentRow}:D" . ($currentRow + 1));
            $sheet->setCellValue("A{$currentRow}", "\n\n____________________________________\n" . ($sigRatee ?: 'Ratee Signature') . "\nPosition: " . ($rateePos ?: '—') . "\nDate: " . ($sigRateeDate ?: '_____________'));

            $sheet->mergeCells("E{$currentRow}:H" . ($currentRow + 1));
            $sheet->setCellValue("E{$currentRow}", "\n\n____________________________________\n" . ($sigDean ?: 'Office Head Signature') . "\nPosition: " . ($approverPos ?: '—') . "\nDate: " . ($sigDeanDate ?: '_____________'));

            $sheet->getStyle("A{$sigRowStart}:H" . ($currentRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A{$sigRowStart}:H" . ($currentRow + 1))->getFont()->setSize(8.5);
            $sheet->getStyle("A" . ($sigRowStart - 1) . ":D" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("E" . ($sigRowStart - 1) . ":H" . ($currentRow + 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $sheet->getRowDimension($sigRowStart)->setRowHeight(40);
            $sheet->getRowDimension($sigRowStart + 1)->setRowHeight(40);
        } else {
            // 3-Column Signatories for OPCR / IPERF
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
        }

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

    /**
     * Generate spreadsheet for CSC IPERF (Individual Performance Evaluation Rating Form for COS & Job Order)
     */
    private static function exportIperf(Spreadsheet $spreadsheet, array $formData, array $doc, array $ownerInfo, ?array $superiorInfo = null): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('IPERF - COS & JO');

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setShowGridLines(true);

        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.4);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.4);

        // Columns A to H Widths (Exact match for 8-column layout)
        $sheet->getColumnDimension('A')->setWidth(28); // OFFICE PPA
        $sheet->getColumnDimension('B')->setWidth(28); // EXPECTED OUTPUTS
        $sheet->getColumnDimension('C')->setWidth(28); // ACTUAL ACCOMPLISHMENTS
        $sheet->getColumnDimension('D')->setWidth(6.5); // Q
        $sheet->getColumnDimension('E')->setWidth(6.5); // T
        $sheet->getColumnDimension('F')->setWidth(6.5); // E
        $sheet->getColumnDimension('G')->setWidth(9.5); // Ave.
        $sheet->getColumnDimension('H')->setWidth(20);  // REMARKS

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(9.5);

        // 1. Institutional Header
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->getStyle('A1')->getFont()->setSize(10)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'BENGUET STATE UNIVERSITY');
        $sheet->getStyle('A2')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'La Trinidad, Benguet');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Title & Subtitle
        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL');
        $sheet->getStyle('A4')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A5:H5');
        $sheet->setCellValue('A5', '(attach rubrics for the rating of actual accomplishments vis-à-vis expected outputs)');
        $sheet->getStyle('A5')->getFont()->setSize(8.5)->setItalic(true);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. 3-Row Metadata Matrix (Rows 6-8)
        $rateeName = $formData['ratee']['name'] ?? ($ownerInfo['name'] ?? '');
        $classification = $formData['classification'] ?? 'Contract of Service (COS)';
        $rateePos = $formData['ratee']['position'] ?? ($ownerInfo['position'] ?? '');
        $rateePeriod = $formData['ratee']['period'] ?? ($ownerInfo['period'] ?? '');
        $rateeDept = $formData['ratee']['dept'] ?? ($ownerInfo['dept'] ?? '');

        // Row 6: Name & Classification
        $sheet->setCellValue('A6', 'Name of Employee:');
        $sheet->mergeCells('B6:D6');
        $sheet->setCellValue('B6', $rateeName ?: 'indicate full name (First Name Middle Initial Last Name, Extension)');
        $sheet->setCellValue('E6', 'Classification:');
        $sheet->mergeCells('F6:H6');
        $sheet->setCellValue('F6', $classification);

        // Row 7: Position & Rating Period
        $sheet->setCellValue('A7', 'Position:');
        $sheet->mergeCells('B7:D7');
        $sheet->setCellValue('B7', $rateePos ?: 'indicate the full position title specified in the contract/job order');
        $sheet->setCellValue('E7', 'Rating Period:');
        $sheet->mergeCells('F7:H7');
        $sheet->setCellValue('F7', $rateePeriod ?: 'e.g., July - December 2024');

        // Row 8: Office
        $sheet->setCellValue('A8', 'Office:');
        $sheet->mergeCells('B8:H8');
        $sheet->setCellValue('B8', $rateeDept ?: 'indicate in full the specific area of assignment');

        // Styles for metadata matrix
        $sheet->getStyle('A6:H8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A6:A8')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('E6:E7')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A6:A8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
        $sheet->getStyle('E6:E7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
        $sheet->getStyle('B6:H8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B6')->getFont()->setBold(true)->getColor()->setRGB('BA372A');
        $sheet->getStyle('F6')->getFont()->setBold(true);
        $sheet->getStyle('B7')->getFont()->getColor()->setRGB('BA372A');
        $sheet->getStyle('F7')->getFont()->setBold(true);
        $sheet->getStyle('B8')->getFont()->getColor()->setRGB('BA372A');

        // 3. Table Headers (Rows 10-11)
        $sheet->mergeCells('A10:A11');
        $sheet->setCellValue('A10', "OFFICE PPA\n(PROGRAMS, PROJECTS, ACTIVITIES)\n(aligned with the deliverables of the office)");

        $sheet->mergeCells('B10:B11');
        $sheet->setCellValue('B10', "EXPECTED OUTPUTS\n(based on contract or duties and responsibilities in the request to hire personnel)");

        $sheet->mergeCells('C10:C11');
        $sheet->setCellValue('C10', "ACTUAL ACCOMPLISHMENTS");

        $sheet->mergeCells('D10:G10');
        $sheet->setCellValue('D10', "RATING");

        $sheet->setCellValue('D11', 'Q');
        $sheet->setCellValue('E11', 'T');
        $sheet->setCellValue('F11', 'E');
        $sheet->setCellValue('G11', 'Ave.');

        $sheet->mergeCells('H10:H11');
        $sheet->setCellValue('H10', "REMARKS");

        $sheet->getStyle('A10:H11')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A10:H11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A10:H11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle('A10:H11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(10, 11);

        // 4. Flat Deliverable Rows (Rows 12+)
        $currentRow = 12;
        $items = $formData['categories']['core'] ?? [];
        if (empty($items)) {
            $items = [
                ['mfo' => '', 'indicators' => '', 'accomplishments' => '', 'q' => '', 't' => '', 'e' => '', 'remarks' => '']
            ];
        }

        $startRowIdx = $currentRow;

        foreach ($items as $item) {
            $sheet->setCellValue("A{$currentRow}", $item['mfo'] ?? '');
            $sheet->setCellValue("B{$currentRow}", $item['indicators'] ?? '');
            $sheet->setCellValue("C{$currentRow}", $item['accomplishments'] ?? '');

            $qVal = is_numeric($item['q'] ?? null) ? (int)$item['q'] : '';
            $tVal = is_numeric($item['t'] ?? null) ? (int)$item['t'] : '';
            $eVal = is_numeric($item['e'] ?? null) ? (int)$item['e'] : '';

            if ($qVal !== '') $sheet->setCellValue("D{$currentRow}", $qVal);
            if ($tVal !== '') $sheet->setCellValue("E{$currentRow}", $tVal);
            if ($eVal !== '') $sheet->setCellValue("F{$currentRow}", $eVal);

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

        // 5. OVERALL AVERAGE RATING Footer Row
        $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "OVERALL AVERAGE RATING");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Overall Average Formula: =IFERROR(AVERAGE(G{start}:G{end}), 0)
        $sheet->setCellValue("G{$currentRow}", "=IFERROR(AVERAGE(G{$startRowIdx}:G{$endRowIdx}), 0)");
        $sheet->getStyle("G{$currentRow}")->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle("G{$currentRow}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('0284C7');
        $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Adjectival Rating Formula
        $sheet->setCellValue("H{$currentRow}", "=IF(G{$currentRow}>=4.5,\"OUTSTANDING\",IF(G{$currentRow}>=3.5,\"VERY SATISFACTORY\",IF(G{$currentRow}>=2.5,\"SATISFACTORY\",IF(G{$currentRow}>=1.5,\"UNSATISFACTORY\",\"POOR\"))))");
        $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('0F172A');
        $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension($currentRow)->setRowHeight(24);

        $currentRow++;

        // 6. Remarks Box
        $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "Remarks/Suggestions/Recommendations on Ratee's Performance:");
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

        // 7. Two-Phase Signatories & Reference Guide (Exact Replica of Photo)
        $defaultSupName = trim(($superiorInfo['first_name'] ?? '') . ' ' . ($superiorInfo['last_name'] ?? ''));

        $sigTargetsPreparedName = $formData['signatories']['targetsPreparedName'] ?? $rateeName;
        $sigTargetsPreparedDate = $formData['signatories']['targetsPreparedDate'] ?? '';

        $sigTargetsApprovedName = $formData['signatories']['targetsApprovedName'] ?? $defaultSupName;
        $sigTargetsApprovedDate = $formData['signatories']['targetsApprovedDate'] ?? '';

        $sigEvalRatedName = $formData['signatories']['evalRatedName'] ?? $defaultSupName;
        $sigEvalRatedDate = $formData['signatories']['evalRatedDate'] ?? '';

        $sigEvalConformeName = $formData['signatories']['evalConformeName'] ?? $rateeName;
        $sigEvalConformeDate = $formData['signatories']['evalConformeDate'] ?? '';

        $sigStartRow = $currentRow;

        // Phase 1 Header (Cols A-B)
        $sheet->mergeCells("A{$currentRow}:B{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "signed at the start of the rating period");
        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setBold(true)->setSize(8.5)->getColor()->setRGB('BA372A');

        // Phase 2 Header (Cols C-D)
        $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
        $sheet->setCellValue("C{$currentRow}", "signed at the end of the rating period");
        $sheet->getStyle("C{$currentRow}")->getFont()->setItalic(true)->setBold(true)->setSize(8.5)->getColor()->setRGB('BA372A');

        // Measures Header (Cols E-H)
        $sheet->mergeCells("E{$currentRow}:H{$currentRow}");
        $sheet->setCellValue("E{$currentRow}", "Measures & Rating Guide");
        $sheet->getStyle("E{$currentRow}")->getFont()->setBold(true)->setSize(8.5);

        $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

        $currentRow++;

        // Phase 1 Content (Cols A-B, Rows $currentRow to $currentRow + 5)
        $sheet->mergeCells("A{$currentRow}:B" . ($currentRow + 5));
        $phase1Text = "Targets prepared by:\n\n" . ($sigTargetsPreparedName ?: '____________________') . "\nEmployee (Ratee)\nDate: " . ($sigTargetsPreparedDate ?: '_____________') . "\n\nApproved by:\n\n" . ($sigTargetsApprovedName ?: '____________________') . "\nImmediate Supervisor (Rater)\nDate: " . ($sigTargetsApprovedDate ?: '_____________');
        $sheet->setCellValue("A{$currentRow}", $phase1Text);
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(8.5);

        // Phase 2 Content (Cols C-D, Rows $currentRow to $currentRow + 5)
        $sheet->mergeCells("C{$currentRow}:D" . ($currentRow + 5));
        $phase2Text = "Rated by:\n\n" . ($sigEvalRatedName ?: '____________________') . "\nImmediate Supervisor (Rater)\nDate: " . ($sigEvalRatedDate ?: '_____________') . "\n\nConforme:\n\n" . ($sigEvalConformeName ?: '____________________') . "\nEmployee (Ratee)\nDate: " . ($sigEvalConformeDate ?: '_____________');
        $sheet->setCellValue("C{$currentRow}", $phase2Text);
        $sheet->getStyle("C{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("C{$currentRow}")->getFont()->setSize(8.5);

        // Side Reference Content (Cols E-H, Rows $currentRow to $currentRow + 5)
        $sheet->mergeCells("E{$currentRow}:H" . ($currentRow + 5));
        $guideText = "Measures:\n  Q - Quality\n  T - Timeliness\n  E - Efficiency\n\nRating Guide:\n  5 - Outstanding\n  4 - Very Satisfactory\n  3 - Satisfactory\n  2 - Unsatisfactory\n  1 - Poor";
        $sheet->setCellValue("E{$currentRow}", $guideText);
        $sheet->getStyle("E{$currentRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("E{$currentRow}")->getFont()->setSize(8.5);

        $sheet->getStyle("A{$sigStartRow}:B" . ($currentRow + 5))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("C{$sigStartRow}:D" . ($currentRow + 5))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("E{$sigStartRow}:H" . ($currentRow + 5))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * Export official BSU Rubrics Template spreadsheet pre-filled with the employee's deliverables.
     *
     * @param array $doc
     * @param array|null $formData
     * @param array $ownerInfo
     * @return void
     */
    public static function exportRubricTemplate(array $doc, ?array $formData, array $ownerInfo)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Page Setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setShowGridLines(true);

        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.4);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.4);

        // 2. Column Widths
        $sheet->getColumnDimension('A')->setWidth(46);
        $sheet->getColumnDimension('B')->setWidth(8);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(35);

        // Default Font
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // 3. Header Title Block (Row 1 optional title / metadata)
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'RUBRICS TEMPLATE / SUCCESS INDICATOR STANDARDS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('14532D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 4. Matrix Table Headers (Rows 2-3)
        $sheet->mergeCells('A2:A3');
        $sheet->setCellValue('A2', "OFFICE PPA / MAJOR FINAL OUTPUT /\nEXPECTED OUTPUTS");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(9.5);

        $sheet->mergeCells('B2:E2');
        $sheet->setCellValue('B2', 'Standards or Rating Matrix per Success Indicator');
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(10);

        $sheet->setCellValue('B3', 'Rating');
        $sheet->setCellValue('C3', 'Q');
        $sheet->setCellValue('D3', 'T');
        $sheet->setCellValue('E3', 'E');

        foreach (['B3', 'C3', 'D3', 'E3'] as $col) {
            $sheet->getStyle($col)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($col)->getFont()->setBold(true)->setSize(10);
        }

        // Header Backgrounds (Gray #D9D9D9 matching reference)
        $sheet->getStyle('A2:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle('A2:E3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Header Row Heights
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // 5. Gather Deliverables from Document Form Data
        $deliverables = [];
        $categories = ['core', 'strategic', 'support'];

        if (!empty($formData['categories']) && is_array($formData['categories'])) {
            foreach ($categories as $cat) {
                if (!empty($formData['categories'][$cat]) && is_array($formData['categories'][$cat])) {
                    foreach ($formData['categories'][$cat] as $idx => $row) {
                        $ppa = trim($row['office_ppa'] ?? '');
                        $exp = trim($row['expected_outputs'] ?? $row['indicators'] ?? $row['success_indicators'] ?? $row['target'] ?? '');
                        $mfo = trim($row['mfo_title'] ?? $row['mfo'] ?? $row['paps'] ?? '');

                        $parts = array_filter([$mfo, $ppa, $exp]);
                        $text = implode("\n", $parts);

                        if (!empty($text)) {
                            $rowKey = $row['row_id'] ?? ("row-{$cat}-{$idx}");
                            $deliverables[] = [
                                'key'  => $rowKey,
                                'text' => $text,
                            ];
                        }
                    }
                }
            }
        }

        // Default placeholder deliverables if document has no rows yet
        if (empty($deliverables)) {
            $deliverables = [
                ['key' => 'row-1', 'text' => '100% of submitted/collected documents are recorded in the Monitoring Database, within 5 working days from date of receipt.'],
                ['key' => 'row-2', 'text' => 'Issued/Routed 100% of approved issuances to offices/personnel within 3 working days from receipt of signed document.'],
                ['key' => 'row-3', 'text' => '100% of requested documents are submitted within 5 working days from receipt of request with at most 2 revisions.'],
            ];
        }

        // Saved digital rubrics if any
        $savedRubrics = $formData['rubrics'] ?? [];

        // 6. Populate 5-Row Deliverable Blocks (Rows 4+)
        $currentRow = 4;
        foreach ($deliverables as $del) {
            $blockStart = $currentRow;
            $blockEnd   = $currentRow + 4;

            // Merge Col A for this deliverable
            $sheet->mergeCells("A{$blockStart}:A{$blockEnd}");
            $sheet->setCellValue("A{$blockStart}", $del['text']);
            $sheet->getStyle("A{$blockStart}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getStyle("A{$blockStart}")->getFont()->setSize(9.5);

            // Fetch saved rubric for this deliverable key or text if available
            $delRubric = $savedRubrics[$del['key']] ?? null;

            // Ratings 5 down to 1
            $scores = [5, 4, 3, 2, 1];
            foreach ($scores as $offset => $score) {
                $r = $blockStart + $offset;
                $sheet->setCellValue("B{$r}", $score);
                $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(10);

                // Populate saved digital criteria if present
                if ($delRubric && isset($delRubric[(string)$score])) {
                    $sheet->setCellValue("C{$r}", $delRubric[(string)$score]['q'] ?? '');
                    $sheet->setCellValue("D{$r}", $delRubric[(string)$score]['t'] ?? '');
                    $sheet->setCellValue("E{$r}", $delRubric[(string)$score]['e'] ?? '');
                }

                $sheet->getStyle("C{$r}:E{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("C{$r}:E{$r}")->getFont()->setSize(9);
                $sheet->getRowDimension($r)->setRowHeight(22);
            }

            // Outline & cell borders
            $sheet->getStyle("A{$blockStart}:E{$blockEnd}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $currentRow = $blockEnd + 1;
        }

        // Output Excel Stream
        $cleanOwner = preg_replace('/[^A-Za-z0-9_\-]/', '_', $ownerInfo['name'] ?? 'Employee');
        $cleanPeriod = preg_replace('/[^A-Za-z0-9_\-]/', '_', $ownerInfo['period'] ?? 'Period');
        $filename = "RUBRICS_TEMPLATE_{$cleanOwner}_{$cleanPeriod}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export the CSC SPMS Summary List of Individual Ratings (SLIR) Masterlist to Excel.
     *
     * @param string $cycleTitle
     * @param array $records
     * @param array $summaryMeta
     * @param string|null $collegeFilterName
     * @return void
     */
    public static function exportMasterlist(string $cycleTitle, array $records, array $summaryMeta = [], ?string $collegeFilterName = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ratings Master List');

        // Page Setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setShowGridLines(true);

        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.4);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.4);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(20);

        // University Header
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setSize(10)->setItalic(true);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'BENGUET STATE UNIVERSITY');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0F3D29');

        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Human Resource Development Office (HRDO)');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setSize(10)->setBold(true);

        $sheet->mergeCells('A4:E4');
        $sheet->setCellValue('A4', 'SPMS PERSONNEL ROSTER & MASTER LIST');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getFont()->setSize(11)->setBold(true);

        $scopeText = $collegeFilterName ? "Scope: {$collegeFilterName}" : 'Scope: University-Wide (All Colleges & Offices)';
        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', "Evaluation Period: {$cycleTitle}  |  {$scopeText}  |  Generated on: " . date('F j, Y'));
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5')->getFont()->setSize(9)->getColor()->setRGB('4B5563');

        // Table Header
        $headers = [
            'A' => 'NO.',
            'B' => 'PERSONNEL NAME',
            'C' => 'COLLEGE / DEPARTMENT',
            'D' => 'POSITION / DESIGNATION',
            'E' => 'EMPLOYMENT STATUS'
        ];

        $headerRow = 7;
        $sheet->getRowDimension($headerRow)->setRowHeight(26);
        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}{$headerRow}", $title);
            $sheet->getStyle("{$col}{$headerRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle("{$col}{$headerRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$headerRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('0F3D29'); // Deep BSU Forest Green
        }
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Data Rows
        $currentRow = 8;
        $idx = 1;
        $statusBreakdown = ['permanent' => 0, 'temporary' => 0, 'casual' => 0, 'contractual' => 0];

        foreach ($records as $r) {
            $sheet->getRowDimension($currentRow)->setRowHeight(20);

            // Zebra background
            $bgRgb = ($idx % 2 === 0) ? 'F9FBF9' : 'FFFFFF';

            $empStatus = !empty($r['employment_status']) ? $r['employment_status'] : 'Permanent';
            $stKey = strtolower($empStatus);
            if (isset($statusBreakdown[$stKey])) {
                $statusBreakdown[$stKey]++;
            } else {
                $statusBreakdown['permanent']++;
            }

            $personName = $r['ratee_name'] ?? $r['full_name'] ?? 'Personnel';
            $sheet->setCellValue("A{$currentRow}", $idx);
            $sheet->setCellValue("B{$currentRow}", $personName);
            $sheet->setCellValue("C{$currentRow}", $r['department'] ?? 'General Administration');
            $sheet->setCellValue("D{$currentRow}", $r['position'] ?? 'Faculty / Staff');
            $sheet->setCellValue("E{$currentRow}", strtoupper($empStatus));

            // Alignment & Styling
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getFont()->setBold(true);

            // Font & Fill
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getFont()->setSize(9);
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgRgb);
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

            $currentRow++;
            $idx++;
        }

        // Summary Section
        $summaryStart = $currentRow + 1;
        $sheet->mergeCells("A{$summaryStart}:D{$summaryStart}");
        $sheet->setCellValue("A{$summaryStart}", 'PERSONNEL & APPOINTMENT BREAKDOWN');
        $sheet->getStyle("A{$summaryStart}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('0F3D29');

        $totalCount = count($records);

        $s1 = $summaryStart + 1;
        $sheet->setCellValue("A{$s1}", 'Total Personnel:');
        $sheet->setCellValue("B{$s1}", $totalCount);
        $sheet->setCellValue("C{$s1}", 'Permanent:');
        $sheet->setCellValue("D{$s1}", $statusBreakdown['permanent']);

        $s2 = $s1 + 1;
        $sheet->setCellValue("A{$s2}", 'Temporary:');
        $sheet->setCellValue("B{$s2}", $statusBreakdown['temporary']);
        $sheet->setCellValue("C{$s2}", 'Casual / Contractual:');
        $sheet->setCellValue("D{$s2}", ($statusBreakdown['casual'] + $statusBreakdown['contractual']));

        $sheet->getStyle("A{$s1}:D{$s2}")->getFont()->setSize(9);
        $sheet->getStyle("A{$s1}:A{$s2}")->getFont()->setBold(true);
        $sheet->getStyle("C{$s1}:C{$s2}")->getFont()->setBold(true);

        // Sign-off / Certification Blocks
        $signRow = $s2 + 3;
        $sheet->setCellValue("B{$signRow}", 'Prepared by:');
        $sheet->setCellValue("D{$signRow}", 'Certified & Approved by:');
        $sheet->getStyle("B{$signRow}")->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle("D{$signRow}")->getFont()->setItalic(true)->setSize(9);

        $nameRow = $signRow + 3;
        $sheet->setCellValue("B{$nameRow}", 'HRDO DIRECTOR / SPMS FOCAL PERSON');
        $sheet->setCellValue("D{$nameRow}", 'UNIVERSITY PRESIDENT / PMT CHAIR');
        $sheet->getStyle("B{$nameRow}")->getFont()->setBold(true)->setSize(10)->setUnderline(true);
        $sheet->getStyle("D{$nameRow}")->getFont()->setBold(true)->setSize(10)->setUnderline(true);

        $titleRow = $nameRow + 1;
        $sheet->setCellValue("B{$titleRow}", 'Director, Human Resource Development Office');
        $sheet->setCellValue("D{$titleRow}", 'Benguet State University / PMT');
        $sheet->getStyle("B{$titleRow}")->getFont()->setSize(9);
        $sheet->getStyle("D{$titleRow}")->getFont()->setSize(9);

        // Stream Output
        $cleanCycle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $cycleTitle ?: 'Cycle');
        $filename = "SPMS_RATINGS_MASTER_LIST_{$cleanCycle}_" . date('Ymd') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

