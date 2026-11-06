<?php

namespace App\Filters;

use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\RequestInterface;

/**
 * CodeIgniter's honeypot filter, but a form caught by the bot trap gets a plain 403 instead of an
 * uncaught exception, which showed up as "500 Internal Server Error" (and a CRITICAL log line) for
 * every bot or scanner that filled in the hidden field.
 */
class HoneypotFilter extends Honeypot
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            return parent::before($request, $arguments);
        } catch (HoneypotException $e) {
            log_message('notice', 'Honeypot: form submission refused as a bot from ' . $request->getIPAddress());
            return service('response')->setStatusCode(403)->setBody('Request refused.');
        }
    }
}
