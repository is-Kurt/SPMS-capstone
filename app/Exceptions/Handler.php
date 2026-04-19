<?php

namespace App\Exceptions;

use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Handler implements ExceptionHandlerInterface
{
    public function __construct(private \Config\Exceptions $config) {}

    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode  
    ): void {
        if ($request->isAJAX()) {
            $response->setJSON([
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ])->setStatusCode(500)->send();
            exit;
        }

        $handler = new \CodeIgniter\Debug\ExceptionHandler($this->config);
        $handler->handle($exception, $request, $response, $statusCode, $exitCode);
    }
}