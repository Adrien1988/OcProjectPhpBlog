<?php

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CsrfService;

class CsrfMiddleware
{

    private $csrfService;


    public function __construct(CsrfService $csrfService)
    {
        $this->csrfService = $csrfService;

    }//end __construct()


    public function handle(Request $request, callable $next)
    {
        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_csrf_token');
            if (!$this->csrfService->isTokenValid('contact_form', $submittedToken)) {
                return new Response('Invalid CSRF token.', 400);
            }
        }

        return $next($request);

    }//end handle()


}//end class
