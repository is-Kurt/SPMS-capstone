<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CscExcelExporter
{
    /**
     * Streams a paper's form as an .xlsx that matches the form on screen (2026-11-08): the same title, commitment
     * sentence, columns, function headings, scores ("N/A" included), summary, remarks and signatories, with the same
     * defaults the screen uses (Document::paperCycle / defaultSupervisors, the default function weights). The
     * screen's rules live in app/Views/document/partials/assets/_scripts.php (populateSpmsForm, calculateCategory,
     * getAdjectivalRating); keep the two in step.
     *
     * @param array $doc       Document::getDocumentWithFolderInfo row
     * @param array|null $formData tabs[0].formData
     * @param array $ownerInfo paper_owner_info + period (the screen's default rating period)
     * @param array $defaults  ['supervisors' => the screen's default approvers, 'weights' => DefaultFunctionWeightModel::getAllWeightsKeyed()]
     */
    public static function export(array $doc, ?array $formData, array $ownerInfo, array $defaults = [])
    {
        $formData = $formData ?? [];
        $type = self::formType($doc, $formData);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(9.5);
        $sheet = $spreadsheet->getActiveSheet();
        self::pageSetup($sheet);

        if ($type === 'IPERF') {
            self::exportIperf($spreadsheet, $formData, $doc, $ownerInfo, $defaults);
        } else {
            self::exportStandard($sheet, $type, $formData, $ownerInfo, $defaults);
        }

        $rateeName = self::rateeName($formData, $ownerInfo);
        $sanitizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rateeName ?: 'Employee');
        $fileName = "{$type}_{$sanitizedName}_" . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        self::applyProtection($spreadsheet, $spreadsheet->getActiveSheet());

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /** IPCR, DPCR, OPCR or IPERF, decided as the form does (app/Views/document/show.php). */
    private static function formType(array $doc, array $formData): string
    {
        $tabType = strtolower(trim((string) ($formData['doc_type'] ?? '')));
        $ownerType = strtolower((string) ($doc['doc_type'] ?? '') ?: ($tabType ?: 'ipcr'));
        $titles = strtoupper(trim((string) ($doc['title'] ?? ''))) . ' ' . strtoupper(trim((string) ($formData['title'] ?? '')));
        $noStoredType = empty($doc['doc_type']) && $tabType === '';
        $ownerFormType = '';
        if ($noStoredType && !empty($doc['owner_id'])) {
            $owner = (new \App\Models\UserModel())->find($doc['owner_id']);
            $ownerFormType = strtoupper((string) ($owner['doc_type'] ?? '')) ?: \App\Libraries\OrgStructure::instance()->defaultDocTypeFor((int) $doc['owner_id']);
        }
        $stored = strtoupper((string) ($doc['doc_type'] ?? ''));
        $trueOpcr = $tabType === 'opcr' || str_contains($titles, 'OPCR') || str_contains($titles, 'OFFICE') || $stored === 'OPCR';
        $trueDpcr = $tabType === 'dpcr' || str_contains($titles, 'DPCR') || str_contains($titles, 'DIVISION') || str_contains($titles, 'DEPARTMENT') || $stored === 'DPCR';
        if ($ownerType === 'dpcr' || $trueDpcr || ($noStoredType && $ownerFormType === 'DPCR')) return 'DPCR';
        if ($trueOpcr || ($noStoredType && $ownerFormType === 'OPCR')) return 'OPCR';
        if ($ownerType === 'iperf' || $tabType === 'iperf' || stripos($titles, 'iperf') !== false || ($noStoredType && $ownerFormType === 'IPERF')) return 'IPERF';
        return 'IPCR';
    }

    private static function pageSetup(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setShowGridLines(true);
        $sheet->getPageMargins()->setTop(0.5)->setRight(0.4)->setBottom(0.5)->setLeft(0.4);
    }

    /** A saved value, unless it is blank (the screen treats "" and "   " as not set). */
    private static function filled($value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        return $value === '' ? null : $value;
    }

    private static function rateeName(array $fd, array $ownerInfo): string
    {
        return self::filled($fd['ratee']['name'] ?? null) ?? (string) ($ownerInfo['name'] ?? '');
    }

    /** The title on screen: the saved one, unless it belongs to another form type. */
    private static function formTitle(string $type, array $fd): string
    {
        $defaults = [
            'IPCR'  => 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW',
            'DPCR'  => 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)',
            'OPCR'  => 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)',
            'IPERF' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL',
        ];
        $title = (string) ($fd['title'] ?? '');
        $foreign = ['IPCR' => [], 'DPCR' => ['IPCR', 'OPCR', 'Division Performance'], 'OPCR' => ['IPCR', 'DPCR', 'Faculty / Professors'], 'IPERF' => ['IPCR', 'DPCR', 'OPCR']][$type];
        foreach ($foreign as $word) if (str_contains($title, $word)) $title = '';
        if (str_contains($title, 'Faculty / Professors') || str_contains($title, 'IPCR Form')) $title = '';
        return $title !== '' ? $title : $defaults[$type];
    }

    /** The rating period on screen: the saved one, else the cycle's (ownerInfo period). */
    private static function period(array $fd, array $ownerInfo): string
    {
        $saved = (string) ($fd['ratee']['period'] ?? '');
        if (str_starts_with(strtolower($saved), 'untitled evaluation')) $saved = '';
        return $saved !== '' ? $saved : (string) ($ownerInfo['period'] ?? '');
    }

    /** The approvers on screen: the saved ones, else the default approvers, else one blank. */
    private static function approvers(array $fd, array $defaults): array
    {
        $list = [];
        if (!empty($fd['approvers']) && is_array($fd['approvers'])) {
            foreach ($fd['approvers'] as $a) if (is_array($a) && self::filled($a['name'] ?? null) !== null) { $list = $fd['approvers']; break; }
        } elseif (self::filled($fd['approver']['name'] ?? null) !== null) {
            $list = [$fd['approver']];
        } elseif (self::filled($fd['signatories']['dean'] ?? null) !== null) {
            $list = [['name' => $fd['signatories']['dean'], 'position' => $fd['signatories']['deanPos'] ?? '',
                      'date' => $fd['signatories']['deanDate'] ?? '', 'evalDate' => $fd['signatories']['deanDate'] ?? '']];
        }
        if (!$list) $list = $defaults['supervisors'] ?? [];
        if (!$list) $list = [['name' => '', 'position' => '', 'date' => '', 'evalDate' => '']];
        return array_values(array_map(fn($a) => [
            'name' => (string) ($a['name'] ?? ''), 'position' => (string) ($a['position'] ?? ''),
            'date' => (string) ($a['date'] ?? ''), 'evalDate' => (string) ($a['evalDate'] ?? ''),
        ], array_filter($list, 'is_array')));
    }

    /** Core / strategic / support weights as fractions (null = not set): the saved ones, else the default weights. */
    private static function weights(string $type, array $fd, array $defaults): array
    {
        $w = [];
        foreach (['core', 'strategic', 'support'] as $k) {
            $v = $fd['weights'][$k] ?? null;
            $w[$k] = ($v === null || $v === '') ? null : (float) $v;
        }
        $sys = $defaults['weights'][$type] ?? null;
        if (in_array(null, $w, true) && $sys && isset($sys['core_fraction'])) {
            $w = ['core' => (float) $sys['core_fraction'], 'strategic' => (float) $sys['strategic_fraction'], 'support' => (float) $sys['support_fraction']];
        }
        return $w;
    }

    /** A score as on screen: 1 to 5, "N/A" for a dimension marked not applicable, else blank. */
    private static function score($v)
    {
        $s = is_scalar($v) ? strtoupper(trim((string) $v)) : '';
        if ($s === 'X' || $s === 'N/A') return 'N/A';
        if ($s === '' || !preg_match('/^-?\d+/', $s)) return null;
        $n = (int) $s;
        return ($n >= 1 && $n <= 5) ? $n : null;
    }

    private static function rich(array $runs): \PhpOffice\PhpSpreadsheet\RichText\RichText
    {
        $rt = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        foreach ($runs as [$text, $style]) {
            $font = $rt->createTextRun($text)->getFont();
            $font->setName('Arial')->setSize($style['size'] ?? 9.5)->setBold(!empty($style['bold']))->setItalic(!empty($style['italic']));
            $font->getColor()->setRGB($style['color'] ?? '0F172A');
        }
        return $rt;
    }

    private static function merge(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range, $value = null): void
    {
        if (str_contains($range, ':')) {
            [$a, $b] = explode(':', $range);
            if ($a !== $b) $sheet->mergeCells($range);
        }
        if ($value !== null) {
            $first = explode(':', $range)[0];
            $value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText ? $sheet->setCellValue($first, $value) : self::put($sheet, $first, $value);
        }
    }

    /**
     * Rows tall enough for $text in the merged block $fromCol..$toCol x $rowFrom..$rowTo: Excel sizes a row to its
     * wrapped text, but never for merged cells. $lines adds fixed line breaks; ~1.15 characters per width unit at 9.5pt.
     */
    private static function fit(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $fromCol, string $toCol, int $rowFrom, int $rowTo, string $text, float $lineHeight = 12.5): void
    {
        $width = 0.0;
        for ($c = $fromCol; ; $c++) { $width += $sheet->getColumnDimension($c)->getWidth(); if ($c === $toCol) break; }
        $perLine = max(10, $width * 1.15);
        $lines = 0;
        foreach (explode("\n", $text) as $part) $lines += max(1, (int) ceil(mb_strlen($part) / $perLine));
        $per = max(15, ($lines * $lineHeight + 4) / ($rowTo - $rowFrom + 1));
        for ($r = $rowFrom; $r <= $rowTo; $r++) {
            if ($sheet->getRowDimension($r)->getRowHeight() < $per) $sheet->getRowDimension($r)->setRowHeight($per);
        }
    }

    private static function letterhead(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $last, string $office, string $title): void
    {
        self::merge($sheet, "A1:{$last}1", 'Republic of the Philippines');
        $sheet->getStyle('A1')->getFont()->setName('Bookman Old Style')->setSize(10)->setBold(true)->getColor()->setRGB('111827');
        self::merge($sheet, "A2:{$last}2", 'Benguet State University');
        $sheet->getStyle('A2')->getFont()->setName('Old English Text MT')->setSize(17)->getColor()->setRGB('436531');
        $sheet->getRowDimension(2)->setRowHeight(28);
        self::merge($sheet, "A3:{$last}3", 'La Trinidad, 2601 Benguet');
        $sheet->getStyle('A3')->getFont()->setName('Bookman Old Style')->setSize(9.5)->setItalic(true)->getColor()->setRGB('1F2937');
        $sheet->getRowDimension(3)->setRowHeight(16);
        self::merge($sheet, "A4:{$last}4", $office);
        $sheet->getStyle('A4')->getFont()->setName('Monotype Corsiva')->setSize(14)->setItalic(true)->setBold(true)->getColor()->setRGB('111827');
        $sheet->getRowDimension(4)->setRowHeight(20);
        self::merge($sheet, "A5:{$last}5", $title);
        $sheet->getStyle('A5')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A5')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(5)->setRowHeight(22);
        $sheet->getStyle("A1:{$last}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        self::addOfficialSeals($sheet, $last);
    }

    /** IPCR, DPCR and OPCR: one layout, the columns per type as on screen (templates/ipcr.php, dpcr.php, opcr.php). */
    private static function exportStandard(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $type, array $fd, array $ownerInfo, array $defaults): void
    {
        $wide = $type !== 'IPCR';   // DPCR and OPCR add Allotted Budget and the accountable column
        $sheet->setTitle($type);
        $C = $wide
            ? ['mfo' => 'A', 'ind' => 'B', 'budget' => 'C', 'acc' => 'D', 'done' => 'E', 'q' => 'F', 't' => 'G', 'e' => 'H', 'ave' => 'I', 'rem' => 'J']
            : ['mfo' => 'A', 'ind' => 'B', 'done' => 'C', 'q' => 'D', 't' => 'E', 'e' => 'F', 'ave' => 'G', 'rem' => 'H'];
        $last = $C['rem'];
        $widths = $wide ? ['A' => 24, 'B' => 28, 'C' => 15, 'D' => 18, 'E' => 24, 'F' => 6, 'G' => 6, 'H' => 6, 'I' => 8, 'J' => 20]
                        : ['A' => 28, 'B' => 30, 'C' => 28, 'D' => 6, 'E' => 6, 'F' => 6, 'G' => 8, 'H' => 22];
        foreach ($widths as $col => $w) $sheet->getColumnDimension($col)->setWidth($w);
        // left and right halves for the header and signature blocks
        $leftEnd = $wide ? 'D' : 'C';
        $rightStart = $wide ? 'E' : 'D';

        $defaultName = (string) ($ownerInfo['name'] ?? '');
        $defaultPos = (string) ($ownerInfo['position'] ?? '');
        $defaultDept = (string) ($ownerInfo['dept'] ?? '');
        $rateeName = self::rateeName($fd, $ownerInfo);
        $office = self::filled($fd['ratee']['dept'] ?? null) ?? ($defaultDept ?: 'Office of the University President');
        self::letterhead($sheet, $last, $office, self::formTitle($type, $fd));

        // commitment sentence (meta/_meta_standard.php)
        $roles = (!empty($fd['ratee']['roles']) && is_array($fd['ratee']['roles'])) ? $fd['ratee']['roles'] : ($ownerInfo['roles'] ?? []);
        if ($roles && self::filled($fd['ratee']['position'] ?? null) !== null && empty($fd['ratee']['roles'])) {
            $roles[0]['position'] = $fd['ratee']['position'];
            if (self::filled($fd['ratee']['dept'] ?? null) !== null) $roles[0]['dept'] = $fd['ratee']['dept'];
        }
        if (!$roles) $roles = [['position' => self::filled($fd['ratee']['position'] ?? null) ?? $defaultPos, 'dept' => self::filled($fd['ratee']['dept'] ?? null) ?? $defaultDept]];
        $blank = '____________';
        $parts = [];
        foreach (array_values($roles) as $i => $r) {
            $p = (string) ($r['position'] ?? ($i === 0 ? $defaultPos : ''));
            $d = (string) ($r['dept'] ?? ($i === 0 ? $defaultDept : ''));
            $parts[] = ($p !== '' ? $p : $blank) . ' of the ' . ($d !== '' ? $d : $blank);
        }
        $rolesText = count($parts) <= 2 ? implode(', and ', $parts) : implode(', ', array_slice($parts, 0, -1)) . ', and ' . end($parts);
        $period = self::period($fd, $ownerInfo);
        $sentence = 'I, ' . ($rateeName !== '' ? $rateeName : $blank) . ', ' . $rolesText
            . ', commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period '
            . ($period !== '' ? $period : $blank) . '.';
        self::merge($sheet, "A7:{$last}8", $sentence);
        $sheet->getStyle('A7')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_JUSTIFY);
        self::fit($sheet, 'A', $last, 7, 8, $sentence, 13);

        // Approved by (left) and the employee + rating scale (right)
        $approvers = self::approvers($fd, $defaults);
        $r = 10;
        self::merge($sheet, "A{$r}:{$leftEnd}{$r}", 'APPROVED BY:');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        $r++;
        foreach ($approvers as $i => $a) {
            $nameRuns = [['Name: ', ['bold' => true]], [$a['name'], ['bold' => true]]];
            if ($i === 0 && $wide) $nameRuns[] = ['   (may add signatories depending on position)', ['italic' => true, 'size' => 8, 'color' => '5F6F86']];
            self::merge($sheet, "A{$r}:{$leftEnd}{$r}", self::rich($nameRuns)); $r++;
            self::merge($sheet, "A{$r}:{$leftEnd}{$r}", self::rich([['Position: ', ['bold' => true]], [$a['position'], []]])); $r++;
            self::merge($sheet, "A{$r}:{$leftEnd}{$r}", self::rich([['Date: ', ['bold' => true]], [$a['date'], []]])); $r++;
            if ($i < count($approvers) - 1) $r++;
        }
        $leftBottom = $r - 1;
        $rr = 10;
        $signName = self::filled($fd['rateeSign']['name'] ?? null) ?? $defaultName;
        $right = [
            [$signName, ['bold' => true]],
            ['Name of Employee', ['bold' => true, 'size' => 9, 'color' => '334155']],
            ['Date: ' . (string) ($fd['rateeSign']['date'] ?? ''), ['size' => 9, 'color' => '334155']],
            null,
            ['Rating Scale:', ['bold' => true, 'size' => 9]],
            ['5 – Outstanding', ['size' => 9, 'color' => '475569']],
            ['4 – Very Satisfactory', ['size' => 9, 'color' => '475569']],
            ['3 – Satisfactory', ['size' => 9, 'color' => '475569']],
            ['2 – Unsatisfactory', ['size' => 9, 'color' => '475569']],
            ['1 – Poor', ['size' => 9, 'color' => '475569']],
        ];
        foreach ($right as $line) {
            if ($line !== null) self::merge($sheet, "{$rightStart}{$rr}:{$last}{$rr}", self::rich([$line]));
            $rr++;
        }
        $r = max($leftBottom, $rr - 1) + 2;

        // table header (templates/*.php)
        $currency = self::filled($fd['budget_currency'] ?? null) ?? self::filled($fd['currency'] ?? null) ?? '₱';
        $heads = [
            'IPCR' => ['mfo' => 'MAJOR FINAL OUTPUT', 'ind' => "SUCCESS INDICATORS\n(TARGETS + MEASURES)", 'done' => 'ACTUAL ACCOMPLISHMENTS', 'rating' => 'RATING', 'ave' => 'Ave.'],
            'DPCR' => ['mfo' => 'PROGRAMS, PROJECTS, ACTIVITIES', 'ind' => "SUCCESS INDICATORS\n(TARGETS + MEASURES)", 'budget' => "ALLOTTED BUDGET\n({$currency})",
                       'acc' => "INDIVIDUALS /\nOFFICES ACCOUNTABLE", 'done' => 'ACTUAL ACCOMPLISHMENTS', 'rating' => 'RATING', 'ave' => 'Ave.'],
            'OPCR' => ['mfo' => "PROJECT/\nPROGRAM/\nACTIVITIES", 'ind' => "SUCCESS INDICATORS (TARGETS +\nMEASURES) PERFORMANCE", 'budget' => "ALLOTTED\nBUDGET\n({$currency})",
                       'acc' => "DIVISIONS\nACCOUNTABLE", 'done' => 'ACTUAL ACCOMPLISHMENT', 'rating' => 'RATINGS', 'ave' => 'AVE'],
        ][$type];
        $h1 = $r; $h2 = $r + 1;
        foreach (['mfo', 'ind', 'budget', 'acc', 'done'] as $k) if (isset($C[$k])) self::merge($sheet, "{$C[$k]}{$h1}:{$C[$k]}{$h2}", $heads[$k]);
        self::merge($sheet, "{$C['q']}{$h1}:{$C['ave']}{$h1}", $heads['rating']);
        self::put($sheet, "{$C['q']}{$h2}", 'Q');
        self::put($sheet, "{$C['t']}{$h2}", 'T');
        self::put($sheet, "{$C['e']}{$h2}", 'E');
        self::put($sheet, "{$C['ave']}{$h2}", $heads['ave']);
        self::merge($sheet, "{$C['rem']}{$h1}:{$C['rem']}{$h2}", 'REMARKS');
        $hs = $sheet->getStyle("A{$h1}:{$last}{$h2}");
        $hs->getFont()->setBold(true)->setSize(9);
        $hs->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $hs->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $hs->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($h1)->setRowHeight($type === 'OPCR' ? 30 : 22);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($h1, $h2);
        $r = $h2 + 1;

        // function sections and rows (forms/_body_standard.php, renderScoreCellTdHtml, calculateCategory)
        $w = self::weights($type, $fd, $defaults);
        $names = ['core' => 'CORE FUNCTIONS', 'strategic' => 'STRATEGIC FUNCTIONS', 'support' => 'SUPPORT FUNCTIONS'];
        $ranges = [];
        foreach ($names as $cat => $label) {
            $pct = ($w[$cat] !== null && $w[$cat] > 0) ? round($w[$cat] * 100) . '%' : ' ____ %';
            self::merge($sheet, "A{$r}:{$last}{$r}", self::rich([
                ["{$label} ({$pct})", ['bold' => true]],
                ['   (depending on position/designation)', ['italic' => true, 'size' => 8, 'color' => '5F6F86']],
            ]));
            $sheet->getStyle("A{$r}:{$last}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            $sheet->getStyle("A{$r}:{$last}{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($r)->setRowHeight(20);
            $r++;
            $rows = array_values(array_filter($fd['categories'][$cat] ?? [], 'is_array')) ?: [[]];
            $start = $r;
            foreach ($rows as $row) {
                self::put($sheet, "{$C['mfo']}{$r}", (string) ($row['mfo'] ?? ''));
                self::put($sheet, "{$C['ind']}{$r}", (string) ($row['indicators'] ?? ''));
                if (isset($C['budget'])) {
                    $b = trim((string) ($row['budget'] ?? ''));
                    $cur = self::filled($row['budget_currency'] ?? null) ?? $currency;
                    $num = str_replace([',', ' ', $cur], '', $b);
                    if ($b !== '' && is_numeric($num)) {
                        $sheet->setCellValue("{$C['budget']}{$r}", (float) $num);
                        $sheet->getStyle("{$C['budget']}{$r}")->getNumberFormat()->setFormatCode('"' . str_replace('"', '', $cur) . ' "#,##0.00');
                    } else {
                        self::put($sheet, "{$C['budget']}{$r}", $b);
                    }
                }
                if (isset($C['acc'])) self::put($sheet, "{$C['acc']}{$r}", (string) ($row['accountable'] ?? ''));
                self::put($sheet, "{$C['done']}{$r}", (string) ($row['accomplishments'] ?? ''));
                foreach (['q', 't', 'e'] as $d) {
                    $s = self::score($row[$d] ?? null);
                    if ($s !== null) self::put($sheet, "{$C[$d]}{$r}", $s);
                }
                $sheet->setCellValue("{$C['ave']}{$r}", "=IF(COUNT({$C['q']}{$r}:{$C['e']}{$r})>0,AVERAGE({$C['q']}{$r}:{$C['e']}{$r}),\"—\")");
                self::put($sheet, "{$C['rem']}{$r}", (string) ($row['remarks'] ?? ''));
                $sheet->getStyle("A{$r}:{$last}{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("{$C['q']}{$r}:{$C['ave']}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("{$C['ave']}{$r}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("{$C['ave']}{$r}")->getFont()->setBold(true);
                $sheet->getStyle("A{$r}:{$last}{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $r++;
            }
            $ranges[$cat] = "{$C['ave']}{$start}:{$C['ave']}" . ($r - 1);
        }

        // summary (forms/_summary_table.php): weights on the left, the breakdown and the final rating on the right
        $s = $r;
        [$boxEnd, $labelCols, $scoreCols, $noteCols] = $wide
            ? ['B', ['C', 'D'], ['E', 'E'], ['F', 'J']]
            : ['B', ['C', 'C'], ['D', 'F'], ['G', 'H']];
        $allSet = $w['core'] && $w['strategic'] && $w['support'];
        if ($allSet) {
            $total = (int) round(($w['core'] + $w['strategic'] + $w['support']) * 100);
            $formula = sprintf('Core Functions (%d%%) + Strategic Functions (%d%%) + Support Functions (%d%%).', round($w['core'] * 100), round($w['strategic'] * 100), round($w['support'] * 100));
            $check = $total === 100 ? [' (Total: 100%)', ['bold' => true, 'color' => '10B981']] : [" Error: Total weight is {$total}% (Must equal 100% exactly)", ['bold' => true, 'color' => 'EF4444']];
        } else {
            $formula = 'Core Functions ( ____ %) + Strategic Functions ( ____ %) + Support Functions ( ____ %).';
            $check = [' (Required: All category weights must be entered and total 100%)', ['bold' => true, 'color' => 'EF4444']];
        }
        self::merge($sheet, "A{$s}:{$boxEnd}" . ($s + 3), self::rich([
            ["Formula Weights:\n", ['bold' => true, 'size' => 8.5, 'color' => '64748B']],
            [$formula, ['size' => 9.5, 'color' => '334155']], $check,
            ["\n\nValidated against standard Civil Service Commission SPMS Guidelines.", ['italic' => true, 'size' => 8.5, 'color' => '94A3B8']],
        ]));
        $sheet->getStyle("A{$s}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        self::fit($sheet, 'A', $boxEnd, $s, $s + 3, "Formula Weights:\n{$formula}{$check[0]}\n\nValidated against standard Civil Service Commission SPMS Guidelines.");
        $sheet->getStyle("A{$s}:{$boxEnd}" . ($s + 3))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
        $sheet->getStyle("A{$s}:{$boxEnd}" . ($s + 3))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $lines = ['core' => 'CORE FUNCTION', 'strategic' => 'STRATEGIC FUNCTION', 'support' => 'SUPPORT FUNCTIONS'];
        $scoreCells = [];
        $i = 0;
        foreach ($lines as $cat => $label) {
            $row = $s + $i++;
            $weight = $w[$cat] ?? 0.0;
            $mult = $w[$cat] !== null ? number_format($w[$cat], 2) : '—';
            self::merge($sheet, "{$labelCols[0]}{$row}:{$labelCols[1]}{$row}", $label);
            $sheet->getStyle("{$labelCols[0]}{$row}")->getFont()->setBold(true)->setSize(9);
            $score = "{$scoreCols[0]}{$row}";
            self::merge($sheet, "{$scoreCols[0]}{$row}:{$scoreCols[1]}{$row}");
            $sheet->setCellValue($score, "=IFERROR(AVERAGE({$ranges[$cat]}),0)*" . (float) $weight);
            $sheet->getStyle($score)->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle($score)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(['core' => '047857', 'strategic' => '0284C7', 'support' => 'D97706'][$cat]);
            self::merge($sheet, "{$noteCols[0]}{$row}:{$noteCols[1]}{$row}");
            $sheet->setCellValue("{$noteCols[0]}{$row}", "=\"(Average: \"&TEXT(IFERROR(AVERAGE({$ranges[$cat]}),0),\"0.000\")&\" × {$mult})\"");
            $sheet->getStyle("{$noteCols[0]}{$row}")->getFont()->setSize(9)->getColor()->setRGB('64748B');
            $sheet->getStyle("{$noteCols[0]}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$labelCols[0]}{$row}:{$last}{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $scoreCells[$cat] = $score;
        }
        $fr = $s + 3;
        self::merge($sheet, "{$labelCols[0]}{$fr}:{$labelCols[1]}{$fr}", 'FINAL AVERAGE RATING');
        $sheet->getStyle("{$labelCols[0]}{$fr}")->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('E2E8F0');
        $grand = "{$scoreCols[0]}{$fr}";
        self::merge($sheet, "{$scoreCols[0]}{$fr}:{$scoreCols[1]}{$fr}");
        $sheet->setCellValue($grand, '=' . implode('+', $scoreCells));
        $sheet->getStyle($grand)->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle($grand)->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FBBF24');
        $rated = 'COUNT(' . implode(',', $ranges) . ')';
        self::merge($sheet, "{$noteCols[0]}{$fr}:{$noteCols[1]}{$fr}");
        $sheet->setCellValue("{$noteCols[0]}{$fr}", "=IF({$rated}=0,\"PENDING EVALUATION\",IF({$grand}>=4.5,\"OUTSTANDING\",IF({$grand}>=3.5,\"VERY SATISFACTORY\",IF({$grand}>=2.5,\"SATISFACTORY\",IF({$grand}>=1.5,\"UNSATISFACTORY\",\"POOR\")))))"
            . "&CHAR(10)&\"(Core \"&TEXT({$scoreCells['core']},\"0.000\")&\" + Strategic \"&TEXT({$scoreCells['strategic']},\"0.000\")&\" + Support \"&TEXT({$scoreCells['support']},\"0.000\")&\")\"");
        $sheet->getStyle("{$noteCols[0]}{$fr}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("{$noteCols[0]}{$fr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setWrapText(true);
        $sheet->getStyle("{$labelCols[0]}{$fr}:{$last}{$fr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('062316');
        $sheet->getStyle("{$labelCols[0]}{$fr}:{$last}{$fr}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($fr)->setRowHeight(30);
        self::fit($sheet, $noteCols[0], $noteCols[1], $fr, $fr, "VERY SATISFACTORY\n(Core 0.000 + Strategic 0.000 + Support 0.000)", 13);
        $sheet->getStyle("{$labelCols[0]}{$s}:{$last}{$fr}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$s}:{$last}{$fr}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $r = $fr + 1;

        // remarks box (layout/_remarks_box.php)
        self::merge($sheet, "A{$r}:{$last}{$r}", "Remarks/Suggestions/Recommendations on Ratee's Performance:");
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(9.5);
        self::merge($sheet, "A" . ($r + 1) . ":{$last}" . ($r + 2), (string) ($fd['pmtRemarks'] ?? ''));
        $sheet->getStyle("A" . ($r + 1))->getFont()->setItalic(true)->setSize(9.5);
        $sheet->getStyle("A" . ($r + 1))->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        self::fit($sheet, 'A', $last, $r + 1, $r + 2, (string) ($fd['pmtRemarks'] ?? ''));
        $sheet->getStyle("A{$r}:{$last}" . ($r + 2))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $r += 4;

        // signatories (signatures/_signatures_standard.php): the ratee left, "Final Rating by" per approver right
        $sig = is_array($fd['signatories'] ?? null) ? $fd['signatories'] : [];
        $sigTop = $r;
        $left = [
            [['Name and Signature of Ratee: ', []], [self::filled($sig['ratee'] ?? null) ?? $defaultName, ['bold' => true]]],
            [['Position: ', []], [self::filled($sig['rateePos'] ?? null) ?? $defaultPos, []]],
            [['Date: ', []], [self::filled($sig['rateeDate'] ?? null) ?? '', []]],
        ];
        foreach ($left as $k => $runs) self::merge($sheet, "A" . ($sigTop + $k) . ":{$leftEnd}" . ($sigTop + $k), self::rich($runs));
        $rr = $sigTop;
        foreach ($approvers as $i => $a) {
            if ($i === 0) {
                $a['name'] = self::filled($sig['dean'] ?? null) ?? $a['name'];
                $a['position'] = self::filled($sig['deanPos'] ?? null) ?? $a['position'];
                $a['evalDate'] = self::filled($sig['deanDate'] ?? null) ?? $a['evalDate'];
            }
            $block = [
                [['Final Rating by' . ($i > 0 ? ' (Signatory ' . ($i + 1) . ')' : '') . ':', ['bold' => true]]],
                [[$a['name'], ['bold' => true]]],
                [['Position: ', []], [$a['position'], []]],
                [['Date: ', []], [$a['evalDate'], []]],
            ];
            if ($i === 0) $block[] = [['(may add signatories depending on position/designation)', ['italic' => true, 'size' => 8, 'color' => '5F6F86']]];
            foreach ($block as $runs) { self::merge($sheet, "{$rightStart}{$rr}:{$last}{$rr}", self::rich($runs)); $rr++; }
            $rr++;
        }
    }

    /**
     * IPERF (COS and Job Order personnel), as on screen: templates/iperf.php, meta/_meta_iperf.php,
     * forms/_body_iperf.php, signatures/_signatures_iperf.php (2026-11-08).
     */
    private static function exportIperf(Spreadsheet $spreadsheet, array $fd, array $doc, array $ownerInfo, array $defaults = []): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('IPERF - COS & JO');
        foreach (['A' => 28, 'B' => 30, 'C' => 28, 'D' => 6, 'E' => 6, 'F' => 6, 'G' => 8, 'H' => 22] as $col => $w) $sheet->getColumnDimension($col)->setWidth($w);

        $defaultName = (string) ($ownerInfo['name'] ?? '');
        $rateeName = self::rateeName($fd, $ownerInfo);
        $rateePos = self::filled($fd['ratee']['position'] ?? null) ?? (string) ($ownerInfo['position'] ?? '');
        $rateeDept = self::filled($fd['ratee']['dept'] ?? null) ?? (string) ($ownerInfo['dept'] ?? '');
        $office = self::filled($fd['ratee']['dept'] ?? null) ?? ((string) ($ownerInfo['dept'] ?? '') ?: 'Office of the University President');
        self::letterhead($sheet, 'H', $office, self::formTitle('IPERF', $fd));
        self::merge($sheet, 'A6:H6', '(attach rubrics for the rating of actual accomplishments vis-à-vis expected outputs)');
        $sheet->getStyle('A6')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('5F6F86');
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // name, classification, position, rating period, office
        $meta = [
            8  => ['Name of Employee:', $rateeName ?: 'indicate full name (First Name Middle Initial Last Name, Extension; e.g., Juan D. Cruz III)', 'Classification:', iperf_classification($ownerInfo['employment_status'] ?? null) ?: (self::filled($fd['classification'] ?? null) ?? '')],
            9  => ['Position:', $rateePos ?: 'indicate the full position title specified in the contract/job order', 'Rating Period:', self::period($fd, $ownerInfo) ?: 'e.g., July - December 2024'],
            10 => ['Office:', $rateeDept ?: 'indicate in full the specific area of assignment (e.g., CIS - Department of Development Communication)', null, null],
        ];
        foreach ($meta as $row => [$l1, $v1, $l2, $v2]) {
            self::put($sheet, "A{$row}", $l1);
            if ($l2 === null) {
                self::merge($sheet, "B{$row}:H{$row}", $v1);
            } else {
                self::merge($sheet, "B{$row}:C{$row}", $v1);
                self::merge($sheet, "D{$row}:F{$row}", $l2);
                self::merge($sheet, "G{$row}:H{$row}", $v2);
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);
                $sheet->getStyle("D{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
                $sheet->getStyle("G{$row}")->getFont()->setBold(true);
            }
            self::fit($sheet, 'B', $l2 === null ? 'H' : 'C', $row, $row, (string) $v1);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
        }
        $sheet->getStyle('B8')->getFont()->setBold(true);
        $sheet->getStyle('A8:H10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A8:H10')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // table header
        self::merge($sheet, 'A12:A13', self::rich([
            ["OFFICE PPA\n", ['bold' => true, 'size' => 9]], ["(PROGRAMS, PROJECTS, ACTIVITIES)\n", ['size' => 8.5]],
            ['(aligned with the deliverables of the office)', ['italic' => true, 'size' => 8, 'color' => '5F6F86']],
        ]));
        self::merge($sheet, 'B12:B13', self::rich([
            ["EXPECTED OUTPUTS\n", ['bold' => true, 'size' => 9]],
            ['(based on contract or duties and responsibilities in the request to hire personnel)', ['italic' => true, 'size' => 8, 'color' => '5F6F86']],
        ]));
        self::merge($sheet, 'C12:C13', 'ACTUAL ACCOMPLISHMENTS');
        self::merge($sheet, 'D12:G12', 'RATING');
        foreach (['D13' => 'Q', 'E13' => 'T', 'F13' => 'E', 'G13' => 'Ave.'] as $cell => $v) self::put($sheet, $cell, $v);
        self::merge($sheet, 'H12:H13', 'REMARKS');
        $hs = $sheet->getStyle('A12:H13');
        $hs->getFont()->setBold(true)->setSize(9);
        $hs->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $hs->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $hs->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension(12)->setRowHeight(34);
        $sheet->getRowDimension(13)->setRowHeight(30);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(12, 13);

        // deliverables (one flat list)
        $r = 14;
        $rows = array_values(array_filter($fd['categories']['core'] ?? [], 'is_array')) ?: [[]];
        foreach ($rows as $row) {
            self::put($sheet, "A{$r}", (string) ($row['mfo'] ?? ''));
            self::put($sheet, "B{$r}", (string) ($row['indicators'] ?? ''));
            self::put($sheet, "C{$r}", (string) ($row['accomplishments'] ?? ''));
            foreach (['q' => 'D', 't' => 'E', 'e' => 'F'] as $d => $col) {
                $s = self::score($row[$d] ?? null);
                if ($s !== null) self::put($sheet, "{$col}{$r}", $s);
            }
            $sheet->setCellValue("G{$r}", "=IF(COUNT(D{$r}:F{$r})>0,AVERAGE(D{$r}:F{$r}),\"—\")");
            self::put($sheet, "H{$r}", (string) ($row['remarks'] ?? ''));
            $sheet->getStyle("A{$r}:H{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("D{$r}:G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("G{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}:H{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $r++;
        }
        $range = 'G14:G' . ($r - 1);

        // overall average (label over 3 columns, the average over 2, the rating over 3, as on screen)
        self::merge($sheet, "A{$r}:C{$r}", 'OVERALL AVERAGE RATING');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(10);
        self::merge($sheet, "D{$r}:E{$r}");
        $sheet->setCellValue("D{$r}", "=IFERROR(AVERAGE({$range}),0)");
        $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('047857');
        self::merge($sheet, "F{$r}:H{$r}");
        $sheet->setCellValue("F{$r}", "=IF(COUNT({$range})=0,\"PENDING EVALUATION\",IF(D{$r}>=4.5,\"OUTSTANDING\",IF(D{$r}>=3.5,\"VERY SATISFACTORY\",IF(D{$r}>=2.5,\"SATISFACTORY\",IF(D{$r}>=1.5,\"UNSATISFACTORY\",\"POOR\")))))");
        $sheet->getStyle("F{$r}")->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle("D{$r}:H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$r}:H{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$r}:H{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $sheet->getStyle("A{$r}:H{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($r)->setRowHeight(24);
        $r += 1;

        // remarks box
        self::merge($sheet, "A{$r}:H{$r}", "Remarks/Suggestions/Recommendations on Ratee's Performance:");
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(9.5);
        self::merge($sheet, "A" . ($r + 1) . ":H" . ($r + 2), (string) ($fd['pmtRemarks'] ?? ''));
        $sheet->getStyle("A" . ($r + 1))->getFont()->setItalic(true)->setSize(9.5);
        $sheet->getStyle("A" . ($r + 1))->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        self::fit($sheet, 'A', 'H', $r + 1, $r + 2, (string) ($fd['pmtRemarks'] ?? ''));
        $sheet->getStyle("A{$r}:H" . ($r + 2))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $r += 4;

        // signatories: start of period, end of period, measures and rating guide
        $sig = is_array($fd['signatories'] ?? null) ? $fd['signatories'] : [];
        $approvers = self::approvers($fd, $defaults);
        $rater = $approvers[0]['name'] ?? '';
        $val = fn(string $k, string $fallback = '') => self::filled($sig[$k] ?? null) ?? $fallback;
        $hint = ['italic' => true, 'bold' => true, 'size' => 9, 'color' => '5F6F86'];
        $lbl = ['bold' => true, 'size' => 9.5];
        $nm = ['bold' => true, 'size' => 9.5];
        $sub = ['size' => 8.5, 'color' => '5F6F86'];
        $txt = ['size' => 9.5, 'color' => '334155'];
        $blocks = [
            'A' => ['B', [
                ["signed at the start of the rating period\n\n", $hint],
                ["Targets prepared by:\n", $lbl], [$val('targetsPreparedName', $defaultName) . "\n", $nm], ["Employee (Ratee)\n", $sub],
                ['Date: ' . $val('targetsPreparedDate') . "\n\n", $txt],
                ["Approved by:\n", $lbl], [$val('targetsApprovedName', $rater) . "\n", $nm], ["Immediate Supervisor (Rater)\n", $sub],
                ['Date: ' . $val('targetsApprovedDate'), $txt],
            ]],
            'C' => ['D', [
                ["signed at the end of the rating period\n\n", $hint],
                ["Rated by:\n", $lbl], [$val('evalRatedName', $rater) . "\n", $nm], ["Immediate Supervisor (Rater)\n", $sub],
                ['Date: ' . $val('evalRatedDate') . "\n\n", $txt],
                ["Conforme:\n", $lbl], [$val('evalConformeName', $defaultName) . "\n", $nm], ["Employee (Ratee)\n", $sub],
                ['Date: ' . $val('evalConformeDate'), $txt],
            ]],
            'E' => ['H', [
                ["Measures:\n", $lbl], ["Q   Quality\nT   Timeliness\nE   Efficiency\n\n", $txt],
                ["Rating Guide:\n", $lbl], ["5   Outstanding\n4   Very Satisfactory\n3   Satisfactory\n2   Unsatisfactory\n1   Poor", $txt],
            ]],
        ];
        $end = $r + 9;
        foreach ($blocks as $from => [$to, $runs]) {
            self::merge($sheet, "{$from}{$r}:{$to}{$end}", self::rich($runs));
            $sheet->getStyle("{$from}{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("{$from}{$r}:{$to}{$end}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }
        for ($i = $r; $i <= $end; $i++) $sheet->getRowDimension($i)->setRowHeight(18);
        $sheet->getStyle("E{$r}:H{$end}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FDFDFD');
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
        self::put($sheet, 'A1', 'RUBRICS TEMPLATE / SUCCESS INDICATOR STANDARDS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('14532D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 4. Matrix Table Headers (Rows 2-3)
        $sheet->mergeCells('A2:A3');
        self::put($sheet, 'A2', "OFFICE PPA / MAJOR FINAL OUTPUT /\nEXPECTED OUTPUTS");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(9.5);

        $sheet->mergeCells('B2:E2');
        self::put($sheet, 'B2', 'Standards or Rating Matrix per Success Indicator');
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(10);

        self::put($sheet, 'B3', 'Rating');
        self::put($sheet, 'C3', 'Q');
        self::put($sheet, 'D3', 'T');
        self::put($sheet, 'E3', 'E');

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
            self::put($sheet, "A{$blockStart}", $del['text']);
            $sheet->getStyle("A{$blockStart}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getStyle("A{$blockStart}")->getFont()->setSize(9.5);

            // Fetch saved rubric for this deliverable key or text if available
            $delRubric = $savedRubrics[$del['key']] ?? null;

            // Ratings 5 down to 1
            $scores = [5, 4, 3, 2, 1];
            foreach ($scores as $offset => $score) {
                $r = $blockStart + $offset;
                self::put($sheet, "B{$r}", $score);
                $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(10);

                // Populate saved digital criteria if present
                if ($delRubric && isset($delRubric[(string)$score])) {
                    self::put($sheet, "C{$r}", $delRubric[(string)$score]['q'] ?? '');
                    self::put($sheet, "D{$r}", $delRubric[(string)$score]['t'] ?? '');
                    self::put($sheet, "E{$r}", $delRubric[(string)$score]['e'] ?? '');
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
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(26);

        // University Header
        $sheet->mergeCells('A1:G1');
        self::put($sheet, 'A1', 'Republic of the Philippines');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setName('Bookman Old Style')->setSize(10)->setBold(true)->getColor()->setRGB('111827');

        $sheet->mergeCells('A2:G2');
        self::put($sheet, 'A2', 'Benguet State University');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setName('Old English Text MT')->setSize(17)->getColor()->setRGB('436531');
        $sheet->getRowDimension(2)->setRowHeight(28);

        $sheet->mergeCells('A3:G3');
        self::put($sheet, 'A3', 'Human Resource Development Office (HRDO)');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setName('Bookman Old Style')->setSize(10.5)->setBold(true)->getColor()->setRGB('111827');

        $sheet->mergeCells('A4:G4');
        self::put($sheet, 'A4', 'SPMS PERSONNEL ROSTER & MASTER LIST');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getFont()->setSize(11)->setBold(true);

        $scopeText = $collegeFilterName ? "Scope: {$collegeFilterName}" : 'Scope: University-Wide (All Colleges & Offices)';
        $sheet->mergeCells('A5:G5');
        self::put($sheet, 'A5', "Evaluation Period: {$cycleTitle}  |  {$scopeText}  |  Generated on: " . date('F j, Y'));
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5')->getFont()->setSize(9)->getColor()->setRGB('4B5563');

        // Add BSU Seal (Left) and Bagong Pilipinas (Right)
        self::addOfficialSeals($sheet, 'G');

        // Table Header
        $headers = [
            'A' => 'NO.',
            'B' => 'PERSONNEL NAME',
            'C' => 'COLLEGE / DEPARTMENT',
            'D' => 'POSITION / DESIGNATION',
            'E' => 'EMPLOYMENT STATUS',
            'F' => 'NUMERICAL RATING',
            'G' => 'ADJECTIVAL RATING'
        ];

        $headerRow = 7;
        $sheet->getRowDimension($headerRow)->setRowHeight(26);
        foreach ($headers as $col => $title) {
            self::put($sheet, "{$col}{$headerRow}", $title);
            $sheet->getStyle("{$col}{$headerRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle("{$col}{$headerRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$headerRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('0F3D29'); // Deep BSU Forest Green
        }
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            self::put($sheet, "A{$currentRow}", $idx);
            self::put($sheet, "B{$currentRow}", $personName);
            self::put($sheet, "C{$currentRow}", $r['department'] ?? 'General Administration');
            self::put($sheet, "D{$currentRow}", $r['position'] ?? 'Faculty / Staff');
            self::put($sheet, "E{$currentRow}", strtoupper($empStatus));
            self::put($sheet, "F{$currentRow}", $r['rating_num'] !== null ? number_format($r['rating_num'], 2) : '—');
            self::put($sheet, "G{$currentRow}", $r['adjectival_display'] ?? $r['adjectival_label'] ?? 'Not Yet Rated');

            // Alignment & Styling
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("F{$currentRow}")->getFont()->setBold(true);

            // Font & Fill
            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFont()->setSize(9);
            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgRgb);
            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

            $currentRow++;
            $idx++;
        }

        // Summary Section
        $summaryStart = $currentRow + 1;
        $sheet->mergeCells("A{$summaryStart}:G{$summaryStart}");
        self::put($sheet, "A{$summaryStart}", 'PERSONNEL & APPOINTMENT BREAKDOWN');
        $sheet->getStyle("A{$summaryStart}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('0F3D29');

        $totalCount = count($records);

        $s1 = $summaryStart + 1;
        self::put($sheet, "A{$s1}", 'Total Personnel:');
        self::put($sheet, "B{$s1}", $totalCount);
        self::put($sheet, "C{$s1}", 'Permanent:');
        self::put($sheet, "D{$s1}", $statusBreakdown['permanent']);

        $s2 = $s1 + 1;
        self::put($sheet, "A{$s2}", 'Temporary:');
        self::put($sheet, "B{$s2}", $statusBreakdown['temporary']);
        self::put($sheet, "C{$s2}", 'Casual / Contractual:');
        self::put($sheet, "D{$s2}", ($statusBreakdown['casual'] + $statusBreakdown['contractual']));

        $sheet->getStyle("A{$s1}:D{$s2}")->getFont()->setSize(9);
        $sheet->getStyle("A{$s1}:A{$s2}")->getFont()->setBold(true);
        $sheet->getStyle("C{$s1}:C{$s2}")->getFont()->setBold(true);

        // Sign-off / Certification Blocks
        $signRow = $s2 + 3;
        self::put($sheet, "B{$signRow}", 'Prepared by:');
        self::put($sheet, "D{$signRow}", 'Certified & Approved by:');
        $sheet->getStyle("B{$signRow}")->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle("D{$signRow}")->getFont()->setItalic(true)->setSize(9);

        $nameRow = $signRow + 3;
        self::put($sheet, "B{$nameRow}", 'HRDO DIRECTOR / SPMS FOCAL PERSON');
        self::put($sheet, "D{$nameRow}", 'UNIVERSITY PRESIDENT / PMT CHAIR');
        $sheet->getStyle("B{$nameRow}")->getFont()->setBold(true)->setSize(10)->setUnderline(true);
        $sheet->getStyle("D{$nameRow}")->getFont()->setBold(true)->setSize(10)->setUnderline(true);

        $titleRow = $nameRow + 1;
        self::put($sheet, "B{$titleRow}", 'Director, Human Resource Development Office');
        self::put($sheet, "D{$titleRow}", 'Benguet State University / PMT');
        $sheet->getStyle("B{$titleRow}")->getFont()->setSize(9);
        $sheet->getStyle("D{$titleRow}")->getFont()->setSize(9);

        // Stream Output
        $cleanCycle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $cycleTitle ?: 'Cycle');
        $filename = "SPMS_RATINGS_MASTER_LIST_{$cleanCycle}_" . date('Ymd') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        self::applyProtection($spreadsheet, $sheet);

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Apply worksheet and workbook protection to make exported files uneditable.
     * Prevents modifying cells, formulas, or structure while allowing viewing, copying, and printing.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $password Default password to unlock (e.g. 'spms')
     * @return void
     */
    /**
     * setCellValue for everything except the exporter's own formulas. Text that starts with = + - or @
     * (for example a cell typed as =HYPERLINK(...)) is written as plain text, so it can never run as a
     * formula when the file is opened in Excel (formula injection, security test 2026-11-06).
     * Numbers, including negative ones, stay numbers.
     */
    private static function put(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $coordinate, $value): void
    {
        if (is_string($value) && !is_numeric($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
            $sheet->setCellValueExplicit($coordinate, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            return;
        }
        $sheet->setCellValue($coordinate, $value);
    }

    /**
     * Locks the sheet and the workbook structure with the Excel password in use (Accounts > Excel Password), hashed
     * with SHA-512 as current Excel does (2026-11-08; before, the weak legacy hash and always "spms"). The file names
     * when the password came into use in its Comments (File > Info), so the Admin can look up an older one by date.
     */
    private static function applyProtection(Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?string $password = null): void
    {
        $key = \App\Libraries\ExcelPassword::current();
        $password ??= $key['password'];
        $spreadsheet->getProperties()->setCreator('SPMS')->setDescription(
            ($key['since'] ? 'Protected with the Excel password in use since ' . date('M j, Y g:i A', strtotime($key['since'])) : 'Protected with the built-in Excel password')
            . '. The Admin finds it in SPMS: Accounts > Excel Password.');
        $protection = $sheet->getProtection();
        $protection->setSheet(true);
        $protection->setSort(true);
        $protection->setInsertRows(true);
        $protection->setInsertColumns(true);
        $protection->setDeleteRows(true);
        $protection->setDeleteColumns(true);
        $protection->setFormatCells(true);
        $protection->setFormatRows(true);
        $protection->setFormatColumns(true);
        $protection->setAutoFilter(true);
        $protection->setPivotTables(true);

        if ($password !== '') {
            $protection->setAlgorithm(\PhpOffice\PhpSpreadsheet\Worksheet\Protection::ALGORITHM_SHA_512)->setSpinCount(100000);
            $protection->setPassword($password);
            $security = $spreadsheet->getSecurity();
            $security->setLockStructure(true);
            $security->setWorkbookAlgorithmName(\PhpOffice\PhpSpreadsheet\Worksheet\Protection::ALGORITHM_SHA_512)
                ->setWorkbookSaltValue(random_bytes(16), true)->setWorkbookSpinCount(100000);
            $security->setWorkbookPassword($password);
        }
    }

    /**
     * Embeds the official BSU Seal (left) and Bagong Pilipinas (right) logos into the spreadsheet header.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $lastCol Rightmost column letter (e.g. 'H' or 'J')
     * @return void
     */
    private static function addOfficialSeals(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $lastCol): void
    {
        $publicPath = defined('FCPATH') ? FCPATH : realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR;
        
        // 1. Left: Benguet State University Official Seal
        $bsuSealPath = $publicPath . 'assets/images/bsu_seal.png';
        if (file_exists($bsuSealPath)) {
            $drawingBsu = new Drawing();
            $drawingBsu->setName('BSU Seal');
            $drawingBsu->setDescription('Benguet State University Official Seal');
            $drawingBsu->setPath($bsuSealPath);
            $drawingBsu->setCoordinates('A1');
            $drawingBsu->setHeight(68);
            $drawingBsu->setOffsetX(18);
            $drawingBsu->setOffsetY(3);
            $drawingBsu->setWorksheet($sheet);
        }

        // 2. Right: Bagong Pilipinas Official Logo (MC 24, s. 2023 Compliance)
        $bpPath = $publicPath . 'assets/images/bagong_pilipinas.png';
        if (file_exists($bpPath)) {
            $drawingBp = new Drawing();
            $drawingBp->setName('Bagong Pilipinas');
            $drawingBp->setDescription('Bagong Pilipinas Official Logo');
            $drawingBp->setPath($bpPath);
            $drawingBp->setCoordinates("{$lastCol}1");
            $drawingBp->setHeight(64);
            $drawingBp->setOffsetX(15);
            $drawingBp->setOffsetY(4);
            $drawingBp->setWorksheet($sheet);
        }
    }
}

