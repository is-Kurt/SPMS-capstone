<?php

namespace App\Controllers;

use App\Libraries\ExcelPassword;

/**
 * Accounts > Excel Password (2026-11-08), Admin only: sets the password that protects newly downloaded Excel files.
 * The tab is drawn by AccountManagement::index; the passwords are kept in App\Libraries\ExcelPassword.
 */
class ExcelPasswords extends BaseController
{
    /** POST /excel-password/set: password, password_confirm, admin_password (the Admin's own account password). */
    public function set()
    {
        return $this->tryOrFail(function () {
            $me = (new \App\Models\UserModel())->find((int) session()->get('user_id'));
            if (!$me || !password_verify((string) $this->request->getPost('admin_password'), (string) $me['password'])) {
                return $this->respondError('Incorrect password.', 422);
            }
            $new = (string) $this->request->getPost('password');
            if ($new !== (string) $this->request->getPost('password_confirm')) {
                return $this->respondError('Passwords do not match.', 422);
            }
            $err = ExcelPassword::set($new, (int) $me['id']);
            if ($err !== null) return $this->respondError($err, 422);
            $since = date('M j, Y g:i A', strtotime((string) ExcelPassword::current()['since']));
            audit_log('EXCEL_PASSWORD_CHANGED', 'SYSTEM', 'excel_password', null, "Excel password changed, in use from {$since} (the password itself is not logged)");
            return $this->respond(['status' => 'success',
                'message' => "Saved. Excel files downloaded from now on use it; files downloaded before keep the password they had."]);
        });
    }
}
