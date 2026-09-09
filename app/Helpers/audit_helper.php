<?php

use App\Models\ActivityLogModel;

if (!function_exists('audit_log')) {
    /**
     * Records an action into the institutional audit log.
     *
     * @param string $action       Specific action code e.g. 'LOGIN', 'TARGET_SUBMITTED', 'SCORE_UPDATED'
     * @param string $category     Category e.g. 'AUTH', 'TARGET', 'RATING', 'ACCOUNT', 'EVIDENCE'
     * @param string|null $entityType Name of table or resource e.g. 'document_folder', 'document', 'user', 'document_attachment'
     * @param int|null $entityId   ID of the affected resource
     * @param mixed $details       Optional details (string or array to be JSON-encoded)
     * @param int|null $userId     Optional user ID overriding session user
     * @return bool
     */
    function audit_log(
        string $action,
        string $category = 'SYSTEM',
        ?string $entityType = null,
        ?int $entityId = null,
        $details = null,
        ?int $userId = null
    ): bool {
        try {
            // Determine active user ID if not explicitly passed
            if ($userId === null) {
                $session = session();
                if ($session && $session->has('user_id')) {
                    $userId = (int) $session->get('user_id');
                }
            }

            // Determine client IP and User-Agent
            $request = service('request');
            $ipAddress = null;
            $userAgent = null;

            if ($request instanceof \CodeIgniter\HTTP\IncomingRequest) {
                $ipAddress = $request->getIPAddress();
                $agent = $request->getUserAgent();
                if ($agent) {
                    $userAgent = substr((string) $agent, 0, 250);
                }
            } elseif ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
                $ipAddress = '127.0.0.1 (CLI)';
                $userAgent = 'CLI / Spark Command';
            }

            // Format details as string
            $detailsStr = null;
            if (is_array($details) || is_object($details)) {
                $detailsStr = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($details !== null) {
                $detailsStr = (string) $details;
            }

            $model = new ActivityLogModel();
            $model->insert([
                'user_id'     => $userId,
                'action'      => strtoupper(trim($action)),
                'category'    => strtoupper(trim($category)),
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'ip_address'  => $ipAddress,
                'user_agent'  => $userAgent,
                'details'     => $detailsStr,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Throwable $e) {
            // Silent fail-safe: never disrupt user flows if audit logging fails
            log_message('error', '[audit_log] Error logging activity: ' . $e->getMessage());
            return false;
        }
    }
}
