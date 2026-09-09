<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuditLog extends BaseController
{
    /**
     * GET /audit-logs
     * Admin Audit Trail & Institutional Activity Viewer
     */
    public function index()
    {
        $role = session()->get('role');
        if ($role !== 'Admin') {
            return redirect()->to('dashboard')->with('errors', ['error' => 'Unauthorized access. Only Administrators can view the audit trail.']);
        }

        $filters = [
            'search'    => trim($this->request->getGet('search') ?? ''),
            'category'  => trim($this->request->getGet('category') ?? 'ALL'),
            'date_from' => trim($this->request->getGet('date_from') ?? ''),
            'date_to'   => trim($this->request->getGet('date_to') ?? ''),
        ];

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 25;

        $logModel = new ActivityLogModel();
        $logs = $logModel->getLogs($filters, $perPage, $page);
        $totalCount = $logModel->countLogs($filters);
        $totalPages = max(1, (int) ceil($totalCount / $perPage));

        // Aggregate high-level metric cards
        $db = \Config\Database::connect();
        $metrics = [
            'total'    => (int) $db->table('activity_logs')->countAllResults(),
            'auth'     => (int) $db->table('activity_logs')->where('category', 'AUTH')->countAllResults(),
            'target'   => (int) $db->table('activity_logs')->where('category', 'TARGET')->countAllResults(),
            'rating'   => (int) $db->table('activity_logs')->where('category', 'RATING')->countAllResults(),
            'evidence' => (int) $db->table('activity_logs')->where('category', 'EVIDENCE')->countAllResults(),
        ];

        return view('audit_logs/index', [
            'logs'        => $logs,
            'filters'     => $filters,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalCount'  => $totalCount,
            'totalPages'  => $totalPages,
            'metrics'     => $metrics,
        ]);
    }

    /**
     * GET /audit-logs/export-csv
     * Generates a downloadable CSV report of the filtered audit trail.
     */
    public function exportCsv()
    {
        $role = session()->get('role');
        if ($role !== 'Admin') {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $filters = [
            'search'    => trim($this->request->getGet('search') ?? ''),
            'category'  => trim($this->request->getGet('category') ?? 'ALL'),
            'date_from' => trim($this->request->getGet('date_from') ?? ''),
            'date_to'   => trim($this->request->getGet('date_to') ?? ''),
        ];

        $logModel = new ActivityLogModel();
        $logs = $logModel->getAllForExport($filters);

        $filename = 'SPMS_Audit_Trail_' . date('Ymd_His') . '.csv';

        // Write CSV to a php://temp memory stream
        $fp = fopen('php://temp', 'r+');

        // UTF-8 BOM for Microsoft Excel auto-detect
        fwrite($fp, "\xEF\xBB\xBF");

        // Header Row
        fputcsv($fp, [
            'Log ID',
            'Timestamp',
            'Action Code',
            'Category',
            'User Name',
            'User Email',
            'System Role',
            'Entity Type',
            'Entity ID',
            'Client IP',
            'Event Details',
            'User Agent / Device'
        ]);

        foreach ($logs as $row) {
            fputcsv($fp, [
                $row['id'],
                $row['created_at'],
                $row['action'],
                $row['category'],
                $row['user_name'],
                $row['email'] ?? '—',
                $row['role_name'] ?? '—',
                $row['entity_type'] ?? '—',
                $row['entity_id'] ?? '—',
                $row['ip_address'] ?? '—',
                $row['details'] ?? '—',
                $row['user_agent'] ?? '—',
            ]);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($csvContent);
    }
}
