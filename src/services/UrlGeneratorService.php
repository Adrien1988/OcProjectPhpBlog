<?php

namespace App\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGeneratorService
{

    /**
     * Le générateur d'URL Symfony.
     *
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;


    /**
     * Constructeur de la classe UrlGeneratorService.
     *
     * @param UrlGeneratorInterface $urlGenerator Le générateur d'URL de Symfony.
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

    }//end __construct()


    /**
     * Génère une URL pour la route spécifiée.
     *
     * @param string $route         La route nommée.
     * @param array  $parameters    Les paramètres de la route.
     * @param int    $referenceType Le type de référence pour l'URL (absolue ou relative).
     *
     * @return string L'URL générée.
     */
    public function generateUrl(string $route, array $parameters=[], int $referenceType=UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        return $this->urlGenerator->generate($route, $parameters, $referenceType);

    }//end generateUrl()


    /**
     * Obtient l'URL de base de l'application.
     *
     * @return string L'URL de base.
     */
    public function getBaseUrl(): string
    {
        $currentRequest = $this->urlGenerator->getContext()->getBaseUrl();
        $scheme         = $this->urlGenerator->getContext()->getScheme();
        $host           = $this->urlGenerator->getContext()->getHost();

        return "{$scheme}://{$host}{$currentRequest}";

    }//end getBaseUrl()


}//end class
