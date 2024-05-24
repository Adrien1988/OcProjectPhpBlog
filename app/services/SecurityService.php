<?php

namespace App\Services;

use voku\helper\AntiXSS;

class SecurityService
{
    private AntiXSS $antiXSS;

    public function __construct()
    {
        $this->antiXSS = new AntiXSS();
    }

    public function cleanInput(string $input): string
    {
        return $this->antiXSS->xss_clean($input);
    }
}
