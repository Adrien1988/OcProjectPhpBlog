<?php

namespace App\Middlewares;

use ParagonIE\AntiCSRF\AntiCSRF;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CsrfMiddleware
{
    private AntiCSRF $antiCSRF;

    public function __construct()
    {
        $this->antiCSRF = new AntiCSRF();
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->antiCSRF->validateRequest()) {
                throw new AccessDeniedHttpException('Invalid CSRF token.');
            }
        }

        if ($request->isMethod('GET')) {
            $this->antiCSRF->insertToken();
        }

        return $next($request);
    }
}
