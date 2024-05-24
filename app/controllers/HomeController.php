<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HomeController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index()
    {
        $content = $this->twig->render('home/index.html.twig', ['message' => 'Welcome to the home page!',]);
        
        return new Response($content);
    }
}
