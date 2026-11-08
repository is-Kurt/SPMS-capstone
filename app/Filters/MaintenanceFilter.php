<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * While a backup is being restored (a few seconds), every request gets a plain "The site is down" page (503), so
 * nobody saves into a database that is being replaced (App\Libraries\Backups). Runs before everything else.
 */
class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!\App\Libraries\Backups::inMaintenance()) return null;
        $response = service('response')->setStatusCode(503)->setHeader('Retry-After', '30')->setHeader('Cache-Control', 'no-store');
        $wantsJson = $request->isAJAX() || str_contains($request->getHeaderLine('Accept'), 'application/json')
                  || $request->getHeaderLine('X-Requested-With') !== '';
        if ($wantsJson) {
            return $response->setJSON(['status' => 'error', 'message' => 'The site is down. Please try again in a moment.']);
        }
        $css = base_url('assets/css/main/style.css');
        return $response->setBody(<<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SPMS</title>
<link rel="stylesheet" href="{$css}">
</head>
<body class="min-h-screen flex items-center justify-center bg-zinc-50 text-zinc-900">
<main class="text-center px-6">
<h1 class="text-2xl font-black tracking-tight">The site is down</h1>
<p class="mt-2 text-sm text-zinc-500">Please try again in a moment.</p>
</main>
</body>
</html>
HTML);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
