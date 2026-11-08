<?php

namespace App\Controllers;

use App\Libraries\Backups;

/**
 * Accounts > Backups (2026-11-08), Admin only: back up now, restore a backup, delete one made here.
 * The work is in App\Libraries\Backups; the tab itself is drawn by AccountManagement::index.
 */
class SystemBackups extends BaseController
{
    /** POST /backups/create */
    public function create()
    {
        return $this->tryOrFail(function () {
            try {
                $b = Backups::create('manual', trim((string) $this->request->getPost('note')), $this->who());
            } catch (\RuntimeException $e) {
                return $this->respondError($e->getMessage(), 409);
            }
            audit_log('BACKUP_CREATED', 'SYSTEM', 'backup', null, $b['id']);
            return $this->respond(['status' => 'success', 'message' => 'Backup made: ' . ($b['label'] ?? $b['id']) . '.']);
        });
    }

    /** POST /backups/restore: id, password (the Admin's own), confirm ("RESTORE"). */
    public function restore()
    {
        return $this->tryOrFail(function () {
            $id = (string) $this->request->getPost('id');
            if (strtoupper(trim((string) $this->request->getPost('confirm'))) !== 'RESTORE') {
                return $this->respondError('Type RESTORE to confirm.', 422);
            }
            $me = (new \App\Models\UserModel())->find((int) session()->get('user_id'));
            if (!$me || !password_verify((string) $this->request->getPost('password'), (string) $me['password'])) {
                return $this->respondError('That password is not right.', 422);
            }
            if (!Backups::find($id)) return $this->respondError('That backup was not found.', 404);
            try {
                $res = Backups::restore($id, $this->who());
            } catch (\RuntimeException $e) {
                return $this->respondError($e->getMessage(), 409);
            }
            // Written after the restore, so it is in the restored audit trail
            audit_log('BACKUP_RESTORED', 'SYSTEM', 'backup', null, "Restored {$res['restored']['label']} ({$res['restored']['id']}); the state before is backup {$res['before']['id']}");
            return $this->respond(['status' => 'success',
                'message' => "Restored: {$res['restored']['label']}. The state before the restore was backed up first (Before a restore), so you can undo it."]);
        });
    }

    /** POST /backups/delete: id (backups made here only). */
    public function delete()
    {
        return $this->tryOrFail(function () {
            $id = (string) $this->request->getPost('id');
            try {
                Backups::delete($id);
            } catch (\RuntimeException $e) {
                return $this->respondError($e->getMessage(), 409);
            }
            audit_log('BACKUP_DELETED', 'SYSTEM', 'backup', null, $id);
            return $this->respond(['status' => 'success', 'message' => 'Backup deleted.']);
        });
    }

    private function who(): string
    {
        $u = (new \App\Models\UserModel())->find((int) session()->get('user_id'));
        return $u ? trim($u['first_name'] . ' ' . $u['last_name']) . ' (' . $u['email'] . ')' : 'user ' . session()->get('user_id');
    }
}
