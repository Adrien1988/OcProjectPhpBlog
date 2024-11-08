<?php

namespace App\Init;

use App\Services\SessionService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Initialise le service de session.
 */
class SessionInit
{


    /**
     * Initialise le service de session en démarrant une nouvelle session.
     *
     * @return SessionService Le service de session initialisé.
     */
    public function initialize(): SessionService
    {
        $sessionStorage = new NativeSessionStorage();
        $session        = new Session($sessionStorage);
        $session->start();
        return new SessionService($session);

    }//end initialize()


}//end class
