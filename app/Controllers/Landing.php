<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index()
    {
        $session = session();
        // The public marketing landing page has been decommissioned in favor of
        // the official institutional BSU-SPMS portal gateway at /login.
        if ($session->get('user_id') && !$this->request->getGet('logged_out')) {
            $dest = ($session->get('role') === 'TWG') ? 'ratings' : 'folders';
            return redirect()->to(site_url($dest));
        }

        return redirect()->to(site_url('login'));
    }
}
